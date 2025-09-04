<?php
require_once '../../classes/Ticket.php';

class TicketSplitService
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
        // Validates if a ticket with the provided ID exists.
        if ($this->ticketData === false) {
            return ["success" => false, "message" => "Ticket doesn't exist."];
        }

        // Checks if the ticket is created during splitting process.
        if ($this->ticket->isCreatedBySplitting($this->ticketData)) {
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
            if (empty($title) || !$this->validateText($title, 5)) {
                return ["success" => false, "message" => "Each title must be at least 5 characters long."];
            }
        }

        // Validate descriptions
        foreach ($values['error_description'] as $description) {
            if (empty($description) || !$this->validateText($description, 15)) {
                return ["success" => false, "message" => "Description must be at least 15 characters long."];;
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
     * Validates text length.
     * Returns true if text length is equal or greater than specified length, otherwise false.
     * @param string $text Text to validate.
     * @param string $length Minimum length required.
     * @return bool True if valid, false otherwise.
     */
    public function validateText(string $text, string $length): bool
    {
        if (strlen($text) < $length) {
            return false;
        }
        return true;
    }

    /**
     * Checks if a department ID exists among existing departments.
     * @param int $departmentId ID to validate.
     * @return bool Valid ID or false if invalid.
     */
    public function validateDepartments(int $departmentId): bool
    {
        require_once '../../classes/Department.php';
        $department  = new Department();
        $departments = $department->getAllDepartmentIds();
        return in_array($departmentId, $departments);
    }

    /**
     * Checks if a priority ID exists among existing priorities.
     * @param int $priorityId ID to validate.
     * @return bool Valid ID or false if invalid.
     */
    public function validatePriorities(int $priorityId): bool
    {
        require_once '../../classes/Priority.php';
        $priority   = new Priority();
        $priorities = $priority->getAllPriorotyIds();
        return in_array($priorityId, $priorities);
    }

    /**
     * Proxy method to split a ticket using the Ticket model.
     *
     * @param array $values Formatted as expected by Ticket::splitTicket().
     * @return void
     * @see Ticket::splitTicket()
     */
    public function splitTicket(array $values): void
    {
        $this->ticket->splitTicket($values);
    }
}
