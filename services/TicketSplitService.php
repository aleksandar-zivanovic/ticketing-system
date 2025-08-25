<?php
require_once ROOT . 'classes' . DS . 'Ticket.php';
require_once ROOT . 'services' . DS . 'TicketService.php';
require_once ROOT . 'services' . DS . 'BaseService.php';

class TicketSplitService extends BaseService
{
    private Ticket $ticket;
    private array|false $ticketData = false;

    public function __construct()
    {
        $this->ticket = new Ticket();
    }

    /**
     * Validates ticket and form data for splitting a ticket.
     * 
     * @param int $ticketId ID of the ticket to validate.
     * @param array $values Associative array containing form data.
     * @return array
     * @throws RuntimeException If validation fails.
     * 
     * @see TicketSplitService::fetchTicket()
     * @see TicketSplitService::validateTicketData()
     * @see TicketSplitService::validateFormData()
     */
    public function validateData(int $ticketId, array $values): array
    {
        $this->fetchTicket($ticketId);

        $validateTicketData = $this->validateTicketData();
        if ($validateTicketData["success"] === false) {
            return $validateTicketData;
        }

        $validateFormData = $this->validateFormData($values);
        if ($validateFormData["success"] === false) {
            return $validateFormData;
        }

        return ["success" => true];
    }

    /**
     * Fetches ticket data by ID and stores it in the ticketData property.
     * 
     * @param int $ticketId ID of the ticket to fetch.
     * @return void
     */
    public function fetchTicket(int $ticketId): void
    {
        $this->ticketData = $this->ticket->fetchTicketById($ticketId);
    }

    /**
     * Validates the fetched ticket data.
     * 
     * @return array Associative array with 'success' (bool) and 'message' (string).
     */
    public function validateTicketData(): array
    {
        // Checks if the ticket is created during splitting process.
        if ($this->ticketData["parent_ticket"] !== null) {
            return ["success" => false, "message" => "Splitting of a ticket created through splitting process is forbidden."];
        }

        // Validate the user is an admin
        if ($_SESSION["user_role"] !== "admin") {
            return ["success" => false, "message" => "You don't have permission to split the ticket."];
        }

        return ["success" => true];
    }

    /**
     * Validates form data for creating or splitting a ticket.
     * 
     * @param array $values Associative array containing form data.
     * @return array Associative array with 'success' (bool) and 'message' (string).
     */
    public function validateFormData(array $values): array
    {
        $counts['title']       = count($values['error_title']);
        $counts['description'] = count($values['error_description']);
        $counts['department']  = count($values['error_department']);
        $counts['priority']    = count($values['error_priority']);

        // Validate that at least 2 tickets will be created
        if (
            $counts['title']  < 2 || $counts['description'] < 2 || $counts['department'] < 2 || $counts['priority'] < 2
        ) {
            return ["success" => false, "message" => "There must be at least 2 tickets with all necessary data."];
        }

        // Validate that all fields are filled.
        if (count(array_unique([$counts['title'], $counts['description'], $counts['department'], $counts['priority']])) > 1) {
            return ["success" => false, "message" => "All ticket forms must be filled in completely. Every ticket must have title, description, department and priority."];
        }

        // Validate titles
        foreach ($values['error_title'] as $title) {
            if (empty($title) || $this->validateTextLength($title, 5, null) === false) {
                return ["success" => false, "message" => "Each title must be at least 5 characters long."];
            }
        }

        // Validate descriptions
        foreach ($values['error_description'] as $description) {
            if (empty($description) || $this->validateTextLength($description, 15, null) === false) {
                return ["success" => false, "message" => "Description must be at least 15 characters long."];
            }
        }

        // Validate departments
        foreach ($values['error_department'] as $department) {
            if ($this->validateDepartments($department) === false) {
                return ["success" => false, "message" => "Selected department is not valid."];
            }
        }

        // Validate priorities
        foreach ($values['error_priority'] as $priorities) {
            if ($this->validatePriorities($priorities) === false) {
                return ["success" => false, "message" => "Selected priority is not valid."];
            }
        }

        // Validate domain
        if (!str_contains($values["error_page"], APP_DOMAIN)) {
            return ["success" => false, "message" => "Invalid domain"];
        }

        // Validate creator
        if ($values["error_user_id"] !== $this->ticketData["created_by"]) {
            return ["success" => false, "message" => "Selected wrong creator."];
        }

        // Validate ticket status
        if ($this->ticketData["statusId"] !== 1) { // 1 = waitting
            return ["success" => false, "message" => "Splitting of a ticket different than waitting status is frobidden."];
        }

        return ["success" => true];
    }

    /**
     * Proxy method to split a ticket using the Ticket model.
     *
     * @param array $splitData Formatted as expected by Ticket::splitTicket().
     * @return void
     * @throws RuntimeException If the query execution fails.
     * @throws UnexpectedValueException If the table name is invalid.
     * @throws Exception Exception If there is an error in images upload.
     * @throws InvalidArgumentException if the number of rows and where values do not match,
     * or if unsupported parameter types are provided.
     * 
     * @see Ticket::splitTicket()
     * @see Ticket::createTicket()
     * @see Ticket::updateTicket()
     */
    public function splitTicket(array $splitData): void
    {
        require_once ROOT . 'classes' . DS . 'Attachment.php';
        $attachment     = new Attachment();
        $attachments    = $attachment->processImagesForSplit();
        $ticketService  = new TicketService();
        $parentId       = $splitData["error_ticket_id"];

        foreach ($attachments as $key => $ticketAttachments) {
            $data['title']        = $splitData["error_title"][$key];
            $data['priorityId']   = $splitData["error_priority"][$key];
            $data['description']  = $splitData["error_description"][$key];
            $data['departmentId'] = $splitData["error_department"][$key];
            $data['userId']       = $splitData["error_user_id"];
            $data['url']          = $splitData["error_page"];
            $data['statusId']     = 1; // 1 = waitting

            $ticketService->createTicket($data, $ticketAttachments, true, $parentId);

            $columns = [
                [
                    "statusId" => 3,
                    "closed_date" => date("Y-m-d H:i:s"),
                    "closing_type" => "split"
                ]
            ];
            $whereClauses = [["id" => $parentId]];

            $this->ticket->updateTicket($columns, $whereClauses);
        }

        unset(
            $_SESSION["error_department"],
            $_SESSION["error_priority"],
            $_SESSION["error_page"],
            $_SESSION["error_title"],
            $_SESSION["error_description"],
            $_SESSION["error_user_id"],
            $_SESSION["error_ticket_id"],
        );
    }
}
