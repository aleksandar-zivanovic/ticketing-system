<?php
require_once '../../classes/Ticket.php';
require_once 'BaseService.php';

class TicketCreateService extends BaseService
{
    private Ticket $ticket;

    public function __construct()
    {
        $this->ticket = new Ticket();
    }

    /**
     * Service validation.
     * 
     * @return array Associative array with 'success' (bool) and 'message' (string).
     */
    public function validate(array $data): array
    {
        // Validates minimal title length
        if ($this->validateTextLength($data["title"], 5) === false) {
            return ["success" => false, "message" => "Title must be at least 5 characters long."];
        }

        // Validates minimal description length
        if ($this->validateTextLength($data["description"], 15) === false) {
            return ["success" => false, "message" => "Description must be at least 15 characters long."];
        }

        // Validate departments
        if ($this->validateDepartments($data['departmentId']) === false) {
            return ["success" => false, "message" => "Selected department is not valid."];
        }

        // Validate priorities
        if ($this->validatePriorities($data['priorityId']) === false) {
            return ["success" => false, "message" => "Selected priority is not valid."];
        }

        // Validates if the creator is real and verified
        if ($this->validateUser($data["userId"] === false)) {
            return ["success" => false, "message" => "User is not valid."];
        }

        return ["success" => true];
    }

    /**
     * Checks if a user with the selected ID really exists
     * 
     * @param int $userId ID column from users table.
     * @return bool True if a user exist, othewise false.
     */
    private function validateUser(int $userId): bool
    {
        require_once '../../classes/User.php';
        $user = new User();
        $userDetails = $user->getUserById($userId);

        if ($userDetails === null || $userDetails["verified"] !== 1) {
            return false;
        }

        return true;
    }

    /**
     * Creates a ticket using the Ticket class.
     * 
     * @param array $data Associative array containing ticket data.
     * @param ?callable $onTicketCreated Callback function to receive the new ticket ID.
     * @return void
     * 
     * @throws RuntimeException If the query execution fails.
     * @throws UnexpectedValueException If the table name is invalid.
     * @throws Exception Exception If there is an error in images upload.
     * @see Ticket::createTicket()
     * @see Attachment::processImages()
     */
    public function createTicket(array $data, ?callable $onTicketCreated = null): void
    {
        $this->ticket->createTicket(split: false, ticketAttachments: null, attachment: null, data: $data, onTicketCreated: $onTicketCreated);
    }
}
