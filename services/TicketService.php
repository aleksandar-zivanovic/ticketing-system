<?php
require_once '../../classes/Ticket.php';
require_once '../../classes/Pagination.php';

class TicketService
{
    /** Fetch tickets for pagination with given parameters
     *
     * @param array $allowedValues - Allowed filter values for tickets
     * @param string $orderBy - Order by parameter
     * @param string|null $sortBy - Sort by parameter
     * @param int $limit - Number of results per page
     * @param bool $handledByMe - Whether to fetch tickets handled by the current user
     * @return array - Array of tickets
     */
    public function fetchTicketsForPagination($allowedValues, $orderBy, $sortBy, $limit, $handledByMe = false)
    {
        $ticket = new Ticket();
        return $ticket->fetchAllTickets(allowedValues: $allowedValues, orderBy: $orderBy, sortBy: $sortBy, limit: $limit, handledByMe: $handledByMe);
    }

    /** Count total tickets for pagination
     *
     * @param array $allowedValues - Allowed filter values for tickets
     * @param string $orderBy - Order by parameter
     * @param string|null $sortBy - Sort by parameter
     * @param bool $handledByMe - Whether to count tickets handled by the current user
     * @return int - Total number of tickets
     */
    public function countAllTicketsForPagination($allowedValues, $orderBy, $sortBy, $handledByMe = false)
    {
        $ticket = new Ticket();
        return $ticket->countAllTickets(allowedValues: $allowedValues, orderBy: $orderBy, sortBy: $sortBy, handledByMe: $handledByMe);
    }

    /** Count total pages for pagination
     *
     * @param int $limit - Number of results per page
     * @param int $totalItems - Total number of items
     * @return int - Total number of pages
     */
    public function countTotalPages(int $limit, int $totalItems)
    {
        $pagination = new Pagination($limit, $totalItems);
        return $pagination->getTotalPages();
    }

    /** Get pagination object
     *
     * @param int $limit - Number of results per page
     * @param int $totalItems - Total number of items
     * @return array - Array containing currentPage, totalPages, and pagination object
     */
    public function getPaginationData(int $limit, int $totalItems): array
    {
        $pagination = new Pagination($limit, $totalItems);
        $currentPage = $pagination->getCurrentPage();
        $totalPages = $pagination->getTotalPages();
        return [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'pagination' => $pagination
        ];
    }
}
