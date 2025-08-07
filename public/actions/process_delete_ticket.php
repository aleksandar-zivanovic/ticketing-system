<?php
session_start();
require_once '../../config/config.php';
require_once '../../helpers/functions.php';
require_once '../../classes/Ticket.php';

if (!isset($_POST["delete_ticket"]) || $_POST["delete_ticket"] !== "Delete Ticket") {
    die(header("Location: ../index.php"));
}

// Validates ticket ID.
$validId = filter_input(INPUT_POST, "ticket_id", FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$validId) {
    die(header("Location: ../index.php"));
}

$ticket = new Ticket();

if ($ticket->deleteTicket($validId)) {
    // Set success message
    $_SESSION["success"] = "Ticket with ID {$validId} is deleted successfully!";

    // Redirect if the deletion completed successfully 
    $panel = trim($_SESSION["user_role"] === "admin") ? "admin" : "user";
    $redirectionUrl = $panel === "admin" ? "../admin/admin-ticket-listing.php" : "../user/user-ticket-listing.php";
    die(header("Location: {$redirectionUrl}"));
}
?>