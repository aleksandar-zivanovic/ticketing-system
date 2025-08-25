<?php
require_once ROOT . 'classes' . DS . 'BaseModel.php';

class SplitTicketNotification extends BaseModel
{

    /**
     * Generates system messaage for the split (parent) ticket.
     * 
     * @param array $theTicket The array got from Tickets()->fetchTicketDetails() method.
     * 
     * @return void
     */
    public function generateParentTicketMessage(array $theTicket): void
    {
        // Ako je tiket zatvoren i ako je status split, onda izbaci ovu poruku
        $childrenTickets = $this->getAllWhere("tickets", "parent_ticket = {$theTicket['id']}");
        $parentTicket    = $this->getAllWhere("tickets", "id = {$theTicket['id']}");
        $adminFirstName  = cleanString($_SESSION["user_name"]);
        $adminSecondName = cleanString($_SESSION["user_surname"]);
        $date            = $parentTicket[0]["closed_date"];
        $parent          = true;
        require_once ROOT . 'views' . DS . 'partials' . DS . '_split_message.php';
    }

    public function generateChildTicketMessage(int $parentId): void
    {
        $parentTicket    = $this->getAllWhere("tickets", "id = $parentId");
        $child           = true;
        require_once ROOT . 'views' . DS . 'partials' . DS . '_split_message.php';
    }
}
