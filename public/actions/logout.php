<?php
session_start();
require_once '../../helpers/functions.php';

// Call log out functionalitiy
if (isset($_POST['logout'])) {
    logout('../forms/login.php');
} else {
    header("Location: ../index.php");
    die;
}