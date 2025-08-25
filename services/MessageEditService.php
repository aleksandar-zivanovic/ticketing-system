<?php
require_once 'MessageService.php';

class MessageEditService extends MessageService
{

    /**
     * Validates the data for editing a message.
     * 
     * @param array $data Data to validate (message_id, ticketId, body, user_id, user_role, sanitizedIds).
     * @return array Returns an array with success status and message or validated data.
     * @throws RuntimeException If query execution fails.
     */
    public function validate(array $data): array
    {
        $validation = parent::validate($data);
        if ($validation["success"] === false) {
            return $validation;
        }

        // Validates ticket existence
        $messageIds = $this->messageModel->getAllMessageIdsByTicket($data["ticketId"]);
        if (empty($messageIds)) {
            return [
                "success" => false,
                "message" => "No messages found for this ticket.",
            ];
        }

        // Validates if the message to be edited is the latest message of the ticket
        if ($data["message_id"] !== max($messageIds)) {
            return [
                "success" => false,
                "message" => "You can only edit the latest message of the ticket.",
            ];
        }

        // Fetch all details for the message with message ID got from the form
        $theMessage = $this->messageModel->getMessageWithAttachments($data["message_id"]);

        // Validates if the user is the message creator
        if ($theMessage["user"] !== $data["user_id"]) {
            return [
                "success" => false,
                "message" => "You do not have permission to edit this message.",
            ];
        }

        // Validates attachment IDs for deletion if any are provided
        if (!empty($data["sanitizedIds"])) {

            // Converts the string of attachment IDs from the message fetched from the database into an array.
            $existingIds = explode(",", $theMessage["attachment_id"]);

            // Converts the string of attachment file names from the message fetched from the database into an array.
            $existingFiles = explode(",", $theMessage["file"]);

            // Compare if ID's of selected images for deletion from form with real attachment ID's from database
            $compare = empty(array_diff($data["sanitizedIds"], $existingIds)) ? true : false;
            if ($compare === false) {
                return [
                    "success" => false,
                    "message" => "Invalid images for deletion!",
                ];
            }
        }

        // Check if there are any changes to be made
        if (empty($data["sanitizedIds"]) && $theMessage["body"] === $data["body"] && empty($_FILES["error_images"]["name"][0])) {
            return [
                "success" => false,
                "message" => "No changes detected to update.",
            ];
        }

        return [
            ...$validation,
            "message_id" => $data["message_id"],
            "existingIds" => $existingIds ?? [],
            "existingFiles" => $existingFiles ?? [],
            "sanitizedIds" => $data["sanitizedIds"] ?? [],
            "ticket_creator" => $validation["created_by"] === $data["user_id"] ? true : false
        ];
    }

    /**
     * Edits a message and handles attachment deletions.
     * 
     * @param array $data Data for editing the message (message_id, ticket_id, body, sanitizedIds, existingIds, existingFiles).
     * @return array Returns an array with success status and message.
     * @throws RuntimeException If query execution fails.
     * @throws Exception If there is an error in upload process, file format or file extension is wrong.
     * @see Attachment::processImages()
     * @see Attachment::deleteAttachmentsFromDbById()
     * @see Attachment::deleteAttachmentsFromServer()
     * @see Message::editMessage()
     */
    public function editMessage(array $data): void
    {
        try {
            // Start transaction
            $this->messageModel->beginTransaction();

            // Update message body
            $this->messageModel->editMessage($data["message_id"], $data["body"]);

            // Upload files process
            if (!empty($_FILES["error_images"]["name"][0])) {
                if ($this->attachmentModel->processImages($_FILES, $data["message_id"], "message_attachments", "error_images") === false) {
                    throw new \RuntimeException("Files upload failed!");
                }
            }

            // Delete images if there're images chosen for deletion
            if (!empty($data["sanitizedIds"])) {
                // Delete files from database.
                if ($this->attachmentModel->deleteAttachmentsFromDbById($data["sanitizedIds"], 'message_attachments') === false) {
                    throw new \RuntimeException("Failed to delete attachments from database.");
                }
            }

            // Commit transaction
            $this->messageModel->commitTransaction();
        } catch (\Throwable $th) {
            // Rollback transaction on error
            $this->messageModel->rollBackTransaction();
            throw $th;
        }

        // Delete images if there're images chosen for deletion
        if (!empty($data["sanitizedIds"])) {
            // Create an array of file IDs and key and files that belongs to those IDs in the database
            $existingFilesWithIds = array_combine($data["existingIds"], $data["existingFiles"]);

            $fileNamesForDeletion = [];
            foreach ($existingFilesWithIds as $key => $value) {
                if (in_array($key, $data["sanitizedIds"])) {
                    $fileNamesForDeletion[] = $value;
                }
            }

            // Delete files from server
            $this->attachmentModel->deleteAttachmentsFromServer($fileNamesForDeletion);
        }
    }
}
