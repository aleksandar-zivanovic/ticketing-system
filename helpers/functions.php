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