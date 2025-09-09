<?php
session_start();
require_once '../../config/config.php';
require_once ROOT . DS . 'helpers' . DS . 'functions.php';

// Checks if a visitor is logged in.
requireLogin();

require_once ROOT . DS . 'controllers' . DS . 'TicketController.php';
require_once ROOT . DS . 'classes' . DS . 'Ticket.php';

if (!isset($_POST["delete_ticket"]) || $_POST["delete_ticket"] !== "Delete Ticket") {
    redirectAndDie("../index.php");
}

$controller = new TicketController();

// Validates ticket ID from post request and user ID from session.
$validTicketId = $controller->validateId($_POST["ticket_id"]);
$validUserId   = $controller->validateId($_SESSION["user_id"]);
if (!$validTicketId || !$validUserId) {
    redirectAndDie("../index.php");
}

// Logic validation
try {
    $validationResult = $controller->validateDeleteRequest($validTicketId, $validUserId);
} catch (\Throwable $th) {
    // Sets fail message and redirect if the deletion completed unsuccessfully 
    redirectAndDie("/ticketing-system/public/", "Delete action failed.", "fail");
}

// Sets redirection URL
$panel = $validationResult["panel"];
$redirectionUrl = $validationResult["panel"] === "admin" ? "../admin/admin-ticket-listing.php" : "../user/user-ticket-listing.php";

try {
    $controller->deleteTicket($validTicketId);
    redirectAndDie($redirectionUrl, "Ticket with ID {$validTicketId} is deleted successfully!", "success");
} catch (\Throwable $th) {
    redirectAndDie($redirectionUrl, "Something get wrong. Try again.", "fail");
}