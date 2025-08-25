<?php
require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'classes' . DS . 'Message.php';
require_once ROOT . 'classes' . DS . 'Ticket.php';
require_once ROOT . 'classes' . DS . 'Attachment.php';
require_once ROOT . 'classes' . DS . 'Database.php';

class MessageService extends BaseService
{
    protected Message $messageModel;
    protected Attachment $attachmentModel;
    protected Ticket $ticketModel;
    public array|false $ticketDetails = [];

    public function __construct()
    {
        $sharedPdo = (new Database())->connect();
        $this->messageModel = new Message($sharedPdo);
        $this->attachmentModel = new Attachment($sharedPdo);
        $this->ticketModel = new Ticket($sharedPdo);
    }

    public function validateShow(array $data): array
    {
        // Fetchs message details by ID and check if it exists
        $messageDetails = $this->messageModel->getMessageWithAttachments($data["messageId"]);
        if ($messageDetails === false) {
            return ["success" => false, "message" => "Message not found.", "url" => "index.php"];
        }

        // // Checks if the user is a ticket creator
        // $isCreator = $this->isCreator($messageDetails["ticket"], $data["userId"]);

        $ticket = $this->ticketModel->fetchTicketById($messageDetails["ticket"]);
        $isCreator = $ticket["created_by"] === $data["userId"];

        return [
            "success" => true, 
            "data" => [
                "messageId"      => $data["messageId"],
                "isCreator"      => $isCreator,
                "currentMessage" => $messageDetails
            ]
        ];
    }

    /**
     * Fetches ticket details by ID.
     * 
     * @param int $ticketId ID of the ticket to fetch.
     * @return void
     * @throws RuntimeException If query execution fails.
     * @see Ticket::fetchTicketDetails()
     */
    public function fetchTicketDetails(int $ticketId): void
    {
        $this->ticketDetails = $this->ticketModel->fetchTicketDetails($ticketId);
    }

    /**
     * Checks if the user is the creator of the ticket.
     * 
     * @param int $ticketId ID of the ticket.
     * @param int $userId ID of the user.
     * @return bool Returns true if the user is the creator, false otherwise.
     * @throws RuntimeException If query execution fails.
     * @see Ticket::fetchTicketDetails()
     */
    public function isCreator(int $ticketId, int $userId): bool
    {
        $ticket = $this->ticketModel->fetchTicketById($ticketId);
        return $ticket["created_by"] === $userId;
    }

    /**
     * Validates the data for the message.
     * 
     * @param array $data Data to validate (body, ticket_id, user_id, user_role).
     * @return array Returns an array with success status and message or validated data.
     * @throws RuntimeException If query execution fails.
     */
    protected function validate(array $data): array
    {
        if ($this->ticketDetails === []) {
            $this->fetchTicketDetails($data["ticketId"]);
        }

        // Validates ticket existence
        if ($this->ticketDetails === false) {
            return [
                "success" => false,
                "message" => "Ticket not found.",
                "ticket_id" => null
            ];
        }

        // Validates message text length
        if ($this->validateTextLength($data["body"], 2, 5000) === false) {
            return [
                "success" => false,
                "message" => "Message body must be between 2 and 5000 characters.",
                "ticket_id" => $data["ticketId"]
            ];
        }

        return [
            "success"    => true,
            "body"       => $data["body"],
            "ticket_id"  => $data["ticketId"],
            "user_role"  => $data["user_role"],
            "user_id"    => $data["user_id"],
            "created_by" => $this->ticketDetails["created_by"]
        ];
    }
}
