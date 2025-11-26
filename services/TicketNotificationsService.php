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
        $siteUrl = "http://localhost/ticketing-system/";

        // Build the email content
        $body    = require_once ROOT . 'EmailTemplates' . DS . 'create_ticket_notification_email.php';

        // Plain text alternative body
        $altBody =
            "Hello {$name} {$surname},\n" .
            "Your ticket has been created in the system.\n\n" .

            "Ticket ID: {$ticketId}.\n" .
            "Title: {$title}.\n" .
            "Description: {$description}.\n" .

            "You can view the ticket here: {$siteUrl}user/user-view-ticket.php?ticket={$ticketId}\n\n" .

            "Best regards,\n" .
            "The Ticketing System Team";

        // Sends the email
        $this->emailService->sendEmail(email: $email, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
    }

    private function handlingTicketNotification(): void
    {
        // Implementation for updating ticket notification email
        // Email se salje kreatoru tikeata
    }

    private function messagesTicketNotification(): void
    {
        // Implementation for assigning ticket notification email
        // Email se salje ucesnicima chata na tiketu, osim onome ko je poslao poruku
    }
}
