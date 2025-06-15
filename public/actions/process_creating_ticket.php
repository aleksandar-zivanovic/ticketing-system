<?php
session_start();
require_once('../../config/config.php');
require_once('../../classes/Ticket.php');
require_once('../../helpers/functions.php');

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