<?php
session_start();
require_once '../../classes/User.php';

$user = new User();
$verificationResult = $user->makeUserVerified() ? "login" : "forms/register";;

// redirection after verification process
header('Location: ../' . $verificationResult . '.php');