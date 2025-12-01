<?php
require_once ROOT . 'services' . DS . 'TicketSplitService.php';
require_once ROOT . 'services' . DS . 'TicketShowService.php';
require_once ROOT . 'controllers' . DS . 'BaseController.php';

class TicketSplitController extends BaseController
{
    private TicketSplitService $ticketSplitService;
    private TicketShowService $ticketShowService;

    public function __construct()
    {
        $this->ticketSplitService = new TicketSplitService();
        $this->ticketShowService = new TicketShowService();
    }

    /**
     * Validates $_POST request data for splitting a ticket.
     *
     * Checks required fields and performs sanitization. Also runs additional validation via service.
     * @return array Validation result with 'success' key and either 'data' or 'message' key.
     * @see TicketSplitService::validateData()
     */
    public function validateSplitRequest(): array
    {
        // Validates request method and user action
        if (
            $_SERVER['REQUEST_METHOD'] !== "POST" ||
            !isset($_POST['user_action']) ||
            $_POST['user_action'] !== "Split Ticket"
        ) {
            return ["success" => false, "message" => "Invalid request method or user action.", "url" => "error"];
        }

        // Validate and set errorTicketId
        $values["error_ticket_id"] = $this->validateId($_POST["error_ticket_id"]);
        if ($values["error_ticket_id"] === false) {
            return ["success" => false, "message" => "Ticket ID is invalid.", "url" => "error"];
        }

        // Sets redirection url for error handling
        $this->redirectUrl = BASE_URL . "admin/split-ticket.php?ticket=" . $values["error_ticket_id"];

        // Validate form data
        // Gets titles from the form
        $values["error_title"] = [];
        foreach ($_POST["error_title"] as $key => $title) {
            $values["error_title"][$key] = cleanString($title);
            if (empty($values["error_title"][$key])) {
                return ["success" => false, "message" => "You didn't set a title."];
            }
        }

        // Gets descriptions from the form
        $values["error_description"] = [];
        foreach ($_POST["error_description"] as $key => $desc) {
            $values["error_description"][$key] = cleanString($desc);
            if (empty($values["error_description"][$key])) {
                return ["success" => false, "message" => "You didn't set a description."];
            }
        }

        // Gets departments from the form. 
        $values["error_department"] = [];
        foreach ($_POST["error_department"] as $departmentId) {
            $sanatizedPriorityId = $this->validateId($departmentId);
            if ($sanatizedPriorityId === false) {
                return ["success" => false, "message" => "Department is not set or is invalid."];
            }
            $values["error_department"][] = $sanatizedPriorityId;
        }

        // Gets priorities from the form
        $values["error_priority"] = [];
        foreach ($_POST["error_priority"] as $priorityId) {
            $sanatizedPriorityId = $this->validateId($priorityId);
            if ($sanatizedPriorityId === false) {
                return ["success" => false, "message" => "Priority is not set or is invalid."];
            }
            $values["error_priority"][] = $sanatizedPriorityId;
        }

        // Service validation layer
        $serviceValidation = $this->ticketSplitService->validateSplitData($values["error_ticket_id"], $values);
        if ($serviceValidation["success"] === false) {
            return $serviceValidation;
        }
        $values += $serviceValidation;

        return ["success" => true, "data" => $values];
    }

    /**
     * Split ticket acition.
     * Splits a ticket into multiple tickets based on the validated data.
     *
     * @return void
     * @throws RuntimeException If the query execution fails.
     * @throws UnexpectedValueException If the table name is invalid.
     * @throws Exception Exception If there is an error in images upload.
     * @throws InvalidArgumentException if the number of rows and where values do not match,
     * or if unsupported parameter types are provided.
     * 
     * @see TicketSplitService::splitTicket()
     */
    public function splitTicket(): void
    {
        // Validates the request data
        $validation = $this->validateSplitRequest();

        $this->handleValidation($validation);

        $validation["data"]["panel"] = "admin";

        // Split ticket
        try {
            $this->ticketSplitService->splitTicket($validation["data"]);
            redirectAndDie(
                BASE_URL . "admin/admin-ticket-listing.php",
                "The ticket is split successfully.",
                "success"
            );
        } catch (\Throwable $th) {
            redirectAndDie(
                $this->redirectUrl,
                "Ticket splitting failed. Please try again."
            );
        }
    }

    /**
     * Validates $_GET request data for showing the split ticket form.
     *
     * Checks required fields and performs sanitization. Also runs additional validation via service.
     * @return array Validation result with 'success' key and either 'data' or 'message' key.
     * @see TicketShowService::validate()
     */
    public function validateShowRequest(): array
    {
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== "GET") {
            return ["success" => false, "message" => "Invalid request method."];
        }

        if (!$this->hasValue($_GET['ticket'])) {
            return ["success" => false, "message" => "Ticket ID is required."];
        }

        $data["id"] = $this->validateId($_GET['ticket']);
        if ($data["id"] === false) {
            return ["success" => false, "message" => "Invalid Ticket ID."];
        }

        $data["split"] = true;

        return $this->ticketShowService->validate($data);
    }

    /**
     * Renders the split ticket form.
     * 
     * @return void
     */
    public function show(): void
    {
        $validation = $this->validateShowRequest();
        $this->handleValidation($validation);
        $validation["data"]["panel"] = "admin";
        $validation["data"]["split"] = true;
        $this->render("ticket.php", $validation["data"]);
    }
}
