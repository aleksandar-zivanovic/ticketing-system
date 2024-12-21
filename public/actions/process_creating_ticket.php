<?php
require_once('../../classes/Ticket.php');
require_once('../../helpers/functions.php');

session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // saving POST values from the registration form to SESSION
    saveFormValuesToSession();

    if (isset($_POST['user_action']) && ($_POST['user_action'] === "Create Ticket")) {
        $ticket = new Ticket();
        $ticket->createTicket();
    }
} else {
    die(header("Location: ../"));
}