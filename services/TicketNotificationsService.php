<?php

require_once 'BaseService.php';
require_once 'EmailService.php';

class TicketNotificationsService extends BaseService
{
    private EmailService $emailService;

    private function ticketNotifications(): void
    {
        $this->createTicketNotification();
        $this->handlingTicketNotification();
        $this->messagesTicketNotification();
    }

    private function createTicketNotification(): void
    {
        // Implementation for creating ticket notification email
        // Email se salje kreatoru tikeata
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
