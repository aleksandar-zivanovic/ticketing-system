<?php
session_start();
require_once '../../helpers/functions.php';
require_once '../../classes/Message.php';
require_once '../../classes/Attachment.php';
require_once '../../config/config.php';

// Checks if a visitor is logged in.
requireLogin();

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
    || !isset($_POST["message_id"])
    || !isset($_POST["ticket_id"])
    || !isset($_POST["body"])
    || strlen($_POST["body"]) < 2
) {
    die(header("Location: ../index.php"));
}

// Message content
$body = cleanString(filter_input(INPUT_POST, "body", FILTER_DEFAULT));

// Message ID from form
$messageId = filter_input(INPUT_POST, "message_id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($messageId === false) throw new Exception("Invalid value!");

// Ticket ID from form
$ticketId = filter_input(INPUT_POST, "ticket_id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($ticketId === false) throw new Exception("Invalid value!");

// Get array of attachment ID's for deletion from the form
$sanitizedIds = [];
if (!empty($_POST["image_ids"])) {
    // Remove signs `[` and `]` from string
    $idsWithoutBrackets = str_replace(["[", "]"], "", $_POST["image_ids"]);

    // Sanatize string
    $cleanIds = cleanString($idsWithoutBrackets);
    $cleanIdsArray = explode(",", $cleanIds);

    foreach ($cleanIdsArray as $value) {
        // Validate values
        $validateId = filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

        if ($validateId === false) {
            throw new Exception("Invalid value!");
        } else {
            // Collect values
            $sanitizedIds[] = $validateId;
        }
    }
}

$message = new Message();

// Check if the current message is the last ticket's message
$messagesIds = $message->getAllMessageIdsByTicket($ticketId);
if ($messageId !== max($messagesIds))  throw new Exception("Invalid message selected!");

// Fetch all details for the message with message ID got from the form
$theMessage = $message->getMessageWithAttachments($messageId);

// Throw exception if the message with the messsage ID from the form doesn't exist
if (count($theMessage) < 1) throw new \Exception("Invalid message!");

// Convert the string of attachment IDs from the message fetched from the database into an array.
$existingIds = explode(",", $theMessage["attachment_id"]);

// Convert the string of attachment file names from the message fetched from the database into an array.
$existingFiles = explode(",", $theMessage["file"]);

// Compare if ID's of selected images for deletion from form with real attachment ID's from database
$compare = empty(array_diff($sanitizedIds, $existingIds)) ? true : false;
if ($compare === false) throw new Exception('Invalid images for deletion!');

// Get current user ID from $_SESSION
$userIdFromSession = intval(cleanString($_SESSION["user_id"]));
if ($userIdFromSession < 1) throw new Exception('Invalid user ID offset from the session!');

// Forbid editing to non creator users
if ($userIdFromSession !== $theMessage["user"]) throw new Exception('Not authorized!');

// Update text message
if ($theMessage['body'] !== $body) $message->editMessage($messageId, $body);

$attachment = new Attachment();

// Delete images if there're images chosen for deletion
if (!empty($sanitizedIds)) {

    // Delete files from database.
    $attachment->deleteAttachmentsFromDbById($sanitizedIds, 'message_attachments');

    // Create an array of file IDs and key and files that belongs to those IDs in the database
    $existingFilesWithIds = array_combine($existingIds, $existingFiles);

    $fileNamesForDeletion = [];
    foreach ($existingFilesWithIds as $key => $value) {
        if (in_array($key, $sanitizedIds)) {
            $fileNamesForDeletion[] = $value;
        }
    }

    // Delete files from server
    $attachment->deleteAttachmentsFromServer($fileNamesForDeletion);
}

// Upload files process
if (!empty($_FILES["error_images"]["name"][0])) {
    if ($attachment->processImages($_FILES, $messageId, "message_attachments", "error_images") === false) {
        throw new \RuntimeException("Files upload failed!");
    }
}

if (str_contains($_SERVER["HTTP_REFERER"], "admin")) {
    header("Location: .." . DS . "admin" . DS . "view-ticket.php?ticket={$ticketId}");
    die;
} else {
    header("Location: .." . DS . "user" . DS . "user-view-ticket.php?ticket={$ticketId}");
    die;
}

?>