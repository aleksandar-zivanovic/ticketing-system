<?php
session_start();
require_once "../../classes/Ticket.php";
require_once "../../classes/Message.php";
require_once "../../helpers/functions.php";

$location = "../user/user-ticket-listing.php";
if (!isset($_POST["take_ticket"]) || $_POST["take_ticket"] !== "Take the Ticket") 
{
    die(header("Location: {$location}"));
}

// Validate $_POST["take_ticket_id"]
if (
    !isset($_POST["take_ticket_id"]) || 
    !is_numeric($_POST["take_ticket_id"]) || 
    $_POST["take_ticket_id"] < 1
) {
    die(header("Location: {$location}"));
}

$ticketID = intval($_POST["take_ticket_id"]);

$ticket = new Ticket();
$theTicket = $ticket->fetchTicketDetails($ticketID); 

// Validate user's permission to delete the ticket
if((trim($_SESSION["user_role"]) !== "admin") || trim($_SESSION["user_id"]) === $theTicket["created_by"]) {
    $_SESSION["general_nfo"] = "You don't have the permission for this action!";
    die(header("Location: ../user/user-view-ticket.php?ticket={$ticketID}"));
}

if ($ticket->takeTicket($ticketID)) {
    $_SESSION["general_nfo"] = "The ticket ID:{$ticketID} is assigned to you!";
    die(header("Location: ../admin/view-ticket.php?ticket={$ticketID}"));
}