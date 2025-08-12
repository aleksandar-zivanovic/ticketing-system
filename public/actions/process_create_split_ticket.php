<?php
session_start();
require_once('../../helpers/functions.php');
requireLogin();
require_once('../../config/config.php');
require_once('../../classes/Ticket.php');

if (
    $_SERVER['REQUEST_METHOD'] !== "POST" ||
    !isset($_POST['user_action']) ||
    !in_array($_POST['user_action'], ["Create Ticket", "Split Ticket"])
) {
    header("Location: ../");
    die;
}

// Sets error log message.
$errorMessage = fn ($name) => "'{$name}' is missing by user {$_SESSION['user_email']} with IP: " . getIp();

// $_POST["error_page"] is common for both actions and is hiddend input field.
if (!isset($_POST["error_page"]) || empty($_POST["error_page"])) {
    logError($errorMessage("error_page"));
    http_response_code(403);
    die('Forbidden action!');
}

saveFormValuesToSession();

$errors = ["error_department", "error_priority", "error_title", "error_description"];
$errorField = "";
$ticket = new Ticket();

if ($_POST['user_action'] === "Create Ticket") {
    // Creates ticket.

    foreach ($errors as $key => $value) {
        if (!isset($_POST[$value]) || empty($_POST[$value])) {
            $errorField = $value;
            goto fail;
        }
    }

    $ticket->createTicket();
}

if ($_POST['user_action'] === "Split Ticket") {
    // Splits a ticket.

    // Only $_POST["error_page"][0] has a string value; [1], [2], etc. are empty.
    if (!isset($_POST["error_page"][0]) || empty($_POST["error_page"][0])) {
        logError($errorMessage("\$_POST[\"error_page\"][0]"));
        http_response_code(403);
        die('Forbidden action!');
    }

    $splitPageRedirect = fn () => header("Location: ../admin/split-ticket.php?ticket=" . cleanString($_POST["error_ticket_id"]));

    $theTicket = $ticket->fetchTicketDetails(filter_input(INPUT_POST, "error_ticket_id", FILTER_VALIDATE_INT));

    // Checks if the ticket is created during splitting process.
    if ($ticket->isCreatedBySplitting($theTicket)) {
        $_SESSION["fail"] = "Splitting of a ticket created through splitting process is forbidden.";
        $splitPageRedirect();
        die;
    }

    // Checks if there is data for at least two tickets
    if ((count($_POST["error_title"]) < 2)) {
        $_SESSION["fail"] = "Splitting requires at least two tickets to be created.";
        $splitPageRedirect();
        die;
    }

    // Allows splitting tickets with statuses in progress and waiting.
    if (!in_array($theTicket["statusId"], [1, 2])) {
        $_SESSION["fail"] = "Only tickets with statuses in progress and waiting, can be split.";
        $splitPageRedirect();
        die;
    }

    // Forbid splitting already split tickets.
    if ($ticket->hasChildren($theTicket["id"])) {
        $_SESSION["fail"] = "Ticket is already split. Can't split the same ticket twice.";
        $splitPageRedirect();
        die;
    }

    // Handles hidden inputs values - strings
    $errorsHiddenStrings = ["error_user_id", "error_ticket_id"];
    foreach ($errorsHiddenStrings as $value) {
        if (!isset($_POST[$value]) || empty($_POST[$value])) {
            logError($errorMessage($value));
            http_response_code(403);
            die('Forbidden action!');
        }
    }

    // Handles inputs values - arrays
    foreach ($errors as $name) {
        if (!isset($_POST[$name]) || !is_array($_POST[$name])) {
            $errorField = $name;
            goto fail;
        }

        foreach ($_POST[$name] as $key => $value) {
            if (!isset($_POST[$name][$key]) || empty($value)) {
                logError($errorMessage($name . "[" . $key . "]"));
                $errorField = $name;
                goto fail;
            }
        }
    }

    $ticket->splitTicket();
}

fail:
// $_SESSION["fail"] is session message for both actions.
$_SESSION["fail"] = ucfirst(str_replace("error_", "", $errorField)) . " field must not be empty!";
if ($_POST['user_action'] === "Split Ticket") {
    header("Location: ../admin/split-ticket.php?ticket=" . cleanString($_POST["error_ticket_id"]));
} else {
    header("Location: ../forms/create-ticket.php?source=" . cleanString($_POST["error_page"]));
}
die;
