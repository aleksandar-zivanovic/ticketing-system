<?php
session_start();
require_once '../helpers/functions.php';
require_once '../classes/User.php';


// Checks if a visitor is logged in.
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['verification_code_form']) {
    $user = new User;
    $result = $user->resendVerificatonCode();
} else {
    die(header("Location: ../public/forms/resend-code.php"));
}