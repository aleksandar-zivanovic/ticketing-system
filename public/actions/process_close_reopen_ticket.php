<?php
session_start();
require_once('../../classes/Ticket.php');
require_once('../../helpers/functions.php');

$action = null;

if (isset($_POST["close_ticket"]) && $_POST["close_ticket"] === "Close Ticket") {
    $action = "close";
}

if (isset($_POST["reopen_ticket"]) && $_POST["reopen_ticket"] === "Reopen Ticket") {
    $action = "reopen";
}

if ($action === null) die(header("Location: ../index.php"));

// Get ticket ID from the form
if (isset($_POST["ticket_id"]) && $_POST["ticket_id"] > 0) {
    $ticketIdFromForm = (int) filter_input(INPUT_POST, "ticket_id", FILTER_SANITIZE_NUMBER_INT);
} else {
    die(header("Location: ../index.php"));
}

// Validate $_SESSION['user_id']
if (!isset($_SESSION['user_id']) || !is_int($_SESSION['user_id']) || $_SESSION['user_id'] < 1) {
    die(header("Location: ../index.php"));
}

// Get user ID from session
$userIdFromSession = $_SESSION['user_id'];

$ticket = new Ticket();
$theTicket = $ticket->fetchTicketDetails($ticketIdFromForm);

// Verifies whether the ticket has "in progress" or "closed" status.
if ($theTicket["statusId"] === 1 || $theTicket["statusId"] === 4) {
    throw new Exception("Only tickets with `in progress` status can be deleted!");
}

// Verifies whether the user is the creator of the ticket or the admin who handles the ticket.
if ($theTicket["handled_by"] != $userIdFromSession && $theTicket["created_by"] != $userIdFromSession) {
    die(header("Location: ../index.php"));
}

if ($theTicket["handled_by"] == $userIdFromSession) {
    $location = "Location: ../admin/view-ticket.php?ticket={$ticketIdFromForm}";
} else {
    $location = "Location: ../user/user-view-ticket.php?ticket={$ticketIdFromForm}";
}

// Closes / reopens the ticket
if ($action === "close" || $action === "reopen") {
    $result = $ticket->closeReopenTicket($ticketIdFromForm, $action);
    if ($result === false) {
        throw new Exception("Ticket {$action} failed. Try again!");
    }
    die(header($location));
}