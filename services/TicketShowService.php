<?php
require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'classes' . DS . 'Ticket.php';
require_once ROOT . 'classes' . DS . 'Message.php';

class TicketShowService extends BaseService
{
    private Ticket $ticketModel;
    private Message $messageModel;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
        $this->messageModel = new Message();
    }

    public function validate(array $data): array
    {
        // Uses for show ticket and split ticket
        if (!isset($data["create"])) {
            $ticket = $this->fetchTicketDetails($data["id"]);
            if (empty($ticket["id"])) {
                return ["success" => false, "message" => "Ticket not found."];
            }

            $data["theTicket"]    = $ticket;
            $data["closingTypes"] = $this->ticketModel->closingTypes;
            $data["hasChildren"]  = $this->ticketModel->hasChildren($ticket["id"]);
            $data["allMessages"]  = $this->messageModel->allMessagesByTicket($ticket["id"]);
        }


        // If the ticket should be created or split, fetch departments and priorities
        if (
            (isset($data["split"]) && $data["split"] === true) ||
            (isset($data["create"]) && $data["create"] === true)
        ) {
            $data = array_merge($data, $this->getAllDepartmentsAndPriorities($this->ticketModel));
        }

        return ["success" => true, "data" => $data];
    }

    /**
     * Fetch ticket details by ID.
     * @param int $ticketID
     * 
     * @return array|false Ticket details array or false if not found.
     * @throws RuntimeException If there is a PDOException while executing the SQL query.
     */
    private function fetchTicketDetails(int $ticketID): array|false
    {
        return $this->ticketModel->fetchTicketDetails($ticketID);
    }
}
