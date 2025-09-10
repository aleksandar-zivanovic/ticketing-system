<?php
require_once '../../classes/Ticket.php';
require_once 'BaseService.php';

class TicketService extends BaseService
{
    private Ticket $ticket;
    private array $ticketDetails;

    public function __construct()
    {
        $this->ticket = new Ticket();
    }

    public function setTicketDetails(array $ticketDetails): void
    {
        $this->ticketDetails = $ticketDetails;
    }

    /**
     * Service validation.
     * 
     * @return array Associative array with 'success' (bool) and 'message' (string).
     */
    public function validateCreate(array $data): array
    {
        // Validates minimal title length
        if ($this->validateTextLength($data["title"], 5) === false) {
            return ["success" => false, "message" => "Title must be at least 5 characters long."];
        }

        // Validates minimal description length
        if ($this->validateTextLength($data["description"], 15) === false) {
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

        // Validates if the creator is real and verified
        if ($this->validateUser($data["userId"]) === false) {
            return ["success" => false, "message" => "User is not valid."];
        }

        return ["success" => true];
    }

    /**
     * Checks if a user with the selected ID really exists
     * 
     * @param int $userId ID column from users table.
     * @return bool True if a user exist, othewise false.
     */
    private function validateUser(int $userId): bool
    {
        require_once '../../classes/User.php';
        $user = new User();
        $userDetails = $user->getUserById($userId);

        if ($userDetails === null || $userDetails["verified"] !== 1) {
            return false;
        }

        return true;
    }

    /**
     * Fetches ticket data and associated details from related tables.
     * 
     * @param int $ticketId A ticket id.
     * @return array The result contains ticket information, including optional image attachments.
     * @throws Exception If there is a PDOException while executing the SQL query.
     */
    private function fetchTicketDetails(int $id): array
    {
        return $this->ticket->fetchTicketDetails($id);
    }

    private function allMessagesByTicket(int $id): array
    {
        require_once '../../classes/Message.php';
        $message = new Message();
        return $message->allMessagesByTicket($id);
    }

    /**
     * Service deletion validation.
     * 
     * @param int $ticketId ID of ticket for deletion
     * @return array{success: bool, message?: string, panel: string}
     */
    public function validateDelete(int $ticketId): array
    {
        // $ticket = $this->fetchTicketDetails($ticketId);
        $this->ticketDetails = $this->fetchTicketDetails($ticketId);
        $panel = $this->ticketDetails["created_by"] !== trim($_SESSION['user_id']) && trim($_SESSION["user_role"] === "admin") ? "admin" : "user";


        $allMessages = $this->allMessagesByTicket($ticketId);

        // Validate user's deletion premission. 
        if (
            // Checks if status is not "in progress"
            $this->ticketDetails["statusId"] !== 1 ||
            // Checks if someone handles the ticket
            $this->ticketDetails["handled_by"] != null ||
            // Checks if there are messages
            !empty($allMessages) ||
            // Checks if the current user is the ticket creator or an admin
            ($this->ticketDetails["created_by"] !== trim($_SESSION['user_id']) && trim($_SESSION["user_role"] !== "admin"))
        ) {
            return ["success" => false, "message" => "You are not authorized to delete this ticket.", "panel" => $panel];
        }

        return ["success" => true, "panel" => $panel];
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
            require_once 'AttachmentService.php';
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
    private function processAttachments(bool $split = false, ?array $ticketAttachments = null, ?Attachment $attachment = null, int $ticketId, ?int $parentId = null): void
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
                $this->ticketDetails = $this->ticket->fetchTicketDetails($ticketId);
                $this->deleteTicket();

                // If $parentId !== null thatm means that the ticket is created by splitting process
                // so we need to delete all child tickets.
                if ($parentId !== null) {
                    $childTickets = $this->ticket->getAllWhere("tickets", "parent_ticket = {$parentId}");
                    if (!empty($childTickets)) {
                        foreach ($childTickets as $childTicket) {
                            $this->ticketDetails = $this->ticket->fetchTicketDetails($childTicket["id"]);
                            $this->deleteTicket();
                        }
                    }

                    // Finally, we need to reopen the parent ticket if it was closed.
                    $this->ticket->updateTicket([
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

    /**
     * Creates a ticket using the Ticket class.
     * 
     * @param array $data Associative array containing ticket data.
     * @param ?callable $onTicketCreated Callback function to receive the new ticket ID.
     * @return int Returns the ID of the newly created ticket.
     * 
     * @throws RuntimeException If the query execution fails.
     * @throws UnexpectedValueException If the table name is invalid.
     * @throws Exception Exception If there is an error in images upload.
     * @see Ticket::createTicket()
     * @see Attachment::processImages()
     */
    // public function createTicket(array $data, ?callable $onTicketCreated = null): void
    // {
    public function createTicket(
        bool $split = false,
        ?array $ticketAttachments = null,
        ?array $data,
        ?int $parentId = null,
    ): int {
        if (!isset($attachment)) {
            require_once '../../classes/Attachment.php';
            $attachment = new Attachment();
        }
        $lastInsertId = $this->ticket->createTicket($split, $ticketAttachments, $attachment, $data, $parentId);

        // Add the year in `years` table.
        $this->ticket->addCurrentYear();

        // Proccesses files if they are attached in form:
        $this->processAttachments($split, $ticketAttachments, $attachment, $lastInsertId, $parentId);

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

        require_once "../../classes/helpers/IdValidator.php";
        list($ids, $params) = IdValidator::prepareIdsForQuery($id);

        return ["id" => $ids, "param" => $params];
    }

    /**
     * Deletes a ticket along with its attachments.
     * 
     * @return void
     * @throws RuntimeException If deletion of files from server or `attachments` table fails
     * @throws Exception If ticket ID is invalid or ticket deletion fails.
     * @see Ticket::deleteTicketRow()
     */
    public function deleteTicket(): void
    {
        // Deletes attachments if exists any 
        $this->deleteAttachments($this->ticketDetails);

        $preparedParams = $this->prepareIdsForDeletion($this->ticketDetails["id"]);
        $ids            = $preparedParams["id"];
        $params         = $preparedParams["param"];

        $this->ticket->deleteTicketRow($ids, $params);
    }
}
