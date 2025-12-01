<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';
require_once ROOT . 'services' . DS . 'MessageCreateService.php';
require_once ROOT . 'services' . DS . 'MessageEditService.php';

class MessageController extends BaseController
{
    private MessageCreateService|MessageEditService $service;

    public function validateShowRequest(): array
    {
        if ($_SERVER["REQUEST_METHOD"] !== "GET") {
            return ["success" => false, "message" => "Invalid request method.", "url" => "index.php"];
        }

        if (!isset($_GET['message']) || empty(trim($_GET['message']))) {
            return ["success" => false, "message" => "Missing message ID.", "url" => "index.php"];
        }

        $data["messageId"] = $this->validateId($_GET['message']);
        if ($data["messageId"] === false) {
            return ["success" => false, "message" => "Invalid message ID.", "url" => "index.php"];
        }

        $data["userId"] = (int) $_SESSION["user_id"];

        $service = new MessageService();
        return $service->validateShow($data);
    }

    public function show(): void
    {
        $validation = $this->validateShowRequest();
        $this->handleValidation($validation);
        $validation["data"]["panel"] = $validation["data"]["isCreator"] === true ? 'user' : 'admin';

        $this->render("message_edit.php", $validation["data"]);
    }

    /**
     * Validates the incoming request data and initializes the appropriate service (create or edit).
     * 
     * @return array Returns an array with success status and message or validated data.
     * @throws RuntimeException If query execution fails.
     * @see MessageCreateService::validate()
     * @see MessageEditService::validate()
     */
    public function validateRequest(): array
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return ["success" => false, "message" => "Invalid request method."];
        }

        $action = null;
        if (isset($_POST["create_message"]) && trim($_POST["create_message"]) === "Send Message") {
            $action = "create_message";
        }

        if (isset($_POST["edit_message"]) && trim($_POST["edit_message"]) === "edit") {
            $action = "edit_message";
        }

        if ($action === null) {
            return ["success" => false, "message" => "Invalid form submission."];
        } elseif ($action === "edit_message") {
            // Initialize service for editing a message
            $this->service = new MessageEditService();
        } elseif ($action === "create_message") {
            // Initialize service for creating a message
            $this->service = new MessageCreateService();
        }

        // Validates and sanitizes ticket ID
        if (!isset($_POST["ticketId"]) || empty(trim($_POST["ticketId"]))) {
            return ["success" => false, "message" => "Missing ticket ID."];
        }

        $values["ticketId"] = $this->validateId($_POST["ticketId"]);
        if ($values["ticketId"] === false) {
            return ["success" => false, "message" => "Invalid ticket ID."];
        }

        // Validates and sanitizes message body
        if (!isset($_POST["body"]) || empty(trim($_POST["body"]))) {
            return ["success" => false, "message" => "Message is required.", "ticket_id" => $values["ticketId"]];
        }
        $values["body"] = cleanString($_POST["body"]);

        // Validates and sanitizes user ID
        if (!isset($_SESSION["user_id"]) || empty(trim($_SESSION["user_id"]))) {
            return ["success" => false, "message" => "Missing user ID.", "ticket_id" => $values["ticketId"]];
        }

        $values["user_id"] = $this->validateId($_SESSION["user_id"]);
        if ($values["user_id"] === false) {
            return ["success" => false, "message" => "Invalid user ID.", "ticket_id" => $values["ticketId"]];
        }

        $values["user_role"] = cleanString($_SESSION["user_role"]);
        if (empty($values["user_role"])) {
            return ["success" => false, "message" => "Invalid user role.", "ticket_id" => $values["ticketId"]];
        }

        $messageCreatorName = cleanString($_SESSION["user_name"]);
        if (empty($values["user_role"])) {
            return ["success" => false, "message" => "Invalid user role.", "ticket_id" => $values["ticketId"]];
        }

        $messageCreatorSurname = cleanString($_SESSION["user_surname"]);
        if (empty($values["user_role"])) {
            return ["success" => false, "message" => "Invalid user role.", "ticket_id" => $values["ticketId"]];
        }

        $values["message_creator_full_name"] = $messageCreatorName . " " . $messageCreatorSurname;

        // Service selection based on action
        if ($action === "edit_message") {
            // Validates and sanitizes message ID
            if (!isset($_POST["message_id"]) || empty(trim($_POST["message_id"]))) {
                return ["success" => false, "message" => "Chosen message is not set.", "ticket_id" => $values["ticketId"]];
            }

            $values["message_id"] = $this->validateId($_POST["message_id"]);
            if ($values["message_id"] === false) {
                return ["success" => false, "message" => "Invalid message ID.", "ticket_id" => $values["ticketId"]];
            }

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
                    $validateId = $this->validateId($value);

                    if ($validateId === false) {
                        return ["success" => false, "message" => "Invalid image for deletion.", "ticket_id" => $values["ticketId"]];
                    }

                    // Collect values
                    $sanitizedIds[] = $validateId;
                }
            }

            $values["sanitizedIds"] = $sanitizedIds;
        }

        // Service-level validation
        return $this->service->validate($values);
    }

    /**
     * Executes the appropriate action (create or edit message) based on the initialized service.
     * 
     * @param string $body The message body.
     * @param int $ticketId The ticket ID.
     * @param int $userId The user ID.
     * @param int|null $messageId The message ID (required for editing).
     * @return array Returns an array with success status and message.
     * @throws RuntimeException If there is an error during message creation/edit or file upload.
     * @throws Exception If there is an error in upload process, file format or file extension is wrong.
     * @see MessageCreateService::createMessage()
     * @see MessageEditService::editMessage()
     */
    public function execute()
    {
        $validation = $this->validateRequest();

        $this->redirectUrl =
            $validation["message_creator"] == trim($_SESSION["user_id"]) ?
            BASE_URL . "user/user-view-ticket.php?ticket=" . $validation["ticket_id"] :
            BASE_URL . "admin/view-ticket.php?ticket=" . $validation["ticket_id"];

        $this->handleValidation($validation);

        try {
            if ($this->service instanceof MessageCreateService) {
                $this->service->createMessage($validation);
                $message = "Message created successfully.";
            } elseif ($this->service instanceof MessageEditService) {
                $this->service->editMessage($validation);
                $message = "Message edited successfully.";
            }

            redirectAndDie($this->redirectUrl, $message, "success");
        } catch (\Throwable $th) {
            redirectAndDie($this->redirectUrl, "Message could not be processed. Try again later.", "fail");
        }
    }
}
