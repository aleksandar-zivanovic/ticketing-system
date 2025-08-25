<?php
require_once 'MessageService.php';

class MessageCreateService extends MessageService
{
    /**
     * Validates the data for creating a message.
     * 
     * @param array $data Data to validate (body, user_id, user_role).
     * @return array Returns an array with success status and message or validated data.
     * @throws RuntimeException If query execution fails.
     * @see MessageService::validate()
     */
    public function validate(array $data): array
    {
        $validation = parent::validate($data);

        if ($validation["success"] === false) {
            return $validation;
        }

        // Validates if the user is the ticket creator or an admin
        if ($validation["created_by"] !== $data["user_id"] && $data["user_role"] !== "admin") {
            return [
                "success" => false,
                "message" => "You do not have permission to add a message to this ticket.",
            ];
        }
        return $validation;
    }

    /**
     * Uploads files attached to the message.
     * 
     * @param int $messageId ID of the message the files are attached to.
     * @return bool Returns true on success, otherwise false.
     * 
     * @throws Exception If there is an error in upload process, file format or file extension is wrong.
     */
    private function uploadFiles(int $messageId): bool
    {
        // Proccesses files if they are attached in form:
        if ($_FILES["error_images"]["error"][0] != 4) {
            return $this->attachmentModel->processImages($_FILES, $messageId, "message_attachments", "error_images");
        }
        return true;
    }

    /** Creates a new message.
     * 
     * @param string $body Text of the message.
     * @param int $ticketId ID of the ticket the message is related to.
     * @param int $userId ID of the user creating the message.
     * @return void
     * @throws RuntimeException If there is an error during message creation or file upload.
     * @throws Exception If there is an error in upload process, file format or file extension is wrong.
     * @see Message::createMessage()
     */
    public function createMessage(string $body, int $ticketId, int $userId): void
    {
        $messageId = $this->messageModel->createMessage(ticketId: $ticketId, userId: $userId, message: $body);

        $imagesUpload = $this->uploadFiles($messageId);

        if ($imagesUpload === false) {
            $this->messageModel->deleteMessage($messageId);
            throw new RuntimeException("Error uploading message attachments.");
        }
    }
}
