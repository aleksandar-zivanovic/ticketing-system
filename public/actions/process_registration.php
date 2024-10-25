<?php
require_once('../../classes/User.php');
require_once('../../helpers/functions.php');

session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // saving POST values from the registration form to SESSION
    $exceptions = ['agree_terms', 'registration_form'];
    saveFormValuesToSession($exceptions);

    if (!$_POST['agree_terms']) {
        $_SESSION['error_message'] = "To register an account, you need to agree with the terms.";
        die(header("Location: ../forms/register.php"));
    }

    if ($_POST['registration_form']) {
        $user = new User;
        $user->register();
    } else {
        die(header("Location: ../forms/register.php"));
    }
}