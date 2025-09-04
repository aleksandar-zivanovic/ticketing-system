<?php
require_once '../../services/TicketSplitService.php';
require_once 'BaseController.php';

class TicketSplitController extends BaseController
{
    private TicketSplitService $ticketSplitService;
    public int $ticketId;
    public array $validatedData;

    public function __construct()
    {
        $this->ticketSplitService = new TicketSplitService();
    }

    /**
     * Validates and sets the errorTicketId property from POST data.
     * 
     * @param int|string $id The ticket ID to validate and set.
     * @return bool True if valid, false otherwise.
     * @see BaseController::validateId()
     * 
     */
    public function validateSomeId(int|string $id): bool
    {
        $splitTicketId = $this->validateId($id);

        if ($splitTicketId === false) {
            return false;
        }

        $this->ticketId = $splitTicketId;
        return true;
    }

    public function validatePostData(array $data): bool
    {
        // Validate and set errorTicketId
        if ($this->validateSomeId($data["error_ticket_id"]) === false) {
            $_SESSION["fail"] = "Invalid ticket ID.";
            return false;
        }
        $values["error_ticket_id"] = $data["error_ticket_id"];

        // Validate form data
        // Gets titles from the form
        $values["error_title"] = [];
        foreach ($data["error_title"] as $key => $title) {
            $values["error_title"][$key] = cleanString($title);
            if (empty($values["error_title"][$key])) {
                $_SESSION["fail"] = "You didn't set a title.";
                return false;
            }
        }

        // Gets descriptions from the form
        $values["error_description"] = [];
        foreach ($data["error_description"] as $key => $desc) {
            $values["error_description"][$key] = cleanString($desc);
            if (empty($values["error_description"][$key])) {
                $_SESSION["fail"] = "You didn't set a description.";
                return false;
            }
        }

        // Gets url from the form

        // Only $data["error_page"][0] has a string value; [1], [2], etc. are empty.
        if (!isset($data["error_page"][0]) || empty($data["error_page"][0])) {
            $_SESSION["fail"] = "Url is not set";
            logError("\$_POST[\"error_page\"][0] is not set or empty in processCreateSplitTicket.php");
            return false;
        }

        $values["error_page"] = filter_var($data["error_page"][0], FILTER_SANITIZE_URL);
        $values["error_page"] = filter_var($values["error_page"], FILTER_VALIDATE_URL);
        if ($values["error_page"] === false) {
            $_SESSION["fail"] = "URL is not valid.";
            logError("\$_POST[\"error_page\"][0] is not valid in processCreateSplitTicket.php");
            return false;
        }

        // Gets departments from the form
        $values["error_department"] = [];
        foreach ($data["error_department"] as $departmentId) {
            if ($this->validateId($departmentId) === false) {
                $_SESSION["fail"] = "Department is not set.";
                return false;
            }
            $values["error_department"][] = $departmentId;
        }

        // Gets priorities from the form
        $values["error_priority"] = [];
        foreach ($data["error_priority"] as $priorityId) {
            if ($this->validateId($priorityId) === false) {
                $_SESSION["fail"] = "Priority is not set.";
                return false;
            }
            $values["error_priority"][] = $priorityId;
        }

        // Gets creators ID from the form
        $values["error_user_id"] = $this->validateId($data["error_user_id"]);
        if ($values["error_user_id"] === false) {
            $_SESSION["fail"] = "Creator ID is not valid.";
            return false;
        }

        // Service validation
        $serviceValidation = $this->ticketSplitService->validateData($this->ticketId, $values);
        if ($serviceValidation["success"] === false) {
            $_SESSION["fail"] = $serviceValidation["message"];
            return false;
        }
        
        $this->validatedData = $values;
        return true;
    }

    /**
     * Proxy method to split a ticket using the TicketSplitService.
     * @return void
     * @see Ticket::splitTicket()
     */
    public function splitTicket(): void
    {
        $this->ticketSplitService->splitTicket($this->validatedData);
    }
}
