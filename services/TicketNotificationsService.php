<?php

require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'services' . DS . 'EmailService.php';

class TicketNotificationsService extends BaseService
{
    private EmailService $emailService;

    public function __construct()
    {
        $this->emailService = new EmailService();
    }

    /**
     * Send a ticket creation notification email to the user.
     * 
     * @param string $email The user's email address.
     * @param string $name The user's first name.
     * @param string $surname The user's surname.
     * @param string $title The title of the created ticket.
     * @param string $description The description of the created ticket.
     * @param int $ticketId The ID of the created ticket.
     * @return void
     * @throws Exception If email sending fails.
     * @see EmailService::sendEmail()
     */
    public function createTicketNotification(string $email, string $name, string $surname, string $title, string $description, int $ticketId): void
    {
        $subject = "Create Ticket Notification";
        $linkUrl  = BASE_URL . "user/user-view-ticket.php?ticket=" . $ticketId;
        $linkText = "Click Here to View Your Ticket:";

        // Build the email content
        $body    = require_once ROOT . 'EmailTemplates' . DS . 'create_ticket_notification_email.php';

        // Plain text alternative body
        $altBody =
            "Hello {$name} {$surname},\n" .
            "Your ticket has been created in the system.\n\n" .
            "Ticket ID: {$ticketId}.\n" .
            "Title: {$title}.\n" .
            "Description: {$description}.\n" .
            "You can view the ticket here: " . BASE_URL . "user/user-view-ticket.php?ticket={$ticketId}\n\n" .
            "Best regards,\n" .
            "The Ticketing System Team";

        // Sends the email
        $this->emailService->sendEmail(email: $email, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
    }

    /**
     * Send a ticket assignment notification email to the user.
     * 
     * @param string $email The user's email address.
     * @param string $name The user's first name.
     * @param string $surname The user's surname.
     * @param string $title The title of the ticket.
     * @param int $ticketId The ID of the ticket.
     * 
     * @return void
     * @throws Exception If email sending fails.
     * @see EmailService::sendEmail()
     */
    public function takeTicketNotification(string $email, string $name, string $surname, string $title, int $ticketId): void
    {
        $subject  = "Your ticket is assigned to an administrator";
        $linkUrl  = BASE_URL . "user/user-view-ticket.php?ticket=" . $ticketId;
        $linkText = "Click Here to View Your Ticket:";

        // Build the email content
        $body     = require_once ROOT . 'EmailTemplates' . DS . 'take_ticket_notification_email.php';

        // Plain text alternative body
        $altBody  =
            "Hello {$name} {$surname},\n" .
            "Your ticket \"{$title}\" with ID {$ticketId} is assigned to an administrator.\n\n" .
            "You can view the ticket here: {$linkUrl}\n\n" .
            "Best regards,\n" .
            "The Ticketing System Team";

        $this->emailService->sendEmail(email: $email, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
    }

    /**
     * Send a ticket close or reopen notification email to the user.
     * 
     * @param string $email The user's email address.
     * @param string $name The user's first name.
     * @param string $surname The user's surname.
     * @param string $title The title of the ticket.
     * @param int $ticketId The ID of the ticket.
     * @param string $action The action performed on the ticket ("close" or "reopen").
     * 
     * @return void
     * @throws Exception If email sending fails.
     * @see EmailService::sendEmail()
     */
    public function closeReopenNotification(string $email, string $name, string $surname, string $title, int $ticketId, string $action): void
    {
        $ucfirstAction   = ucfirst($action);
        $actionPastTense = $action === "close" ? "closed" : "reopened";
        $subject         = "Ticket {$ucfirstAction} Notification";
        $linkUrl         = BASE_URL . "user/user-view-ticket.php?ticket=" . $ticketId;
        $linkText        = "Click Here to View Your Ticket:";

        // Build the email content
        $body            = require_once ROOT . 'EmailTemplates' . DS . 'close_reopen_ticket_notification_email.php';

        // Plain text alternative body
        $altBody  =
            "Hello {$name} {$surname},\n" .
            "Your ticket \"{$title}\" with ID {$ticketId} is {$actionPastTense}.\n\n" .
            "You can view the ticket here: {$linkUrl}\n\n" .
            "Best regards,\n" .
            "The Ticketing System Team";

        // Sends the email
        $this->emailService->sendEmail(email: $email, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
    }
}
