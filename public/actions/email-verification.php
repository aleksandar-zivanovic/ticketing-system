<?php
session_start();
require_once '../../classes/User.php';
require_once '../../helpers/functions.php';

// Checks if a visitor is logged in.
requireLogin();

$user = new User();
$verificationResult = $user->makeUserVerified() ? "login" : "forms/register";

// redirection after verification process
header('Location: ../' . $verificationResult . '.php');