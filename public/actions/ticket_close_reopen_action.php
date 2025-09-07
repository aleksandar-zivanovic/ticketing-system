<?php
session_start();
require_once '../../helpers/functions.php';

// Checks if a visitor is logged in.
requireLogin();

require_once '../../controllers/TicketCloseReopenController.php';

$action = null;

$redirectToIndex = function () {
    header("Location: ../index.php");
    die;
};

if (!isset($_POST["reopen_ticket"]) && !isset($_POST["close_ticket"])) {
    $redirectToIndex();
}

if (isset($_POST["close_ticket"]) && $_POST["close_ticket"] === "Close Ticket") {
    $action = "close";
}

if (isset($_POST["reopen_ticket"]) && $_POST["reopen_ticket"] === "Reopen Ticket") {
    $action = "reopen";
}

if ($action != "close" && $action != "reopen") {
    logError("Ticket close-reopen action: The action parameter is invalid! $action is invalid value!");
    $redirectToIndex();
}

if (!isset($_POST["ticket_id"]) || empty($_POST["ticket_id"]) || $_POST["ticket_id"] < 1) {
    $_SESSION["fail"] = "Missing or incorrect ticket information.";
    $redirectToIndex();
}

// Checks if for the closing action is set $_POST['closingSelect'] and assigned a value to it.
if ($action === "close") {
    if (!isset($_POST['closingSelect']) || empty($_POST['closingSelect'])) {
        $_SESSION["fail"] = "Missing closing type value!";
        $redirectToIndex();
    }
}

// Get user ID from session
$userIdFromSession = (int) $_SESSION['user_id'];

try {
    $controller = new TicketCloseReopenController($_POST, $action);
} catch (\InvalidArgumentException $e) {
    $_SESSION['fail'] = $e->getMessage();
    $redirectToIndex();
}

$validation = $controller->validateRequest($userIdFromSession);

if ($validation["success"] === false) {
    $_SESSION["fail"] = $validation["message"];
    $redirectToIndex();
}

$cleantTicketId = $validation['ticketIdFromForm'];

// Calls close/reopen action
if ($controller->closeReopenTicket($validation["ticketIdFromForm"], $action) === true) {
    $_SESSION["success"] = "Ticket is successfuly {$action}ed";
} else {
    $_SESSION["fail"] = "Failed to {$action} the ticket";
}

if ($controller->isHandler($userIdFromSession) === true) {
    $location = "/ticketing-system/public/admin/view-ticket.php?ticket={$cleantTicketId}";
} else {
    $location = "/ticketing-system/public/user/user-view-ticket.php?ticket={$cleantTicketId}";
}

header("Location: {$location}");
die;
