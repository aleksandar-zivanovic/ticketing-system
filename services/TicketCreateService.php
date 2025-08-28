<?php
require_once '../classes/Ticket.php';

class TicketCreateService
{
    private Ticket $ticket;

    public function __construct()
    {
        $this->ticket = new Ticket();
    }

    /** 
     * Validates URL format.
     * Returns sanitized URL string or false if not valid.
     * @param string $url URL to validate.
     * @return string|false Validated URL or false if not valid.
     */
    public function validateUrl(string $url): string|false
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * Validates text length.
     * Returns true if text length is equal or greater than specified length, otherwise false.
     * @param string $text Text to validate.
     * @param string $length Minimum length required.
     * @return bool True if valid, false otherwise.
     */
    public function validateText(string $text, string $length): bool
    {
        if (strlen($text) < $length) {
            return false;
        }
        return true;
    }

    /**
     * Validates integer value.
     * Returns the integer if it is a valid integer greater than zero, otherwise false.
     * @param int $number Number to validate.
     * @return int|false Validated integer or false if not valid.
     */
    public function validateInt(int $number): int|false
    {
        return filter_var($number, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);    
    }

    /**
     * Creates a ticket using the Ticket class.
     * @param array $data Associative array containing ticket data.
     * @return void
     */
    public function createTicket(array $data): void
    {
        $this->ticket->createTicket(split: false, ticketAttachments: null, attachment:null, data: $data);
    }
}
