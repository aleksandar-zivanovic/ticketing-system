<?php
session_start();
require_once('../../config/config.php');
require_once('../../classes/Message.php');
require_once('../../helpers/functions.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['create_message']) && ($_POST['create_message'] === "Send Message")) {
        $ticketId = filter_input(INPUT_POST, "ticketId", FILTER_VALIDATE_INT);
        if ($ticketId == false || $ticketId < 1) {
            throw new DomainException ("Invalid ticket ID.");
        }

        $message = new Message();
        $message->createMessage($ticketId);
    }
} else {
    header("Location: ../");
    die();
}