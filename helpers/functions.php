<?php

function cleanString(string $string): string
{
    return htmlspecialchars(trim($string));
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

// handling session messages
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
function logError(string $message, array|string|null $errorInfo = null): void
{
    // check if the directory exists and create if not
    $logDirectory = __DIR__ . '../../logs';
    checkAndCreateDirectory($logDirectory);

    // preparing final message
    if (!empty($errorInfo)) {
        $message .= is_array($errorInfo) ? " | PDO error: " . implode(", ", $errorInfo) : $message;
    }

    // writting down an error to the log file
    $logFile = $logDirectory . '/php_errors.log';
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
    string|int|null $value = null, 
    ?string $image = null
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