<?php
require_once '../../classes/Ticket.php';

class TicketTakeService
{
    private Ticket $ticketModel;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
    }

    /**
     * Validates if a ticket with the given ID exists.
     * 
     * @param int $ticketID The ID of the ticket to validate.
     * @return bool True if the ticket exists, false otherwise.
     * @throws Exception If a database error occurs.
     * @see Ticket::getAllWhere() for fetching ticket details.
     */
    public function validate(int $ticketID): bool
    {
        $theTicket = $this->ticketModel->getAllWhere("tickets", "id = {$ticketID}");
        if (empty($theTicket)) {
            return false;
        }

        return true;
    }

    public function takeTicket(int $ticketID, int $adminID): bool
    {
        return $this->ticketModel->takeTicket($ticketID, $adminID);
    }
}
