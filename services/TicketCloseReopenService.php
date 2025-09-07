<?php
require_once '../../classes/Ticket.php';

class TicketCloseReopenService
{
    private Ticket $ticket;
    public array $theTicket;

    public function __construct(int $ticketId, private string $action)
    {
        $this->ticket = new Ticket();
        $this->theTicket = $this->ticket->fetchTicketDetails($ticketId);
    }

    /**
     * Service validation execution.
     * 
     * @return array{success: bool, message?: string, ticketIdFromForm?: int} 
     *         success=false + message on fail, success=true + ticketIdFromForm on success
     */
    public function validate(int $userIdFromSession): array
    {
        if ($this->action === "close") {
            // Validates that a ticket has "in progress" status.
            if ($this->isTheTicketInProgress() === false) {
                return ["success" => false, "message" => "Only tickets with 'in progress' status can be closed!"];
            }
        }

        if ($this->isCreatorOrHandler($userIdFromSession) === false) {
            return ["success" => false, "message" => "You don't have premission for this action!"];
        }

        return ["success" => true, "ticketIdFromForm" => $this->theTicket["id"]];
    }

    /** 
     * Check if the ticket is in "in progress" status.
     * 
     * @return bool True if status is "in progress", otherwise false.
     */
    private function isTheTicketInProgress(): bool
    {
        return $this->theTicket["statusId"] === 2;
    }

    /**
     * Validates closing type value.
     * 
     * @param string $closingType Closing type reason.
     * @return bool Returns true if matches, otherwise false.
     */

    public function isClosingTypeValid(string $closingType): bool
    {
        return in_array($closingType, $this->ticket->closingTypes);
    }

    /**
     * Validates if a ticket is eligible for reopening, by checking closing type.
     * 
     * @return bool Returns true if a ticket is eligible for reopening.
     */
    public function isReopenable(): bool
    {
        if ($this->theTicket["closing_type"] === NULL) {
            return false;
        }

        return in_array(
            $this->ticket->closingTypes[$this->theTicket["closing_type"]],
            ["normal", "abandoned", "canceled", "invalid"]
        );
    }

    public function getTicket(): array
    {
        return $this->theTicket;
    }

    /** 
     * Check if the user is the creator or the handler of the ticket.
     * 
     * @param int $userIdFromSession The user ID from the session.
     * @return bool True if a user is the creator or the handler of the ticket, otherwise false.
     */
    private function isCreatorOrHandler(int $userIdFromSession): bool
    {
        return $this->theTicket["handled_by"] === $userIdFromSession || $this->theTicket["created_by"] === $userIdFromSession;
    }

    /** 
     * Check if current user handling the ticket.
     * 
     * @param int $userIdFromSession The user ID from the session.
     * @return bool True if a user is the handler of the ticket, otherwise false.
     */
    public function isHandler(int $userIdFromSession): bool
    {
        return $this->theTicket["handled_by"] === $userIdFromSession;
    }

    /**
     * Close or Reopen the ticket, depending of $action value.
     * Delegates the action to Ticket::closeReopenTicket(), which handles
     * validation and throws exceptions on failure.
     * 
     * @param int $ticketIdFromForm The ticket ID from the form
     * @param string $action The action to perform: "close" or "reopen".
     * @return bool Returns true if the process was successful.
     * @throws DomainException If the action is invalid.
     * @throws Exception For other errors thrown by Ticket::closeReopenTicket().
     * @see Ticket::closeReopenTicket()
     */
    public function closeReopenTicket(int $ticketIdFromForm, string $action): bool
    {
        return $this->ticket->closeReopenTicket($ticketIdFromForm, $action);
    }
}
