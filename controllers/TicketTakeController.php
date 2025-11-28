<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';
require_once ROOT . 'services' . DS . 'TicketTakeService.php';

class TicketTakeController extends BaseController
{
    private TicketTakeService $service;

    public function __construct()
    {
        $this->service = new TicketTakeService();
    }

    /**
     * Validates the incoming request for taking a ticket.
     *
     * @return array An associative array with 'success' status and either 'data' or 'message'.
     * @see TicketTakeService::validate() for service-level validation.
     */
    public function validateRequest(): array
    {
        // Check request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Invalid request method.", "url" => "error"];
        }

        // Check action
        if (!isset($_POST["take_ticket"]) || $_POST["take_ticket"] !== "Take the Ticket") {
            return ["success" => false, "message" => "Invalid action.", "url" => "error"];
        }

        // Validate the presence of the ticket ID
        if (!$this->hasValue($_POST["ticket_id"])) {
            return ["success" => false, "message" => "Ticket ID is required.", "url" => "error"];
        }

        // Sanitize and validate the ticket ID
        $data["ticket_id"] = $this->validateId($_POST["ticket_id"]);
        if ($data["ticket_id"] === false) {
            return ["success" => false, "message" => "Invalid ticket ID.", "url" => "error"];
        }

        $this->redirectUrl = BASE_URL . "admin/view-ticket.php?ticket=" . $data["ticket_id"];

        // Validate and sanitize the current user ID
        $data["admin_id"] = $this->validateId(cleanString($_SESSION["user_id"]));
        if ($data["admin_id"] === false) {
            return ["success" => false, "message" => "Invalid user."];
        }

        $data["creator_id"] = $this->validateId($_POST["creator_id"]);
        if ($data["creator_id"] === false) {
            return ["success" => false, "message" => "Invalid ticket creator."];
        }

        // Service-level validation
        return $this->service->validate($data);
    }

    public function takeTicket(): void
    {
        $validation = $this->validateRequest();

        $this->handleValidation($validation);

        try {
            $this->service->takeTicket($validation["data"]["ticket_id"], $validation["data"]["admin_id"], $validation["data"]["creator_id"], $validation["data"]["title"]);
            redirectAndDie(
                $this->redirectUrl,
                "The ticket ID:{$validation["data"]["ticket_id"]} is assigned to you!",
                "success"
            );
        } catch (\Throwable $th) {
            redirectAndDie($this->redirectUrl, "An error occurred while taking the ticket.");
        }
    }
}
