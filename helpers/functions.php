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
 */
function saveFormValuesToSession(?array $exceptions = null): void
{
    foreach($_POST as $key => $value) {
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
function checkAndCreateDirectory($locationDir): void {
    if (!is_dir($locationDir)) {
        mkdir($locationDir, 0775, true);
    }
}

// writting down error log
function logError(string $message, array|string|null $errorInfo = null,  ?string $logFileName = "php_errors.log"): void 
{
    // check if the directory exists and create if not
    $logDirectory = __DIR__ . '../../logs/';
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
 * Renders a partial input field using the input.php template.
 * This function sanitizes the input parameters to prevent XSS attacks.
 * 
 * @param string|null $label: The text label for the input field.
 * @param string $name: The name attribute for the input field. It should match the corresponding session variable if using $_SESSION for persistent input.
 * @param string $type: The type of the input field (e.g., text, password).
 * @param string|null $placeholder: The placeholder text for the input field.
 * 
 * @note: Ensure that the name attribute of the input matches the session variable name 
 * if you intend to use $_SESSION to pre-fill the input with previously submitted data.
 */
function renderingInputField(
    ?string $label, 
    string $name, 
    string $type, 
    ?string $placeholder, 
    string|int|null $value = null
): void
{
    $label = $label ? htmlspecialchars($label) : null;
    $name        = htmlspecialchars($name);
    $type        = htmlspecialchars($type);
    $placeholder = $placeholder ? htmlspecialchars($placeholder) : null;
    $value = $value ? htmlspecialchars($value) : null;

    if ($type == 'hidden' && empty($value)) {
        throw new InvalidArgumentException("If type='hidden', value must be string or integer.");
    }

    if ($type == 'hidden' && !empty($placeholder)) {
        throw new InvalidArgumentException("placeholder should not exists.");
    }
    
    require __DIR__ . '/../partials/input.php';  
}

/** 
 * Renders a checkbox field with an optional label and link description.
 * This function sanitizes the checkbox parameters to prevent XSS attacks.
 * 
 * @param string $name The name attribute for the checkbox field.
 * @param ?string $id The id attribute for the checkbox field. If null, the id will default to the value of $name.
 * @param string $agreeText The text for the checkbox label.
 * @param ?string $agreeUrl The URL of the link in the label description. (optional)
 * @param ?string $agreeUrlDescription The text for the link in the label description. (optional)
 *
 * Example usage:
 * renderingCheckboxField('terms', null, 'I agree to the <strong>Terms and Conditions</strong>', 'https://example.com/terms', 'Terms and Conditions');
 */
function renderingCheckboxField(
    string $name, 
    ?string $id = null, 
    string $agreeText, 
    ?string $agreeUrl = null, 
    ?string $agreeUrlDescription = null
): void
{
    $name                 = htmlspecialchars($name);
    $id                   = !$id ? $name : htmlspecialchars($id);
    $agreeText            = htmlspecialchars($agreeText);
    $agreeUrl             = $agreeUrl ? htmlspecialchars($agreeUrl) : null;
    $agreeUrlDescription  = $agreeUrlDescription ? htmlspecialchars($agreeUrlDescription) : null;

    require __DIR__ . '/../partials/input-checkbox.php';  
}

/**
 * Renders a partial text area field using the textArea.php template.
 * This function sanitizes the input parameters to prevent XSS attacks.
 * 
 * @param string|null $label: The text label for the input field.
 * @param string $name: The name attribute for the input field. It should match the corresponding session variable if using $_SESSION for persistent input.
 * 
 * @note: Ensure that the name attribute of the input matches the session variable name 
 * if you intend to use $_SESSION to pre-fill the input with previously submitted data.
 */
function renderingTextArea(?string $label, string $name) : void
{
    $label = $label ? htmlspecialchars($label) : null;
    $name  = htmlspecialchars($name);

    require __DIR__ . '/../partials/textArea.php'; 
} 

/**
 * Renders a partial text area field using the textArea.php template.
 * This function sanitizes the input parameters to prevent XSS attacks.
 * 
 * @param string|null $label: The text label for the input field.
 * @param string $name: The name attribute for the input field. It should match the corresponding session variable if using $_SESSION for persistent input.
 * 
 * @note: Ensure that the name attribute of the input matches the session variable name 
 * if you intend to use $_SESSION to pre-fill the input with previously submitted data.
 */
function renderingSelectOption(?string $label, string $name, array $data) : void
{
    $label = $label ? htmlspecialchars($label) : null;
    $name  = htmlspecialchars($name);

    require __DIR__ . '/../partials/select-option.php'; 
} 

/** 
 * Renders a width: 100% submit button.
 * @param string $name The name attribute for the submit button.
 * @param string $value The value attribute for the submit button.
 */
function renderingSubmitButton(string $name, string $value): void
{
    $name       = htmlspecialchars($name);
    $value      = htmlspecialchars($value);

    require __DIR__ . '/../partials/input-submit.php';  
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
 * @param string|null $getParam The name of the $_GET parameter to check.
 * @param string $formValue The value of the option in the form.
 * @param string|null $sessionName The name of the session variable to check if $_GET is not set.
 * @param string|bool|null $sessionName The name of the session variable to check if $_GET is not set.
 *                                      If `true`, it gets the value of `$getParam`.
 * @return string|null Returns "selected" if the values match, otherwise null.
 */
function addSelectedTag(
    string $getParam = null, 
    string $formValue, 
    bool|string|null $sessionName = null
    ): ?string
{
    // If $sessionName is true, use $getParam as the session key
    $sessionName = $sessionName === true ? $getParam : $sessionName;

    if (isset($_GET[$getParam]) && cleanString($_GET[$getParam]) === $formValue) {
        return "selected";
    }

    if (!isset($_GET[$getParam]) && isset($_SESSION[$sessionName]) && cleanString($_SESSION[$sessionName]) === $formValue) {
        return "selected";
    }

    return null;
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
    foreach($GLOBALS as $varName => $value) {
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
 * If the user is not logged in, redirects to the login page.
 */
function requireLogin() {
    if (!isset($_SESSION['user_role'])) {
        header("Location: /ticketing-system/public/forms/login.php");
        die;
    }
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
 * Detrminates whether the user is in the admin or in the user panel
 * 
 * @return string "admin" if the user is in the admin panel, "user" otherwise.
 */
function getPanel(): string
{
    return str_contains($_SERVER["REQUEST_URI"], "public/admin") ? "admin" : "user";
}

/**
 * Renders a single dashboard card in the admin panel.
 *   
 * @param string $icon. Icon code for Materila Design Icons. 
 * 
 * @param string $label      Name of the ticket category shown on the card (e.g. "Solved").
 * @param int    $count      Total number of tickets that belong to the given $label category.
 * @param string $iconColor  Tailwind CSS class for icon color (e.g. "text-blue-500").
 * @param string $icon       Material Design Icon class (e.g. "mdi-ticket").
 */

function renderDashboardCard (
    string $label, 
    int $count, 
    string $iconColor, 
    string $icon)
{
    include '../../partials/_admin_dashboard_card_widget.php';
}

/**
 * Loads ticket-related names (statuses, priorities, departments) from the database.
 * Useful for building filter dropdowns and preparing allowed values.
 *
 * @return array{
 *     statuses: string[],
 *     priorities: string[],
 *     departments: string[]
 * }
 */
function loadTicketFilterData(): array
{
    // Initialize allowed filter values for tickets
    $status = new Status();
    $statuses = $status->getAllStatusNames();

    $priority = new Priority();
    $priorities = $priority->getAllPriorityNames();

    $department = new Department();
    $departments = $department->getAllDepartmentNames();

    return [
        "statuses" => $statuses, 
        "priorities" => $priorities, 
        "departments" => $departments, 
    ];
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
 * Renders chart.
 * 
 * @param string $title Chart name.
 * @param string $type Chart type (e.g. "line", "bar", etc.).
 * @param array $data Array of data prepared for rendering the chart.
 *   Structure:
 *   - 'labels': array of strings — labels for the X-axis (e.g. months).
 *   - 'datasets': array of arrays — each dataset includes:
 *       - 'label': string — name of the dataset (e.g. ticket status).
 *       - 'data': array of integers — integer values matching the labels.
 *
 * Example:
 * [
 *   'labels' => ['Jan', 'Feb', 'Mar', ..., 'Dec'],
 *   'datasets' => [
 *     [
 *       'label' => 'Open',
 *       'data'  => [12, 7, 3, ...]
 *     ],
 *     // More datasets...
 *   ]
 * ]
 * 
 * @return void
 */
function renderChart(string $title, string $type, array $data): void
{
    $chartId = 'chart_' . uniqid();;
    include '../../partials/_admin_dashboard_chart.php';
}

/**
 * Formats monthly ticket counts for Chart.js.
 * 
 * @param array $created Monthly counts for created tickets.
 *   Format: [
 *     "Jan" => ["created_date" => int],
 *     "Feb" => ["created_date" => int],
 *     // ... all months
 *   ]
 * @param array $solved Monthly counts for solved tickets.
 *   Format: [
 *     "Jan" => ["closed_date" => int],
 *     "Feb" => ["closed_date" => int],
 *     // ... all months
 *   ]
 * 
 * @return array Returns an array with:
 *               - 'opened': array of integers, counts per month
 *               - 'closed': array of integers, counts per month
 */
function formatDataForChartjs(array $created, array $solved): array
{
    $rawData   = [$created, $solved];
    $opened    = [];
    $closed    = [];
    for ($i = 0; $i < count($rawData); $i++) {
        foreach ($rawData[$i] as $month => $filteredMonthlyTickets) {
            foreach ($filteredMonthlyTickets as $filter => $numberOfTickets) {
                if ($filter === "created_date") {
                    $opened[] = $numberOfTickets;
                } elseif ($filter === "closed_date") {
                    $closed[] = $numberOfTickets;
                }
            }
        }
    }

    return ["opened" => $opened, "closed" => $closed];
}