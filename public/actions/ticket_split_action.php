<?php
require_once '../../helpers/functions.php';
requireLogin();
require_once '../../controllers/TicketSplitController.php';

$splitController = new TicketSplitController();
if ($splitController->validatePostData($_POST) === false) {
    header("Location: /ticketing-system/public/admin/split-ticket.php?ticket=" . cleanString($_POST["error_ticket_id"]));
    die;
}

$splitController->splitTicket($splitController->validatedData);

$_SESSION["success"] = "The ticket is split successfully.";
header("Location: /ticketing-system/public/admin/admin-ticket-listing.php");
die;
