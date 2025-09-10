<?php
require_once '../../helpers/functions.php';
requireLogin();
require_once '../../controllers/TicketSplitController.php';

$splitController = new TicketSplitController();
if ($splitController->validatePostData($_POST) === false) {
    header("Location: /ticketing-system/public/admin/split-ticket.php?ticket=" . cleanString($_POST["error_ticket_id"]));
    die;
}

try {
    $splitController->splitTicket($splitController->validatedData);
} catch (\Throwable $th) {
    redirectAndDie("/ticketing-system/public/admin/split-ticket.php?ticket=" . $splitController->ticketId, "Ticket splitting failed. Please try again.");
}

$_SESSION["success"] = "The ticket is split successfully.";
header("Location: /ticketing-system/public/admin/admin-ticket-listing.php");
die;
