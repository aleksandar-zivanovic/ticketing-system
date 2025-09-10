<?php
require_once '../../helpers/functions.php';
requireLogin();

require_once '../../controllers/TicketController.php';

$controller = new TicketController();

// Sets validation failed redirection path
$failedPath = "../forms/create-ticket.php?source=" . cleanString($_POST["error_page"]);

if ($controller->validateCreateRequest($_POST) === false) {
    header("Location: {$failedPath}");
    die;
}

// Creates the ticket and redirects to the ticket view page on success
try {
    $ticketId = $controller->createTicket(false);
    redirectAndDie(
        "/ticketing-system/public/admin/view-ticket.php?ticket={$ticketId}",
        "New ticket created with ID: {$ticketId}",
        "success"
    );
} catch (RuntimeException $e) {
    redirectAndDie($failedPath, "Ticket creation failed. Please try again.");
}
