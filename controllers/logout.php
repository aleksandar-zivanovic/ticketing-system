<?php
session_start();
require_once '../helpers/functions.php';

// Checks if a visitor is logged in.
requireLogin();

// Call log out functionalitiy
if (isset($_POST['logout'])) {
    logout('../public/forms/login.php');
} else {
    header("Location: ../public/index.php");
    die;
}