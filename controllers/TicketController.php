<?php
require_once 'BaseController.php';
require_once '../../services/TicketService.php';

class TicketController extends BaseController
{
    private TicketService $service;
    private array $values;

    public function __construct()
    {
        $this->service = new TicketService();
    }

    /**
     * Validates $_POST request data for creating a ticket.
     *
     * Checks required fields (URL, title, description, department, priority, user)
     * and performs sanitization. Also runs additional validation via service.
     *
     * @param array $_POST Data from the form submission
     * @return bool True if validation passes, false otherwise
     */
    public function validateCreateRequest(array $data): bool
    {
        // Validates the URL from the form input.
        if ($this->hasValue($data["error_page"]) === false) {
            $_SESSION["fail"] = "Error page URL is missing.";
            return false;
        }

        $this->values["url"] = $this->validateUrl($data["error_page"]);
        if ($this->values["url"] === false) {
            $_SESSION["fail"] = "Invalid error page URL.";
            return false;
        }

        // Validates and sanitizes title
        if ($this->hasValue($data["error_title"]) === false) {
            $_SESSION["fail"] = "Title is not set.";
            return false;
        }
        $this->values["title"] = cleanString($data["error_title"]);

        // Validates and sanitizes description
        if ($this->hasValue($data["error_description"]) === false) {
            $_SESSION["fail"] = "Description is not set.";
            return false;
        }
        $this->values["description"] = cleanString($data["error_description"]);

        // Validates and sanitizes department ID
        $this->values["departmentId"] = $data["error_department"];
        if ($this->validateId($this->values["departmentId"]) === false) {
            $_SESSION["fail"] = "Department is not set.";
            return false;
        }

        // Validates and sanitizes priority ID
        $this->values["priorityId"] = $data["error_priority"];
        if ($this->validateId($this->values["priorityId"]) === false) {
            $_SESSION["fail"] = "Priority is not set.";
            return false;
        }

        // Validates and sanatizes creator ID
        $this->values["userId"] = $this->validateId($_SESSION["user_id"]);

        // Validation from servise
        $serviceValidation = $this->service->validateCreate($this->values);
        if ($serviceValidation["success"] === false) {
            $_SESSION["fail"] = $serviceValidation["message"];
            return false;
        }

        return true;
    }

    public function validateDeleteRequest(int $ticketId): array
    {
        return $this->service->validateDelete($ticketId);
    }

    /**
     * Creates a ticket using the Ticket class.
     * 
     * @param ?callable $onTicketCreated Callback function to receive the new ticket ID.
     * @return void
     * 
     * @throws RuntimeException If the query execution fails.
     * @throws UnexpectedValueException If the table name is invalid.
     * @throws Exception Exception If there is an error in images upload.
     * @see Ticket::createTicket()
     * @see Attachment::processImages()
     */
    public function createTicket(?callable $onTicketCreated = null)
    {
        $this->service->createTicket(data: $this->values, onTicketCreated: $onTicketCreated);
    }

    /**
     * Deletes tickets and its attachments from database and server
     * 
     * @param int $id ID of a ticket for deletion
     * @return void
     * @throws RuntimeException If deletion of files from server or `attachments` table fails
     * @throws Exception If ticket ID is invalid or ticket deletion fails.
     */
    public function deleteTicket(int $id): void
    {
        $this->service->deleteTicket($id);
    }
}
