<?php
session_start();
require_once('../../config/config.php');
require_once('../../classes/Ticket.php');
require_once('../../helpers/functions.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // saving POST values from the registration form to SESSION
    saveFormValuesToSession();

    $ticket = new Ticket();
    switch ($_POST['user_action'] ?? "") {
        case "Create Ticket":
            $ticket->createTicket();
            break;
        case "Split the ticket":
            $ticket->splitTicket();
            break;
        default:
            logError("Unallowed action on `public/actions/process_creating_ticket.php`");
    }
} else {
    die(header("Location: ../"));
}
