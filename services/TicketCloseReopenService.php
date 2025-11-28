<?php
require_once ROOT . 'classes' . DS . 'Ticket.php';
require_once ROOT . 'classes' . DS . 'User.php';
require_once ROOT . 'classes' . DS . 'Message.php';
require_once ROOT . 'services' . DS . 'TicketNotificationsService.php';

class TicketCloseReopenService
{
    private Ticket $ticketModel;
    private User $userModel;
    private Message $messageModel;
    private TicketNotificationsService $notificationsService;

    public function __construct()
    {
        $this->ticketModel  = new Ticket();
        $this->userModel    = new User();
        $this->messageModel = new Message();
        $this->notificationsService = new TicketNotificationsService();
    }

    /**
     * Service validation execution.
     * 
     * @param array $data Sanitized data from POST request.
     * @return array An associative array with 'success' (bool) and 'message' (string) keys on failure or 'data' (array) key on success.
     * @throws RuntimeException If the database query fails
     * @see Ticket::fetchTicketById()
     * @see User::getUserById()
     * @see Message::getAllWhere()
     * @see TicketCloseReopenController::validateRequest()
     */
    public function validate(array $data): array
    {
        $ticket = $this->ticketModel->fetchTicketById($data["ticket_id"]);
        $data["title"]      = $ticket['title'];
        $data["creator_id"] = $ticket['created_by'];

        // Checks if ticket exists.
        if ($ticket === false) {
            return ["success" => false, "message" => "Ticket not found."];
        }

        // Fetches the handler's details
        $user = $this->userModel->getUserById($data["user_id"]);

        // Checks if user exists.
        if ($user === null) {
            return ["success" => false, "message" => "User not found."];
        }

        // Checks if the user is either the ticket's creator or handler.
        $userRole = $this->getUserTicketRole($ticket, $data["user_id"]);
        if ($userRole === false) {
            return ["success" => false, "message" => "You are neither the ticket's creator nor its handler."];
        }

        if ($data["action"] === "close") {
            // Validates that the closing type has allowed value.
            if (in_array($data["closingSelect"], $this->ticketModel->closingTypes) === false) {
                return ["success" => false, "message" => "Invalid closing type."];
            }

            // Validates that the ticket has " in progress" status.
            if ($ticket["statusId"] !== 2) {
                return ["success" => false, "message" => "Only tickets with 'in progress' status can be closed!"];
            }

            $messages = $this->messageModel->getAllWhere("messages", "ticket = {$ticket['id']}");

            // Checks if ticket has at least one message.
            if (count($messages) < 1) {
                return ["success" => false, "message" => "Ticket must have at least one message to be able to close."];
            }

            // Check if ticket has at least one message from the admin handler
            $hasHandlerMessage = false;
            foreach ($messages as $msg) {
                if ($msg['user'] === $ticket['handled_by']) {
                    $hasHandlerMessage = true;
                    break;
                }
            }
            if ($hasHandlerMessage === false) {
                return ["success" => false, "message" => "Ticket must have at least one message from the admin handler to be closed."];
            }
        }

        if ($data["action"] === "reopen") {
            // Checks if the user is the handler of the ticket.
            if ($userRole !== "handler") {
                return ["success" => false, "message" => "Only the ticket handler can reopen this ticket."];
            }

            // Validates that a ticket is eligible for reopening, by checking closing type.
            if (in_array($ticket["closing_type"], ["normal", "abandoned", "canceled", "invalid"]) === false) {
                $failureMessage = "Ticket with " . strtoupper($ticket["closing_type"]) . " closing type can't be reopened!";
                return ["success" => false, "message" => $failureMessage];
            }
        }

        return ["success" => true, "data" => $data];
    }

    /** 
     * Check if the user is the creator or the handler of the ticket.
     * 
     * @param int $userId The user ID from the session.
     * @return string|false Returns "creator" if user is the creator, "handler" if user is the handler, otherwise false.
     */
    private function getUserTicketRole(array $ticket, int $userId): string|false
    {
        if ($ticket["handled_by"] === $userId) {
            return "handler";
        } elseif ($ticket["created_by"] === $userId) {
            return "creator";
        } else {
            return false;
        }
    }


    /**
     * Close or Reopen the ticket, depending of $action value.
     * Sends notification email to the ticket creator.
     * 
     * @param array $data The validated data array containing: ticket_id, creator_id and action (close|reopen).
     * @return void
     * @throws RuntimeException If the database query fails
     * @see Ticket::closeReopenTicket()
     */
    public function closeReopenTicket(array $data): void
    {
        // Fetch the ticket creator details
        $creator = $this->userModel->getUserById($data["creator_id"]);
        $this->ticketModel->closeReopenTicket($data["ticket_id"], $data["action"]);

        // Send notification email to the ticket creator
        // string $email, string $name, string $surname, string $title, string $description, int $ticketId, string $action
        $this->notificationsService->closeReopenNotification($creator["email"], $creator["name"], $creator["surname"], $data["title"], $data["ticket_id"], $data["action"]);
    }
}
