<?php
session_start();
require_once('../../classes/User.php');
require_once('../../helpers/functions.php');

if ($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['verification_code_form']) {
    $user = new User;
    $result = $user->resendVerificatonCode();
} else {
    die(header("Location: ../forms/resend-code.php"));
}