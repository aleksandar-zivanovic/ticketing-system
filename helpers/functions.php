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

// adding POST values from a form to SESSION variables
function saveFormValuesToSession(array $exceptions): void
{
    foreach($_POST as $key => $value) {
        if(!in_array($key, $exceptions)) {
            $_SESSION[$key] = $value;
        }
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