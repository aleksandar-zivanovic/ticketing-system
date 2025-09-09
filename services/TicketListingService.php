<?php
require_once '../../classes/Ticket.php';
require_once '../../classes/Pagination.php';

class TicketListingService
{
    /** Fetches tickets for pagination with given parameters
     *
     * @param string $orderBy - Order by parameter
     * @param string|null $sortBy - Sort by parameter
     * @param int $limit - Number of results per page
     * @param int|null $userId - User ID to filter tickets for a specific user (optional)
     * @param bool $handledByMe - Whether to fetch tickets handled by the current user
     * @return array - Array of tickets
     * @see Ticket::fetchAllTickets()
     */
    public function fetchTicketsForPagination($orderBy, $sortBy, $table, $limit, $userId = null, $handledByMe = false)
    {
        $ticket = new Ticket();
        return $ticket->fetchAllTickets(orderBy: $orderBy, sortBy: $sortBy, table: $table, limit: $limit, userId: $userId, handledByMe: $handledByMe);
    }

    /** Counts total tickets for pagination
     *
     * @param array $allowedValues - Allowed filter values for tickets
     * @param string $orderBy - Order by parameter
     * @param string|null $sortBy - Sort by parameter
     * @param int|null $userId - User ID to filter tickets for a specific user (optional)
     * @param bool $handledByMe - Whether to count tickets handled by the current user
     * @return int - Total number of tickets
     * @see Ticket::countAllTickets()
     */
    public function countAllTicketsForPagination($allowedValues, $orderBy, $sortBy, $userId = null, $handledByMe = false): int
    {
        $ticket = new Ticket();
        return $ticket->countAllTickets(allowedValues: $allowedValues, orderBy: $orderBy, sortBy: $sortBy, userId: $userId, handledByMe: $handledByMe);
    }

    /** Counts total pages for pagination
     *
     * @param int $limit - Number of results per page
     * @param int $totalItems - Total number of items
     * @return int - Total number of pages
     * @see Pagination::getTotalPages()
     */
    public function countTotalPages(int $limit, int $totalItems): int
    {
        $pagination = new Pagination($limit, $totalItems);
        return $pagination->getTotalPages();
    }

    /** Retrives current page, total pages and pagination data.
     *
     * @param int $limit - Number of results per page
     * @param int $totalItems - Total number of items
     * @return array - Array containing currentPage, totalPages, and pagination object
     * @see Pagination::getCurrentPage()
     * @see Pagination::getTotalPages()
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
