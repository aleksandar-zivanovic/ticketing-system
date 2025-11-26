<?php
require_once ROOT . 'services' . DS . 'TicketCloseReopenService.php';
require_once ROOT . 'controllers' . DS . 'BaseController.php';

class TicketCloseReopenController extends BaseController
{
    private TicketCloseReopenService $service;

    public function __construct()
    {
        $this->service = new TicketCloseReopenService();
    }

    /**
     * Validates and sanatizes data from POST request.
     * 
     * @return array An associative array with 'success' (bool) and 'message' (string) keys on failure or 'data' (array) key on success.
     * @throws RuntimeException If the database query fails
     * @see TicketCloseReopenService::validate()
     */
    public function validateRequest(): array
    {
        // Validates that the request method is POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Invalid request method.", "url" => "error"];
        }

        // Validates that either "close_ticket" or "reopen_ticket" is set in POST data.
        if (!isset($_POST["close_ticket"]) && !isset($_POST["reopen_ticket"])) {
            return ["success" => false, "message" => "No action specified.", "url" => "error"];
        }

        // Determines the action based on which button was pressed.
        $data["action"] = null;

        if (isset($_POST["close_ticket"]) && trim($_POST["close_ticket"]) === "Close Ticket") {
            $data["action"] = "close";
        }

        if (isset($_POST["reopen_ticket"]) && trim($_POST["reopen_ticket"]) === "Reopen Ticket") {
            $data["action"] = "reopen";
        }

        if ($data["action"] === null) {
            return ["success" => false, "message" => "Invalid action.", "url" => "error"];
        }

        // Validates and sanitizes the ticket ID.
        if (!isset($_POST["ticket_id"]) || empty($_POST["ticket_id"])) {
            return ["success" => false, "message" => "Missing ticket ID.", "url" => "error"];
        }

        $data["ticket_id"] = $this->validateId($_POST["ticket_id"]);
        if ($data["ticket_id"] === false) {
            return ["success" => false, "message" => "Invalid ticket ID.", "url" => "error"];
        }

        $this->redirectUrl = "/ticketing-system/admin/view-ticket.php?ticket=" . $data["ticket_id"];

        $data["user_id"] = $this->validateId($_SESSION["user_id"]);
        if ($data["user_id"] === false) {
            return ["success" => false, "message" => "Invalid user ID from session.", "url" => "error"];
        }

        if ($data["action"] === "close") {
            if (!isset($_POST["closingSelect"]) || empty(trim($_POST["closingSelect"]))) {
                return ["success" => false, "message" => "Missing closing type value!"];
            }
            $data["closingSelect"] = cleanString($_POST["closingSelect"]);
        }

        return $this->service->validate($data);
    }

    /**
     * Executes the ticket close or reopen action after validation.
     * 
     * @return void
     * @throws RuntimeException If the database query fails
     * @see TicketCloseReopenService::closeReopenTicket()
     */
    public function closeReopenTicket(): void
    {
        // Validates the request and handles any validation errors.
        $validated = $this->validateRequest();
        $this->handleValidation($validated);

        // Executes the action.
        $successfulAction = $validated["data"]["action"] === "close" ? "closed" : "reopened";
        try {
            $this->service->closeReopenTicket($validated["data"]["ticket_id"], $validated["data"]["action"]);
            redirectAndDie($this->redirectUrl, "Ticket successfully {$successfulAction}.", "success");
        } catch (\Throwable $th) {
            redirectAndDie($this->redirectUrl, "Failed to {$validated['data']['action']} the ticket.");
        }
    }
}
