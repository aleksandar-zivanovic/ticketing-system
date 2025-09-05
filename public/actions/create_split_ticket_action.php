<?php
session_start();
require_once '../../helpers/functions.php';
// Checks if a visitor is logged in.
requireLogin();
require_once '../../config/config.php';
require_once '../../classes/Ticket.php';


if (
    $_SERVER['REQUEST_METHOD'] !== "POST" ||
    !isset($_POST['user_action']) ||
    !in_array($_POST['user_action'], ["Create Ticket", "Split Ticket"])
) {
    redirectAndDie(path: "../../index.php");
}

// Sets error log message.
$errorMessage = fn($name) => "'{$name}' is missing by user {$_SESSION['user_email']} with IP: " . getIp();

// $_POST["error_page"] is common for both actions and is hiddend input field.
if (!isset($_POST["error_page"]) || empty($_POST["error_page"])) {
    logError($errorMessage("error_page"));
    http_response_code(403);
    die('Forbidden action!');
}

saveFormValuesToSession();

// Create ticket
if ($_POST['user_action'] === "Create Ticket") {
    require_once 'ticket_create_action.php';
}

// Split ticket
if ($_POST['user_action'] === "Split Ticket") {
    require_once 'ticket_split_action.php';
}
