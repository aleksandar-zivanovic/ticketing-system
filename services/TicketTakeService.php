<?php
require_once ROOT . 'classes' . DS . 'Ticket.php';
require_once ROOT . 'services' . DS . 'TicketNotificationsService.php';

class TicketTakeService
{
    private Ticket $ticketModel;
    private User $userModel;
    private TicketNotificationsService $notificationService;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
        $this->userModel   = new User();
        $this->notificationService = new TicketNotificationsService();
    }

    /**
     * Validates the ticket taking request.
     *
     * @param array $data An associative array containing 'ticket_id' and 'admin_id'.
     * @return array An associative array with 'success' status and either 'data' or 'message'.
     * @throws RuntimeException If database request failed.
     * @see User::getUserById() for fetching user details.
     * @see Ticket::getAllWhere() for fetching ticket details.
     */
    public function validate(array $data): array
    {
        // Fetch the admin user details
        $admin = $this->userModel->getUserById($data["admin_id"]);

        // Check if the admin user exists
        if (empty($admin)) {
            return ["success" => false, "message" => "Invalid user."];
        }

        // Check if the admin user is authorized to take tickets. Role ID 3 is assumed to be 'admin'.
        if ($admin["role_id"] !== 3) {
            return ["success" => false, "message" => "User is not authorized."];
        }

        // Fetch the ticket details
        $theTicket = $this->ticketModel->getAllWhere("tickets", "id = {$data["ticket_id"]}")[0];

        // Check if the ticket exists
        if (empty($theTicket)) {
            return ["success" => false, "message" => "Ticket not found."];
        }

        // Check if the ticket is already handled or doesn't have 'waiting' status (statusId = 1)
        if ($theTicket["handled_by"] !== null && $theTicket["statusId"] !== 1) {
            return ["success" => false, "message" => "Ticket is not eligible to be taken."];
        }

        // Prevent admin from taking their own tickets
        if ($theTicket["created_by"] === $data["admin_id"]) {
            return ["success" => false, "message" => "You cannot take your own ticket."];
        }

        if ($theTicket["created_by"] !== $data["creator_id"]) {
            return ["success" => false, "message" => "Ticket creator mismatch."];
        }

        $data["title"] = $theTicket["title"];

        return ["success" => true, "data" => $data];
    }

    /**
     * Assigns a ticket to an admin user.
     * Retrieves user details by user ID.
     *
     * @param int $ticketID The ID of the ticket to be taken.
     * @param int $adminID The ID of the admin user taking the ticket.
     * @param int $creatorID The ID of the ticket creator.
     * @param string $title The title of the ticket.
     * @throws RuntimeException If the ticket assignment fails or database request failed.
     * @return void
     * @see User::getUserById() for fetching user details.
     * @see Ticket::takeTicket() for updating the ticket assignment in the database.
     */
    public function takeTicket(int $ticketID, int $adminID, int $creatorID, string $title): void
    {
        // Fetch the ticket creator details
        $creator = $this->userModel->getUserById($creatorID);

        $this->ticketModel->takeTicket($ticketID, $adminID);

        // Send notification email to the ticket creator
        $this->notificationService->takeTicketNotification($creator["email"], $creator["name"], $creator["surname"], $title, $ticketID);
    }
}
