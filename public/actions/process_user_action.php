<?php
session_start();
require_once '../../config/config.php';
require_once ROOT . DS . "classes" . DS . "User.php";
require_once ROOT . DS . "helpers" . DS . "functions.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // saving POST values from the registration form to SESSION
    $exceptions = ['agree_terms', 'registration_form'];
    saveFormValuesToSession($exceptions);

    if (!empty($_POST['user_action']) && ($_POST['user_action'] === "Login" || $_POST['user_action'] === "Register")) {
        $action = $_POST['user_action'];

        if ($action === "Register") {
            // checking if user is agreed with the terms
            if (!$_POST['agree_terms']) {
                $_SESSION["fail"] = "To register an account, you need to agree with the terms.";
                die(header("Location: ../forms/register.php"));
            }

            $user = new User;
            $user->register();
        } elseif ($action === "Login") {
            $user = new User;
            try {
                $user->login();
            } catch (\InvalidArgumentException $e) {
                $_SESSION["fail"] = $e->getMessage();
                header("Location: ../forms/register.php");
                die;
            } catch (\PDOException $e) {
                $_SESSION["fail"] = $e->getMessage();
                header("Location: ../forms/register.php");
                die;
            }
        }
    } else {
        $_SESSION["fail"] = "Invalid action!";
        die(header("Location: ../forms/register.php"));
    }
} else {
    die(header("Location: ../"));
}
