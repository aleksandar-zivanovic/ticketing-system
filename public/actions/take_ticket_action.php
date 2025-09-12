<?php
session_start();
require_once "../../config/config.php";
require_once ROOT . DS . DS . "classes" . DS . "Ticket.php";
require_once ROOT . DS . DS . "classes" . DS . "Message.php";
require_once ROOT . DS . DS . "helpers" . DS . "functions.php";
require_once ROOT . DS . DS . "controllers" . DS . "TicketTakeController.php";

$location = "../user/user-ticket-listing.php";

// Checks if a visitor is logged and is an admin.
checkAuthorization("admin", $location);

if (!isset($_POST["take_ticket"]) || $_POST["take_ticket"] !== "Take the Ticket") {
    die(header("Location: {$location}"));
}

// Validate the presence of the ticket ID in the POST request
if (
    !isset($_POST["take_ticket_id"]) ||
    empty($_POST["take_ticket_id"])
) {
    redirectAndDie($location);
}

$controller = new TicketTakeController();
try {
    $validationResult = $controller->validateRequest($_POST["take_ticket_id"], $_SESSION["user_id"]);
} catch (\Throwable $th) {
    redirectAndDie($location, "An error occurred while validating the ticket.");
}

if ($validationResult["success"] === false) {
    redirectAndDie($location, $validationResult["message"]);
}

$ticketID = $validationResult["take_ticket_id"];

try {
    $controller->takeTicket();
    redirectAndDie("../admin/view-ticket.php?ticket={$ticketID}", "The ticket ID:{$ticketID} is assigned to you!", "success");
} catch (\Throwable $th) {
    redirectAndDie($location, "An error occurred while validating the ticket.");
}
