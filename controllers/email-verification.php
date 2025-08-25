<?php
session_start();
require_once '../classes/User.php';
require_once '../helpers/functions.php';

$user = new User();
$verificationResult = $user->makeUserVerified() ? "login" : "public/forms/register";

// redirection after verification process
header('Location: ../' . $verificationResult . '.php');