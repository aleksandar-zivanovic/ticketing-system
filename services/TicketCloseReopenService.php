<?php
require_once '../classes/Ticket.php';

class TicketCloseReopenService
{
    private Ticket $ticket;
    public array $theTicket;

    public function __construct(int $ticketId)
    {
        $this->ticket = new Ticket();
        $this->theTicket = $this->ticket->fetchTicketDetails($ticketId);
    }

    /** 
     * Check if the ticket is in "in progress" status.
     * @return bool True if the ticket is in progress, false otherwise.
     * @throws Exception If the ticket is not in progress.
     */
    public function isTheTicketInProgress(): bool
    {
        if ($this->theTicket["statusId"] !== 2) {
            throw new Exception("Only tickets with `in progress` status can be closed!");
        }
        return true;
    }

    /** 
     * Check if the user is neither the creator nor the handler of the ticket.
     * @param int $userIdFromSession The user ID from the session.
     * @return bool True if the user is neither the creator nor the handler, false otherwise.
     */
    public function notCreatorOrHandler(int $userIdFromSession): bool
    {
        if ($this->theTicket["handled_by"] != $userIdFromSession && $this->theTicket["created_by"] != $userIdFromSession) {
            return true;
        }

        return false;
    }

    /**
     * Close or Reopen the ticket, depending of $action value.
     * Delegates the action to Ticket::closeReopenTicket(), which handles
     * validation and throws exceptions on failure.
     * @param int $ticketIdFromForm The ticket ID from the form
     * @param string $action The action to perform: "close" or "reopen".
     * @return void Throws exception on failure; no return value on success.
     * @throws InvalidArgumentException If $actions hasn't value "close" or "reopen"
     * @throws DomainException If the action is invalid.
     * @throws Exception For other errors thrown by Ticket::closeReopenTicket().
     */
    public function closeReopenTicket(int $ticketIdFromForm, string $action): void
    {
        if ($action !== "close" && $action !== "reopen") {
            logError("closeReopenTicket() method in TicketCloseReopenService.php error: The action parameter is invalid! $action is invalid value!");
            throw new InvalidArgumentException("closeReopenTicket() - Unallowed action!");
        }

        $this->ticket->closeReopenTicket($ticketIdFromForm, $action);
    }
}
