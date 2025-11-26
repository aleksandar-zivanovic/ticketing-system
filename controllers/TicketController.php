<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';
require_once ROOT . 'services' . DS . 'TicketService.php';

class TicketController extends BaseController
{
    private TicketService $service;

    public function __construct()
    {
        $this->service = new TicketService();
    }

    /**
     * Validates $_POST request data for creating a ticket.
     *
     * Checks required fields (URL, title, description, department, priority, user)
     * and performs sanitization. Also runs additional validation via service.
     * @return array Validation result with 'success' key and either 'data' or 'message' key.
     */
    private function validateCreateRequest(): array
    {
        // Validates request method and user action
        if (
            $_SERVER['REQUEST_METHOD'] !== "POST" ||
            !isset($_POST['user_action']) ||
            $_POST['user_action'] !== "Create Ticket"
        ) {
            return ["success" => false, "message" => "Invalid request method or user action.", "url" => "error"];
        }

        // Validates existence of the URL from the form input.
        if ($this->hasValue($_POST["error_page"]) === false) {
            return ["success" => false, "message" => "Error page URL is missing.", "url" => "error"];
        }

        // Validates and sanitizes URL
        $values["url"] = $this->validateUrl($_POST["error_page"]);
        if ($values["url"] === false) {
            return ["success" => false, "message" => "Invalid error page URL.", "url" => "error"];
        }

        // Validates and sanitizes title
        if ($this->hasValue($_POST["error_title"]) === false) {
            return ["success" => false, "message" => "Title is not set."];
        }
        $values["title"] = cleanString($_POST["error_title"]);

        // Validates and sanitizes description
        if ($this->hasValue($_POST["error_description"]) === false) {
            return ["success" => false, "message" => "Description is not set."];
        }
        $values["description"] = cleanString($_POST["error_description"]);

        // Validates existence of department ID from the form input.
        if ($this->hasValue($_POST["error_department"]) === false) {
            return ["success" => false, "message" => "Department is not set."];
        }

        // Validates and sanitizes department ID
        $values["departmentId"] = $this->validateId($_POST["error_department"]);
        if ($values["departmentId"] === false) {
            return ["success" => false, "message" => "Department ID is invalid."];
        }

        // Validates existence of priority ID from the form input.
        if ($this->hasValue($_POST["error_priority"]) === false) {
            return ["success" => false, "message" => "Priority ID is not set."];
        }

        // Validates and sanitizes priority ID
        $values["priorityId"] = $this->validateId($_POST["error_priority"]);
        if ($values["priorityId"] === false) {
            return ["success" => false, "message" => "Priority ID is invalid."];
        }

        // Validates and sanitizes creator ID
        $values["userId"] = $this->validateId($_SESSION["user_id"]);

        // Validation from service layer
        $serviceValidation = $this->service->validateCreate($values);
        if ($serviceValidation["success"] === false) {
            return $serviceValidation;
        }

        return ["success" => true, "data" => $values];
    }

    public function validateCreateShowRequest(): array
    {
        if (($method = $this->ensureMethod("GET", "source"))["success"] === false) {
            return $method;
        }

        $source = $this->validateUrl(trim(($_GET["source"])));
        if ($source === false) {
            return ["success" => false, "message" => "Invalid source URL.", "url" => "error"];
        }

        return $this->service->validateShowCreate(["source" => $source]);
    }

    public function show(): void
    {
        $validation = $this->validateCreateShowRequest();
        $this->redirectUrl = "/ticketing-system/create_ticket.php?{$validation["data"]["source"]}";
        $this->handleValidation($validation);
        $this->render("create_ticket.php", $validation["data"]);
    }

    public function validateDeleteRequest(): array
    {
        // Validates request method and user action
        if (
            $_SERVER['REQUEST_METHOD'] !== "POST" ||
            !isset($_POST['delete_ticket']) ||
            $_POST['delete_ticket'] !== "Delete Ticket"
        ) {
            return ["success" => false, "message" => "Invalid request method or user action.", "url" => "error"];
        }

        // Validates existence of ticket ID from the form input.
        if (!isset($_POST["ticket_id"]) || empty($_POST["ticket_id"])) {
            return ["success" => false, "message" => "Ticket ID is missing.", "url" => "error"];
        }

        // Validates and sanitizes ticket ID
        $data["ticket_id"] = $this->validateId($_POST["ticket_id"]);
        if ($data["ticket_id"] === false) {
            return ["success" => false, "message" => "Invalid ticket ID.", "url" => "error"];
        }

        // Validates and sanitizes user ID from session
        $data["user_id"] = $this->validateId($_SESSION["user_id"]);
        if ($data["user_id"] === false) {
            return ["success" => false, "message" => "Invalid user.", "url" => "error"];
        }

        // Fetches ticket creator ID to determine redirection URL after deletion
        $creatorId = $this->service->getTicketCreatorId($data["ticket_id"]);

        // If ticket does not exist
        if ($creatorId === false) {
            return ["success" => false, "message" => "Ticket not found.", "url" => "error"];
        }

        // Sets redirection URL based on whether the user is the ticket creator or an admin
        $redirectUrlSegment = $creatorId === $data["user_id"] ?
            "user/user-view-ticket.php?ticket=" :
            "admin/view-ticket.php?ticket=";

        $this->redirectUrl = "/ticketing-system/" . $redirectUrlSegment . $data["ticket_id"];

        return $this->service->validateDelete($data);
    }

    /**
     * Creates a new ticket.
     * Validates request data, creates the ticket, and handles redirection.
     * On success, redirects to the ticket view page with a success message.
     * On failure, redirects back to the ticket creation form with an error message.
     * 
     * @return void
     * @see Ticket::createTicket()
     * @see Attachment::processImages()
     */
    public function createTicket(): void
    {
        // Validates the request data
        $validation = $this->validateCreateRequest();
        $this->handleValidation($validation);

        $emailDetails = $this->getCurrentUserForEmail();
        $email   = $emailDetails["email"];
        $name    = $emailDetails["name"];
        $surname = $emailDetails["surname"];

        // Create ticket
        try {
            $ticketId = $this->service->createTicket(data: $validation["data"], email: $email, name: $name, surname: $surname, ticketAttachments: null, split: false);
            redirectAndDie(
                "/ticketing-system/user/user-view-ticket.php?ticket={$ticketId}",
                "New ticket created with ID: {$ticketId}",
                "success"
            );
        } catch (\Throwable $th) {
            redirectAndDie(
                "/ticketing-system/forms/create_ticket.php?source=" . $validation["url"],
                "Ticket creation failed. Please try again."
            );
        }
    }

    /**
     * Deletes tickets and its attachments from database and server
     * 
     * @return void
     * @throws RuntimeException If deletion of files from server or `attachments` table fails
     * @throws Exception If ticket ID is invalid or ticket deletion fails.
     */
    public function deleteTicket(): void
    {
        $validation = $this->validateDeleteRequest();
        $this->handleValidation($validation);

        try {
            $this->service->deleteTicket();
            if (str_contains($this->redirectUrl, "user-view-ticket.php")) {
                $this->redirectUrl = "/ticketing-system/user/user-ticket-listing.php";
            } else {
                $this->redirectUrl = "/ticketing-system/admin/admin-ticket-listing.php";
            }
            redirectAndDie(
                $this->redirectUrl,
                "Ticket with ID {$validation['data']['ticket_id']} is deleted successfully!",
                "success"
            );
        } catch (\Throwable $th) {
            redirectAndDie(
                $this->redirectUrl,
                "Something went wrong. Try again.",
            );
        }
    }
}
