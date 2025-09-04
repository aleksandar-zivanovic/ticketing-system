<?php
session_start();
require_once '../../helpers/functions.php';
// Checks if a visitor is logged in.
requireLogin();
require_once '../../config/config.php';
require_once '../../classes/Ticket.php';


if (
    $_SERVER['REQUEST_METHOD'] !== "POST" ||
    !isset($_POST['user_action']) ||
    !in_array($_POST['user_action'], ["Create Ticket", "Split Ticket"])
) {
    redirectAndDie(path: "../../index.php");
}

// Sets error log message.
$errorMessage = fn($name) => "'{$name}' is missing by user {$_SESSION['user_email']} with IP: " . getIp();

// $_POST["error_page"] is common for both actions and is hiddend input field.
if (!isset($_POST["error_page"]) || empty($_POST["error_page"])) {
    logError($errorMessage("error_page"));
    http_response_code(403);
    die('Forbidden action!');
}

saveFormValuesToSession();

$errors        = ["error_department", "error_priority", "error_title", "error_description"];
$errorField    = "";
$ticket        = new Ticket();

if ($_POST['user_action'] === "Create Ticket") {
    // Creates ticket.

    require_once 'TicketCreateController.php';
    $createController = new TicketCreateController();

    // Redirect path after error in form submission.
    $path = "../forms/create-ticket.php?source=" . cleanString($_POST["error_page"]);

    foreach ($errors as $key => $value) {
        if (!isset($_POST[$value]) || empty($_POST[$value])) {
            $errorField = $value;
            goto fail;
        }
    }

    // Validates the URL from the form input.
    $url = cleanString(filter_input(INPUT_POST, "error_page", FILTER_SANITIZE_URL));
    $url = $createService->validateUrl($url);
    if ($url === false) {
        redirectAndDie(
            sessionMessage: "Provided URL is not valid.",
            type: "fail",
            path: $path
        );
    }

    // Collects and sanitizes rest of data from the form

    $title = cleanString(filter_input(INPUT_POST, "error_title", FILTER_DEFAULT));
    if (!$createService->validateText($title, 10)) {
        redirectAndDie(
            sessionMessage: "Title must be at least 10 characters long.",
            type: "fail",
            path: $path
        );
    }

    $description = cleanString(filter_input(INPUT_POST, "error_description", FILTER_DEFAULT));
    if (!$createService->validateText($description, 20)) {
        redirectAndDie(
            sessionMessage: "Description must be at least 20 characters long.",
            type: "fail",
            path: $path
        );
    }

    $departmentId = (int) cleanString(filter_input(INPUT_POST, "error_department", FILTER_SANITIZE_NUMBER_INT));
    $departmentId = $createService->validateId($departmentId);
    if ($departmentId === false) {
        redirectAndDie(
            sessionMessage: "Selected department is not valid.",
            type: "fail",
            path: $path
        );
    }

    $priorityId = (int) cleanString(filter_input(INPUT_POST, "error_priority", FILTER_SANITIZE_NUMBER_INT));
    $priorityId = $createService->validateId($priorityId);
    if ($priorityId === false) {
        redirectAndDie(
            sessionMessage: "Selected priority is not valid.",
            type: "fail",
            path: $path
        );
    }

    $userId = (int) cleanString(filter_var($_SESSION["user_id"], FILTER_SANITIZE_NUMBER_INT));
    $userId = $createService->validateId($userId);
    if ($userId === false) {
        redirectAndDie(
            sessionMessage: "User ID is not valid.",
            type: "fail",
            path: $path
        );
    }

    $data = [
        'url' => $url,
        'title' => $title,
        'description' => $description,
        'departmentId' => $departmentId,
        'priorityId' => $priorityId,
        'userId' => $userId,
    ];

    // Tries to create a ticket and catches potential exception.
    try {
        $createService->createTicket(data: $data);
        redirectAndDie(
            sessionMessage: "The issue is reported! Thank you!",
            type: "info",
            path: "/ticketing-system/public/user/user-ticket-listing.php"
        );
    } catch (\RuntimeException $e) {
        redirectAndDie(
            sessionMessage: "There was an error while creating the ticket. Please try again later.",
            type: "fail",
            path: $path
        );
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
}

// Split ticket
if ($_POST['user_action'] === "Split Ticket") {
    require_once 'ticket_split_action.php';
}


fail:
// $_SESSION["fail"] is session message for both actions.
$_SESSION["fail"] = ucfirst(str_replace("error_", "", $errorField)) . " field must not be empty!";
header("Location: ../forms/create-ticket.php?source=" . cleanString($_POST["error_page"]));
die;
