<?php
require_once '../../controllers/TicketCreateController.php';

$createController = new TicketCreateController();

// Sets validation failed redirection path
$failedPath = "../forms/create-ticket.php?source=" . cleanString($_POST["error_page"]);

if ($createController->validateRequest($_POST) === false) {
    header("Location: {$failedPath}");
    die;
}

$createController->createTicket(onTicketCreated: function ($ticketId) {
    $_SESSION["success"] = "New ticket created with ID: {$ticketId}";
    header("Location: /ticketing-system/public/admin/view-ticket.php?ticket={$ticketId}");
    die;
});
