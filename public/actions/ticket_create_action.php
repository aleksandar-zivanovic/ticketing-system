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

$controller->createTicket(onTicketCreated: function ($ticketId) {
    $_SESSION["success"] = "New ticket created with ID: {$ticketId}";
    header("Location: /ticketing-system/public/admin/view-ticket.php?ticket={$ticketId}");
    die;
});
