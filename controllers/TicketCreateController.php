<?php
require_once '../../services/TicketCreateService.php';
require_once 'BaseController.php';

class TicketCreateController extends BaseController
{
    private TicketCreateService $ticketCreateService;
    private array $values;

    public function __construct()
    {
        $this->ticketCreateService = new TicketCreateService();
    }

    public function validateRequest(array $data): bool
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
        $serviceValidation = $this->ticketCreateService->validate($this->values);
        if ($serviceValidation["success"] === false) {
            $_SESSION["fail"] = $serviceValidation["message"];
            return false;
        }

        return true;
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
        $this->ticketCreateService->createTicket(data: $this->values, onTicketCreated: $onTicketCreated);
    }
}
