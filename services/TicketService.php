<?php
require_once ROOT . 'classes' . DS . 'Ticket.php';
require_once ROOT . 'classes' . DS . 'User.php';
require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'services' . DS . 'TicketNotificationsService.php';
require_once ROOT . 'services' . DS . 'TicketShowService.php';

class TicketService extends BaseService
{
    private Ticket $ticketModel;
    private User $userModel;
    private TicketShowService $ticketShowService;
    private TicketNotificationsService $notificationsService;
    private array|false $ticketDetails;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
        $this->userModel = new User();
        $this->ticketShowService = new TicketShowService();
        $this->notificationsService = new TicketNotificationsService();
    }

    public function validateShowCreate($data): array
    {
        if (!str_contains($data["source"], APP_DOMAIN)) {
            return ["success" => false, "message" => "Invalid domain"];
        }

        $data["create"] = true;
        return $this->ticketShowService->validate($data);
    }

    /**
     * Service validation.
     * 
     * @return array Associative array with 'success' (bool) and 'message' (string).
     */
    public function validateCreate(array $data): array
    {
        // Validate domain
        if (!str_contains($data["url"], APP_DOMAIN)) {
            return ["success" => false, "message" => "Invalid domain"];
        }

        // Validates minimal title length
        if ($this->validateTextLength($data["title"], 5, null) === false) {
            return ["success" => false, "message" => "Title must be at least 5 characters long."];
        }

        // Validates minimal description length
        if ($this->validateTextLength($data["description"], 15, null) === false) {
            return ["success" => false, "message" => "Description must be at least 15 characters long."];
        }

        // Validate departments
        if ($this->validateDepartments($data['departmentId']) === false) {
            return ["success" => false, "message" => "Selected department is not valid."];
        }

        // Validate priorities
        if ($this->validatePriorities($data['priorityId']) === false) {
            return ["success" => false, "message" => "Selected priority is not valid."];
        }

        return ["success" => true];
    }

    /**
     * Fetches ticket data and associated details from related tables.
     * 
     * @param int $ticketId A ticket id.
     * @return array|false An associative array containing ticket details, or false if the ticket does not exist.
     * @throws RuntimeException If there is a PDOException while executing the SQL query.
     */
    private function fetchTicketDetails(int $id): array|false
    {
        $ticket = $this->ticketModel->fetchTicketDetails($id);

        if (empty($ticket["id"])) {
            return false;
        }
        return $ticket;
    }

    /**
     * Gets the ID of the user who created the ticket.
     * 
     * @return int|false The ID of the user who created the ticket, or false if the ticket does not exist.
     * @throws RuntimeException If there is a PDOException while executing the SQL query.
     */
    public function getTicketCreatorId(int $id): int|false
    {
        $this->ticketDetails = $this->fetchTicketDetails($id);
        if ($this->ticketDetails === false) {
            return false;
        }
        return $this->ticketDetails["created_by"];
    }

    /**
     * Retrieves all messages associated with a specific ticket.
     * 
     * @param int $id The ID of the ticket.
     * @return array An array of messages related to the ticket.
     * @throws RuntimeException If there is a PDOException while executing the SQL query.
     * @see Message::allMessagesByTicket()
     */
    private function allMessagesByTicket(int $id): array
    {
        require_once ROOT . 'classes' . DS . 'Message.php';
        $message = new Message();
        return $message->allMessagesByTicket($id);
    }

    /**
     * Service deletion validation.
     * 
     * @param array $data Sanitized data from POST request.
     * @return array{success: bool, message?: string, panel: string}
     */
    public function validateDelete(array $data): array
    {
        // Checks if the ticket status allows deletion (only "waiting" status is allowed)
        if ($this->ticketDetails["statusId"] !== 1) {
            return ["success" => false, "message" => "Ticket is not in a deletable state."];
        }

        // Fetches user details
        $user = $this->userModel->getUserById($data["user_id"]);

        // Determines if user is authorized to delete the ticket (is the ticket creator or an admin)
        if ($data["user_id"] !== $this->ticketDetails["created_by"] && $user["role_id"] !== 3) {
            return ["success" => false, "message" => "You are not authorized to delete this ticket."];
        }

        // Child tickets cannot be deleted
        if ($this->ticketDetails["parent_ticket"] !== null) {
            return ["success" => false, "message" => "Child tickets cannot be deleted."];
        }

        // Parent tickets cannot be deleted
        if ($this->ticketModel->hasChildren($data["ticket_id"]) === true) {
            return ["success" => false, "message" => "Parent tickets cannot be deleted."];
        }

        // Checks if the ticket has messages
        $allMessages = $this->allMessagesByTicket($data["ticket_id"]);
        if (!empty($allMessages)) {
            return ["success" => false, "message" => "Tickets with messages cannot be deleted."];
        }

        return ["success" => true, "data" => $data];
    }

    /**
     * Checks if the ticket has attachment(s) 
     * and deletes (server and database) if there are any attachment.
     * 
     * @param array $ticket Ticket data retrieved with Ticket::fetchTicketDetails()
     * @return void
     * @throws Exception If Attachment::getAttachmentsByIds() fails to fetch attachment(s) ID(s)
     * @throws RuntimeException If deletion of files from server or `attachments` table fails
     * @see AttachmentService::deleteAttachments()
     */
    public function deleteAttachments(array $ticket): void
    {
        if (!empty($this->ticketDetails["attachment_id"]) === true) {
            require_once ROOT . 'services' . DS . 'AttachmentService.php';
            $attachmentService = new AttachmentService();
            $attachmentService->deleteAttachments($ticket);
        }
    }

    /**
     * Processes and uploads attachments if there are any.
     * 
     * @param bool $split Indicates if the ticket is part of a split operation.
     * @param ?array $ticketAttachments Formatted array of attachments for multiple tickets, null a single ticket. Default is null.
     * @param ?Attachment $attachment Attachment object or null. Default is null.
     * @param int $ticketId ID of the ticket to which attachments are to be associated.
     * @return void
     * @throws Exception If there is an error in images upload or if ticket deletion fails after a failed upload.
     * @see Attachment::processImages()
     * @see TicketService::deleteTicket()
     */
    private function processAttachments(int $ticketId, bool $split = false, ?array $ticketAttachments = null, ?Attachment $attachment = null, ?int $parentId = null): void
    {
        // Proccesses files if they are attached in form:
        if ($ticketAttachments === null) {
            $ticketAttachments = $_FILES;
        }
        unset($_FILES);

        if ($ticketAttachments['error_images']['error'][0] != 4) {
            $imagesUpload = $attachment->processImages($ticketAttachments, $ticketId, "ticket_attachments", "error_images");

            // Deletes new ticket if images uploading failed.
            if ($imagesUpload === false) {
                $this->ticketDetails = $this->ticketModel->fetchTicketDetails($ticketId);
                $this->deleteTicket();

                // If $parentId !== null thatm means that the ticket is created by splitting process
                // so we need to delete all child tickets.
                if ($parentId !== null) {
                    $childTickets = $this->ticketModel->getAllWhere("tickets", "parent_ticket = {$parentId}");
                    if (!empty($childTickets)) {
                        foreach ($childTickets as $childTicket) {
                            $this->ticketDetails = $this->ticketModel->fetchTicketDetails($childTicket["id"]);
                            $this->deleteTicket();
                        }
                    }

                    // Finally, we need to reopen the parent ticket if it was closed.
                    $this->ticketModel->updateTicket([
                        [
                            "statusId" => 1,
                            "closed_date" => null,
                            "closing_type" => null
                        ]
                    ], [["id" => $parentId]]);
                }

                throw new RuntimeException("Ticket creation failed during images upload. The ticket is deleted.");
            }
        }
    }

    public function validateTakeTiket(): array
    {
        // Validate user's permission to take the ticket
        if ((trim($_SESSION["user_role"]) !== "admin") || trim($_SESSION["user_id"]) === $this->ticketDetails["created_by"]) {
            return ["success" => false, "message" => "You don't have the permission for this action!"];
        }

        return ["success" => true];
    }

    /**
     * Creates a ticket using the Ticket class.
     * 
     * @param array $data Associative array containing ticket data.
     * @param string $email The email of the user creating the ticket.
     * @param string $name The first name of the user creating the ticket.
     * @param string $surname The last name of the user creating the ticket.
     * @param ?array $ticketAttachments Formatted array of attachments for multiple tickets, null a single ticket. Default is null.
     * @param bool $split Indicates if the ticket is part of a split operation. Default is false.
     * @param ?int $parentId The ID of the parent ticket if this ticket is a child ticket. Default is null.
     * @return int Returns the ID of the newly created ticket.
     * 
     * @throws RuntimeException If the query execution fails.
     * @throws UnexpectedValueException If the table name is invalid.
     * @throws Exception Exception If there is an error in images upload.
     * @see Ticket::createTicket()
     * @see Attachment::processImages()
     */
    public function createTicket(
        array $data,
        string $email,
        string $name,
        string $surname,
        ?array $ticketAttachments = null,
        bool $split = false,
        ?int $parentId = null
    ): int {
        if (!isset($attachment)) {
            require_once ROOT . 'classes' . DS . 'Attachment.php';
            $attachment = new Attachment();
        }
        $lastInsertId = $this->ticketModel->createTicket($data, $split, $parentId);

        require_once ROOT . 'classes' . DS . 'Year.php';
        $yearInstance = new Year();
        if ($yearInstance->checkIfTheYearExists(date("Y")) === false) {
            $yearInstance->createYear(date("Y")); // Adds the year in `years` table.
        }

        // Proccesses files if they are attached in form:
        $this->processAttachments($lastInsertId, $split, $ticketAttachments, $attachment, $parentId);

        // Sends notification email about ticket creation if the ticket is not created by splitting process
        if ($split === false) {
            $this->notificationsService->createTicketNotification($email, $name, $surname, $data['title'], $data['description'], $lastInsertId);
        }

        return $lastInsertId;
    }

    /**
     * Normalizes and validates ticket IDs for deletion.
     *
     * @param int|string|array $id Ticket ID(s)
     * @return array An array containing the integer IDs and their respective placeholders for SQL.
     * @throws Exception If any of the IDs are invalid.
     */
    private function prepareIdsForDeletion(int|string|array $id): array
    {
        // Normalize all inputs to array for unified query processing
        if (is_string($id)) $id = explode(",", $id);
        if (is_int($id))    $id = [$id];

        require_once ROOT . 'classes' . DS . 'helpers' . DS . 'IdValidator.php';
        list($ids, $params) = IdValidator::prepareIdsForQuery($id);

        return ["id" => $ids, "param" => $params];
    }

    /**
     * Deletes a ticket along with its attachments from the database and server.
     * 
     * @return void
     * @throws RuntimeException If deletion of files from server or `attachments` table fails
     * @throws Exception If ticket ID is invalid or ticket deletion fails.
     * @see Ticket::deleteTicketRow()
     */
    public function deleteTicket(): void
    {
        try {
            $this->ticketModel->beginTransaction();
            // Deletes attachments if exists any 
            $this->deleteAttachments($this->ticketDetails);

            $preparedParams = $this->prepareIdsForDeletion($this->ticketDetails["id"]);
            $ids            = $preparedParams["id"];
            $params         = $preparedParams["param"];

            $this->ticketModel->deleteTicketRow($ids, $params);
            $this->ticketModel->commitTransaction();
        } catch (\Throwable $th) {
            $this->ticketModel->rollbackTransaction();
            throw $th;
        }
    }
}
