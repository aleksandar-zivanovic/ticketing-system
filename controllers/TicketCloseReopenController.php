<?php
require_once '../../services/TicketCloseReopenService.php';
require_once 'BaseController.php';

class TicketCloseReopenController extends BaseController
{
    private TicketCloseReopenService $service;
    private int|string $ticketId;

    public function __construct(private array $data, private string $action)
    {
        $this->ticketId = $this->validateId($this->data["ticket_id"]);

        if ($this->ticketId === false) {
            throw new InvalidArgumentException("Ticket ID is invalid");
        }

        $this->service = new TicketCloseReopenService((int) $this->data["ticket_id"], $action);
    }

    /**
     * Validates and sanatizes data from POST request.
     * 
     * @param int $userIdFromSession User ID from session.
     * @return array{success: bool, message?: string, ticketIdFromForm?: int} 
     *         success=false + message on fail, success=true + ticketIdFromForm on success.
     * @see TicketCloseReopenService::validate()
     */
    public function validateRequest(int $userIdFromSession): array
    {
        if ($this->action === "close") {
            // Sanitizes close type string
            $this->data["closingSelect"] = cleanString($this->data["closingSelect"]);
            if ($this->service->isClosingTypeValid($this->data["closingSelect"]) === false) {
                return ["success" => false, "message" => "Invalid closing type."];
            }
        } else {
            // Checks if the ticket is eligible for reopeoning based on its closing reason.
            if ($this->service->isReopenable() === false) {
                return ["success" => false, "message" => "This ticket can't be reopen."];
            }
        }

        return $this->service->validate($userIdFromSession);
    }

    /** 
     * Check if the user is the creator or the handler of the ticket.
     * 
     * @param int $userIdFromSession The user ID from the session.
     * @return bool True if a user is the creator or the handler of the ticket, otherwise false.
     * @see TicketCloseReopenService::isHandler()
     */
    public function isHandler(int $userIdFromSession): bool
    {
        return $this->service->isHandler($userIdFromSession);
    }

    /**
     * Close or Reopen a ticket.
     * Delegates the action to TicketCloseReopenService::closeReopenTicket()
     * 
     * @param int $ticketIdFromForm The ticket ID from the form
     * @param string $action The action to perform: "close" or "reopen".
     * @return bool Returns true if the process was successful.
     * @throws DomainException If the action is invalid.
     * @throws Exception For other errors thrown by the service.
     * @see TicketCloseReopenService::closeReopenTicket()
     */
    public function closeReopenTicket(int $ticketIdFromForm, string $action): bool
    {
        return $this->service->closeReopenTicket($ticketIdFromForm, $action);
    }
}
