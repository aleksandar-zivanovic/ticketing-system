<?php
session_start();
require_once '../classes/Ticket.php';
require_once '../helpers/functions.php';
require_once '../services/TicketCloseReopenService.php';

// Checks if a visitor is logged in.
requireLogin();

$action = null;

$redirectToIndex = function () {
    header("Location: ../public/index.php");
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
    logError("TicketCloseReopenController.php: The action parameter is invalid! $action is invalid value!");
    $redirectToIndex();
}

// Get ticket ID from the form
if (isset($_POST["ticket_id"]) && $_POST["ticket_id"] > 0) {
    $ticketIdFromForm = (int) filter_input(INPUT_POST, "ticket_id", FILTER_SANITIZE_NUMBER_INT);
} else {
    $redirectToIndex();
}

// Validate $_SESSION['user_id']
if (!isset($_SESSION['user_id']) || (int) $_SESSION['user_id'] < 1) {
    $redirectToIndex();
}

// Get user ID from session
$userIdFromSession = (int) $_SESSION['user_id'];

$ticketService = new TicketCloseReopenService($ticketIdFromForm);

if ($action === "close") {
    // Verifies whether the ticket has "in progress".
    $ticketService->isTheTicketInProgress();
}

// Verifies whether the user is the creator of the ticket or the admin who handles the ticket.
if ($ticketService->notCreatorOrHandler($userIdFromSession)) {
    $redirectToIndex();
}

if ($ticketService->theTicket["handled_by"] == $userIdFromSession) {
    $location = "Location: ../public/admin/view-ticket.php?ticket={$ticketIdFromForm}";
} else {
    $location = "Location: ../public/user/user-view-ticket.php?ticket={$ticketIdFromForm}";
}

try {
    $ticketService->closeReopenTicket($ticketIdFromForm, $action);
    $_SESSION['success'] = "Ticket successfully " . ($action === "close" ? "closed!" : "opened!");
} catch (\InvalidArgumentException | \DomainException | \Exception $e) {
    $_SESSION['fail'] = "Unable to process request.";
}

header($location);
die;
