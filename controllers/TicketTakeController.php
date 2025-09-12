<?php
require_once '../../controllers/BaseController.php';
require_once '../../services/TicketTakeService.php';

class TicketTakeController extends BaseController
{
    private int|false $sanitizedTicketID;
    private int|false $sanitizedUserID;
    private TicketTakeService $service;

    public function __construct()
    {
        $this->service = new TicketTakeService();
    }

    public function validateRequest(int|string $ticketID, $currentUser): array
    {
        // Sanitize and validate the ticket ID
        $ticketID = filter_var($ticketID, FILTER_SANITIZE_NUMBER_INT);
        $this->sanitizedTicketID = filter_var($ticketID, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($this->sanitizedTicketID === false) {
            return ["success" => false, "message" => "Invalid ticket ID."];
        }

        // Validate the current user ID
        $this->sanitizedUserID = $this->validateId($currentUser);
        if ($this->sanitizedUserID === false) {
            return ["success" => false, "message" => "Invalid user."];
        }

        // Check if the ticket exists
        if (!$this->service->validate($this->sanitizedTicketID)) {
            return ["success" => false, "message" => "Invalid ticket."];
        }

        return ["success" => true, "take_ticket_id" => $this->sanitizedTicketID];
    }

    public function takeTicket(): int|false
    {
        $ticketService = new TicketTakeService();
        return $ticketService->takeTicket($this->sanitizedTicketID, $this->sanitizedUserID);
    }
}
