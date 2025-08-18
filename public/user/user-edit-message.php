<?php
session_start();
require_once __DIR__ . "/../../config/config.php";
require_once ROOT . DS . "helpers" . DS . "functions.php";

// Checks if a visitor is logged in.
requireLogin();

$panel = "user";

if (!isset($_GET['message']) || !is_numeric($_GET['message'])) {
    header("Location: ../");
    die();
}

require_once ROOT . DS . "partials" . DS . "_message-edit.php";
