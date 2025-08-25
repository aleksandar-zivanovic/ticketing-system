<?php

/**
 * Cleans string to prevent XSS attack.
 * 
 * @param string $string String to sanitize.
 * 
 * @return string
 */
function cleanString(string $string): string
{
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

function persist_input(string $sessionName): void
{
    echo isset($_SESSION[$sessionName]) ? cleanString($_SESSION[$sessionName]) : '';
    unset($_SESSION[$sessionName]);
}

/**
 * Saves POST values from a form into SESSION variables, excluding specified exceptions.
 * 
 * @param array|null $exceptions Keys from $_POST that should not be saved in $_SESSION.
 * @return void
 */
function saveFormValuesToSession(?array $exceptions = null): void
{
    foreach ($_POST as $key => $value) {
        if ($exceptions && in_array($key, $exceptions)) {
            continue;
        }

        $_SESSION[$key] = $value;
    }
}

/**
 * Display a session message. Unset session message after displaying it.
 * 
 * @param string $name $_SESSION key for the message.
 * @param bool $div If true, wraps the message inside a <div> tag.
 * @param string $class An optional class attribute for the <div> tag. 
 *                      If not empty and $div is true, it is added as a class attribute.
 */
function handleSessionMessages(string $name, bool $div = false, string $class = ""): void
{
    if (isset($_SESSION[$name])) {
        $cleanMessage = htmlspecialchars(trim($_SESSION[$name]));
        $messageClass =  !empty($class) ? 'class="' . htmlspecialchars(trim($class)) . '"' : "";
        echo $div ? "<div " . $messageClass . ">" . $cleanMessage .  "</div>" : $cleanMessage;
        unset($_SESSION[$name]);
    }
}

// Checks if directory exists and create it if doesn't
function checkAndCreateDirectory($locationDir): void
{
    if (!is_dir($locationDir)) {
        mkdir($locationDir, 0775, true);
    }
}

// writting down error log
function logError(string $message, array|string|null $errorInfo = null,  ?string $logFileName = "php_errors.log"): void
{
    // check if the directory exists and create if not
    $logDirectory = ROOT . 'logs/';
    checkAndCreateDirectory($logDirectory);

    // preparing final message
    if (!empty($errorInfo)) {
        $message .= is_array($errorInfo) ? " | Error: " . implode(", ", $errorInfo) : $message;
    }

    // writting down an error to the log file
    $logFile = $logDirectory . $logFileName;
    $timestamp = date("Y-m-d H:i:s");
    error_log("[$timestamp]: $message" .  PHP_EOL, 3, $logFile);
}

/**
 * Generates a breadcrumb-friendly page name.
 * Call this function with fileName(__FILE__).
 *
 * @param string $currentPage The current file path.
 * @return string The formatted page name.
 */
function fileName($currentPage): string
{
    $pageName = basename($currentPage, '.php');
    $page = str_replace("-", " ", $pageName);
    return cleanString(ucwords($page));
}

/**
 * Adds the "selected" attribute to a form option based on a $_GET parameter or a session value.
 *
 * This function checks if the provided $_GET parameter matches the given form value.
 * If they match, it returns the "selected" attribute to be used in an <option> element. 
 * If the $_GET parameter is not set, it checks if the session value matches the form value.
 * If neither match, it returns null (no attribute).
 *
 * @param string $formValue The value of the option in the form.
 * @param string|null $getParam The name of the $_GET parameter to check.
 * @param string|bool|null $sessionName The name of the session variable to check if $_GET is not set.
 *                                      If `true`, it gets the value of `$getParam`.
 * @return string Returns "selected" if the values match, otherwise empty string.
 */
function addSelectedTag(
    string $formValue,
    ?string $getParam = null,
    bool|string|null $sessionName = null
): ?string {
    // If $sessionName is true, use $getParam as the session key
    $sessionName = $sessionName === true ? $getParam : $sessionName;

    if (isset($_GET[$getParam]) && cleanString($_GET[$getParam]) === $formValue) {
        return "selected";
    }

    if (!isset($_GET[$getParam]) && isset($_SESSION[$sessionName]) && cleanString($_SESSION[$sessionName]) === $formValue) {
        return "selected";
    }

    return "";
}

/**
 * Logs out the user and redirects the user to the specified page.
 * 
 * @param string $redirectionUrl The URL which the user will be redirected to after logging out.
 * @return void 
 */
function logout(string $redirectionUrl): void
{
    session_unset();
    session_destroy();

    header("Location:{$redirectionUrl}");
    die;
}

/**
 * Debugging function for formatting var_dump and var_export output, 
 * and displaying the variable's name.
 * 
 * @param string $function The function to call: "d" for var_dump, "e" for var_export.
 * @param mixed $variable The variable to debug (e.g., $_SESSION, an array, or any other variable).
 * @return void
 */
function formatVar(string $function, mixed $variable)
{
    if ($function !== "d" && $function !== "e") {
        throw new Exception("Wrong function parameter!");
    }

    $variableName = "";
    foreach ($GLOBALS as $varName => $value) {
        if ($value === $variable) {
            $variableName = "\$" . $varName;
        }
    }

    echo "<pre style='background-color:lightblue;'>";
    echo "<h1>{$variableName}</h1>";
    $function === "d" ? var_dump($variable) : var_export($variable);
    echo "</pre>";
}

/**
 * Checks if the user is logged in.
 * 
 * @return bool True if the user is logged in, false otherwise.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_role']);
}

/**
 * Retrieves user details from the database based on the user ID stored in the session.
 * 
 * @return array|null Returns an associative array of user details if found, or null if the user does not exist.
 * @throws RuntimeException if request failed.
 * @see User::getUserById()
 */
function getUserFromSession(): ?array
{
    require_once ROOT . "classes" . DS . "User.php";
    $userId  = (int) $_SESSION["user_id"];
    $user    = new User();
    return $user->getUserById($userId);
}

/**
 * Verifies that the user from the session exists in the database.
 * If the user does not exist, logs out and redirects to the login page.
 * 
 * @return void
 * @throws RuntimeException if request failed.
 */
function verifyUserFromSession(): void
{
    $theUser = getUserFromSession();
    if ($theUser === null) {
        logout("/ticketing-system/login.php");
    }
}

/**
 * Refreshes the session if the refresh interval has expired.
 * Checks if the user still exists in the database and updates session variables.
 * If the user does not exist, logs out and redirects to the login page.
 * 
 * @return void
 * @throws RuntimeException if request failed.
 */
function refreshSessionIfNeeded(): void
{
    require_once __DIR__ . "/../config/config.php";
    $expirationTime = SESSION_REFRESH_INTERVAL;
    $timeDifference = time() - $expirationTime;

    if (empty($_SESSION["last_check"]) || $_SESSION["last_check"] < $timeDifference) {

        // Fetch user from the database
        $theUser = getUserFromSession();

        if (empty($theUser) || (int) $_SESSION["session_version"] !== $theUser["session_version"]) {
            logout("/ticketing-system/login.php");
        }

        $_SESSION['last_check']      = time();
        $_SESSION["session_version"] = $theUser["session_version"];
        $_SESSION['user_id']         = $theUser['u_id'];
        $_SESSION['user_role']       = $theUser['r_name'];
        $_SESSION['user_email']      = $theUser['u_email'];
    }
}

/**
 * Ensures that the user is logged in.
 * If the user is not logged in, logs out and redirects to the login page.
 * Also verifies the user from the session and refreshes the session if needed.
 * 
 * @return void
 * @throws RuntimeException if request failed.
 * @note Make sure to call this function at the beginning of any page that requires user authentication.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        logout("/ticketing-system/login.php");
    }

    verifyUserFromSession();
    refreshSessionIfNeeded();
}

/**
 * Checks if the user has the required role(s).
 * If the user is not logged in, redirects to the login page.
 * If the user is unauthorized, redirects to the specified URL 
 * or terminates execution with a message if no URL is provided.
 * 
 * @param string|array $role The required role or an array of allowed roles.
 * @param string|null $url Optional. The URL to redirect unauthorized users to. If null, execution is terminated.
 */
function checkAuthorization(string|array $role, ?string $url = null)
{
    // Check if the user is logged in, redirects to the login page if not.
    requireLogin();

    if (is_array($role)) $authorized = in_array($_SESSION['user_role'], $role);

    if (is_string($role)) $authorized = $_SESSION['user_role'] === $role;

    // Redirection
    if ($authorized !== true) {
        if ($url === null) {
            die('Access denied!');
        } else {
            header("Location: " . $url);
            die();
        }
    }
}

/**
 * Prepares the allowed values array used for validating filters in fetchAllTickets().
 * Merges statuses, priorities, and departments arrays into one multidimensional array.
 *
 * @param array $allTicketFilterData Associative array with keys 'statuses', 'priorities', and 'departments', each containing an array of strings.
 *
 * @return array{
 *     statuses: string[],
 *     priorities: string[],
 *     departments: string[]
 * }
 */
function buildAllowedTicketValues(array $allTicketFilterData): array
{
    // Set allowed values list for fetchAllTickets() method
    return array_merge(
        ["statuses" => $allTicketFilterData["statuses"]],
        ["priorities" => $allTicketFilterData["priorities"]],
        ["departments" => $allTicketFilterData["departments"]],
    );
}

/**
 * Debugs value of a variable with the chosen function.
 * Formats display with <pre> tag.
 * 
 * @param mixed $variable A variable for debugging.
 * @param string $function Built-in function you want to use for debugging.
 *                         Allowed values are "var_dump", "var_export" and "print_r".
 * @param bool $die Options that activate die() function.
 * 
 * @return void
 */
function dd(mixed $variable, string $function = "var_dump", bool $die = true): void
{
    echo "<pre>";

    if ($function === "var_dump") {
        var_dump($variable);
    } elseif ($function === "var_export") {
        var_export($variable);
    } elseif ($function === "print_r") {
        print_r($variable);
    } else {
        throw new DomainException("\$function param has unallowed value. Choose `var_dump`, `var_export` or `print_r` value for it.");
    }

    echo "</pre>";

    if ($die === true) die;
}

/**
 * Calculates percentage.
 * 
 * @param int $part Number of units in the part.
 * @param int $total Total number of units.
 * 
 * @return float Percentage rounded to two decimals.
 */
function countPercentage(int $part, int $total): float
{
    return $total > 0 ? round($part / $total * 100, 2) : 0;
}

/**
 * Returns the client IP address.
 *
 * @return string Client IP address or 'unknown' if not available.
 */
function getIp(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Redirects to a specified path and optionally sets a session message.
 * 
 * @param string $sessionMessage The message to store in the session. If empty, no message is set.
 * @param string $type The type of message (e.g., "success", "fail", "info"). Default is "fail".
 * @param string $path The URL path to redirect to.
 * 
 * @throws InvalidArgumentException If the provided type is not one of the allowed values.
 */
function redirectAndDie(string $path, string $sessionMessage = "", string $type = "fail"): void
{
    $types = ["success", "fail", "info"];

    // Validate type
    if (!in_array($type, $types)) {
        throw new InvalidArgumentException("Type has unallowed value. Choose one of the following: " . implode(", ", $types));
    }

    if (!empty($sessionMessage)) {
        $_SESSION[$type] = $sessionMessage;
    }

    header("Location: {$path}");
    die;
}
