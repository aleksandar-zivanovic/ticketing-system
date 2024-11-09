<?php
require_once('../../classes/User.php');
require_once('../../helpers/functions.php');

session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // saving POST values from the registration form to SESSION
    $exceptions = ['agree_terms', 'registration_form'];
    saveFormValuesToSession($exceptions);

    if (!empty($_POST['user_action']) && ($_POST['user_action'] === "Login" || $_POST['user_action'] === "Register")) {
        $action = $_POST['user_action'];

        if ($action === "Register") {
            // checking if user is agreed with the terms
            if (!$_POST['agree_terms']) {
                $_SESSION['error_message'] = "To register an account, you need to agree with the terms.";
                die(header("Location: ../forms/register.php"));
            }

            $user = new User;
            $user->register();
        } elseif ($action === "Login") {
            $user = new User;
            $user->login();
        }
    } else {
        $_SESSION['error_message'] = "Invalid action!";
        die(header("Location: ../forms/register.php"));
    }
} else {
    die(header("Location: ../"));
}