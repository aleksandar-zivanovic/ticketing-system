<?php
require_once ROOT . 'classes' . DS . 'User.php';
require_once ROOT . 'classes' . DS . 'Department.php';
require_once ROOT . 'classes' . DS . 'Priority.php';
require_once ROOT . 'classes' . DS . 'Status.php';
require_once ROOT . 'classes' . DS . 'Ticket.php';
require_once ROOT . 'Support' . DS . 'Paginator.php';
require_once ROOT . 'services' . DS . 'TicketReferenceService.php';
require_once ROOT . 'services' . DS . 'SortingAndOrderingService.php';
require_once ROOT . 'ViewModel' . DS . 'PaginationViewModel.php';

class TicketListingService
{
    private Ticket $ticketModel;
    private TicketReferenceService $ticketReferenceService;
    private SortingAndOrderingService $sortingAndOrderingService;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
        $this->ticketReferenceService = new TicketReferenceService();
        $this->sortingAndOrderingService = new SortingAndOrderingService();
    }

    /** Fetches tickets for pagination with given parameters
     *
     * @param string $action - Action type for the listing (e.g., "all", "my", "handling")
     * @param int $currentPage - Current page number for pagination
     * @param string $orderBy - Order by parameter
     * @param string|null $sortBy - Sort by parameter
     * @param string|null $table - Column from tickets table
     * @param int $limit - Number of results per page
     * @param int|null $userId - User ID to filter tickets for a specific user (optional)
     * 
     * @return array - Array of tickets
     * @throws PDOException If a database query fails.
     * @see Ticket::fetchAllTickets()
     */
    private function fetchTicketsForPagination(string $action, int $currentPage, string $orderBy, ?string $sortBy, ?string $table, int $limit, ?int $userId = null)
    {
        return $this->ticketModel->fetchAllTickets(action: $action, currentPage: $currentPage, orderBy: $orderBy, sortBy: $sortBy, table: $table, limit: $limit, userId: $userId);
    }

    /** Counts total tickets for pagination
     *
     * @param string $action - Action type for the listing (e.g., "all", "my", "handling")
     * @param array $allowedValues - Allowed filter values for tickets
     * @param string|null $sortBy - Sort by parameter
     * @param int|null $userId - User ID to filter tickets for a specific user (optional)
     * @return int - Total number of tickets
     * @throws PDOException If a database query fails.
     * @see Ticket::countAllTickets()
     */
    private function countAllTicketsForPagination(string $action, array $allowedValues, ?string $sortBy, ?int $userId = null): int
    {
        $data = $this->sortingAndOrderingService->validateSortingAndOrdering($allowedValues, $sortBy);

        // If action is "all", we don't filter by user ID, because we want total count of all tickets
        if ($action === "all") {
            $userId = null;
        }
        return $this->ticketModel->countAllTickets(action: $action, userId: $userId, sortBy: $sortBy, table: $data["table"]);
    }

    /**
     * Prepares tickets listing data for the partial template.
     *
     * - Fetches tickets based on panel type (admin/user) and optional "handled by me" filter.
     * - Applies sorting, filtering, and pagination.
     * - Loads supporting data for dropdown filters: statuses, priorities, and departments.
     *
     * @param string $action Action type for the listing (e.g., "all", "my", "handling")
     * @param string $panel "admin" or "user" – determines ticket scope and links
     * @param string|null $sortBy Filter to sort by (status, priority, department, etc.)
     * @param string $orderBy "ASC" | "DESC" or "oldest" | "newest"
     * @param string|null $table Column from tickets table
     * @param int $limit Number of tickets per page
     * @param int $userId User ID from session to filter tickets for a specific user
     * @return array{
     *     data: array,           // List of tickets matching filters
     *     totalItems: int,       // Total number of tickets matching filters
     *     currentPage: int,      // Current pagination page
     *     totalPages: int,       // Total pagination pages
     *     pagination: array,     // Array of pagination links
     *     statuses: array,       // Allowed ticket statuses
     *     priorities: array,     // Allowed ticket priorities
     *     departments: array     // Allowed ticket departments
     * }
     * @throws PDOException If a database query fails.
     * @see TicketListingService::fetchTicketsForPagination()
     * @see TicketListingService::countAllTicketsForPagination()
     * @see helpers/functions.php loadTicketFilterData()
     * @see helpers/functions.php buildAllowedTicketValues()
     */
    public function prepareTicketsListingData(string $action, string $panel, ?string $sortBy, string $orderBy, ?string $table, int $limit, int $userId, int $currentPage, array $options): array
    {
        // Initializes allowed filter values for tickets
        $allTicketFilterData = $this->ticketReferenceService->getReferenceData();
        $statuses    = $allTicketFilterData["statuses"];
        $priorities  = $allTicketFilterData["priorities"];
        $departments = $allTicketFilterData["departments"];

        // Sets allowed values list for fetchAllTickets() method
        $allowedValues = array_merge(
            ["statuses" => $statuses],
            ["priorities" => $priorities],
            ["departments" => $departments],
        );

        // Fetch tickets and count total tickets based on the panel type
        if ($panel === "admin") {
            // Fetch tickets for pagination
            $data = $this->fetchTicketsForPagination(action: $action, userId: $userId, currentPage: $currentPage, orderBy: $orderBy, sortBy: $sortBy, table: $table, limit: $limit);

            // Count total tickets for pagination
            $totalItems = $this->countAllTicketsForPagination(userId: $userId, allowedValues: $allowedValues, sortBy: $sortBy, action: $action);
        }

        if ($panel === "user") {
            // Fetch tickets for pagination
            $data = $this->fetchTicketsForPagination(action: $action, userId: $userId, currentPage: $currentPage, orderBy: $orderBy, sortBy: $sortBy, table: $table, limit: $limit);

            // Count total tickets for pagination
            $totalItems = $this->countAllTicketsForPagination(userId: $userId, allowedValues: $allowedValues, sortBy: $sortBy, action: $action);
        }

        // Get pagination data
        $paginator  = new Paginator($limit, $totalItems);
        $totalPages = $paginator->getTotalPages();

        $viewModel = new PaginationViewModel($paginator);
        $pages = $viewModel->getPageButtons($currentPage, $totalPages);

        return [
            "orderBy"        => $orderBy,
            "sortBy"         => $sortBy,
            "data"           => $data,
            "totalItems"     => $totalItems,
            "currentPage"    => $currentPage,
            "totalPages"     => $totalPages,
            "pagination"     => $paginator,
            "statuses"       => $statuses,
            "priorities"     => $priorities,
            "departments"    => $departments,
            "panel"          => $panel,
            "pages"          => $pages,
            "options"        => $options
        ];
    }
}
