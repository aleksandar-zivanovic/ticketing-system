<?php
require_once ROOT . 'classes' . DS . 'User.php';
require_once ROOT . 'classes' . DS . 'Department.php';
require_once ROOT . 'classes' . DS . 'Priority.php';
require_once ROOT . 'classes' . DS . 'Status.php';
require_once ROOT . 'classes' . DS . 'Ticket.php';
require_once ROOT . 'Support' . DS . 'Paginator.php';
require_once ROOT . 'services' . DS . 'TicketReferenceService.php';
require_once ROOT . 'ViewModel' . DS . 'PaginationViewModel.php';

class TicketListingService
{
    private Ticket $ticketModel;
    private TicketReferenceService $ticketReferenceService;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
        $this->ticketReferenceService = new TicketReferenceService();
    }

    /** 
     * Validates the 'sort' parameter from the GET request.
     *
     * @param string $sortBy The sortBy parameter to validate
     * @return array{table: string|null, cleanSortBy: string|null}
     */
    public function validateSortBy(string $sortBy): array
    {
        $allowedValues = $this->ticketReferenceService->getReferenceData();

        if ($sortBy === null || $sortBy === "all") {
            $table = null;
        } else {
            foreach ($allowedValues as $key => $value) {
                if (in_array($sortBy, $value)) {
                    $table = $key;
                    break;
                }
            }
        }
        return ["table" => $table, "cleanSortBy" => $sortBy];
    }

    /**
     * Validates sorting and ordering values.
     * This method is used in methods for making queries for ticket listings. 
     * Provides table name for the WHERE clause in a query.
     * 
     * @param array $allowedValues An associative array of allowed values for ordering tickets.
     * @param ?string $sortBy The table name for sorting, defaults to null if not provided.
     * @return string|null Returns table name or null if everything is valid, otherwise throws exception;
     * @throws DomainException If the provided $sortBy or $orderBy value is not in the allowed values.
     */
    private function validateSortingAndOrdering(
        array $allowedValues,
        string $orderBy = "newest",
        ?string $sortBy = null
    ): string|null {
        // Checks if the $sortBy value is valid.
        $allowedSort = false;
        if ($sortBy === null || $sortBy === "all") {
            $allowedSort = true;
            $table = null;
        } else {
            foreach ($allowedValues as $key => $value) {
                if (in_array($sortBy, $value)) {
                    $allowedSort = true;
                    $table = $key;
                }
            }
        }

        // Checks if the $orderBy value is valid.
        $allowedOrder = false;
        if ($orderBy === "newest" || $orderBy === "oldest") {
            $allowedOrder = true;
        }

        // Throws an exception if either $sortBy or $orderBy is invalid.
        if ($allowedSort !== true ||  $allowedOrder !== true) {
            throw new DomainException("Invalid order/sort value!");
        }

        return $table;
    }

    /**
     * Prepares limit options for results per page selection.
     *
     * @param int $limit The current limit value
     * @return array An array of limit options with their selected status
     */
    public function prepareLimitOptions(int $limit): array
    {
        if (!in_array($limit, [5, 10, 20, 50]) && $limit !== 0) {
            $limit = 10;
        }

        $options = [
            ["value" => 5,     "selected" => $limit === 5],
            ["value" => 10,    "selected" => $limit === 10],
            ["value" => 20,    "selected" => $limit === 20],
            ["value" => 50,    "selected" => $limit === 50],
            ["value" => "all", "selected" => $limit === 0],
        ];
        return ["options" => $options, "limit" => $limit];
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
     * @param string $orderBy - Order by parameter
     * @param string|null $sortBy - Sort by parameter
     * @param int|null $userId - User ID to filter tickets for a specific user (optional)
     * @return int - Total number of tickets
     * @throws PDOException If a database query fails.
     * @see Ticket::countAllTickets()
     */
    private function countAllTicketsForPagination(string $action, array $allowedValues, string $orderBy, ?string $sortBy, ?int $userId = null): int
    {
        $table = $this->validateSortingAndOrdering($allowedValues, $orderBy, $sortBy);
        return $this->ticketModel->countAllTickets(action: $action, userId: $userId, sortBy: $sortBy, table: $table);
    }

    // /** Retrives total pages and Paginator object for pagination
    //  *
    //  * @param int $limit - Number of results per page
    //  * @param int $totalItems - Total number of items
    //  * @return array - Array containing currentPage, totalPages, and pagination object
    //  * @see Pagination::getCurrentPage()
    //  * @see Pagination::getTotalPages()
    //  */
    // private function getPaginationData(int $limit, int $totalItems): array
    // {
    //     $pagination = new Paginator($limit, $totalItems);
    //     $totalPages = $pagination->getTotalPages();
    //     return [
    //         "totalPages"  => $totalPages,
    //         "pagination"  => $pagination
    //     ];
    // }

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
     * @param string $orderBy "ASC" | "DESC" or "newest" | "oldest"
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
        $allowedValues = buildAllowedTicketValues($allTicketFilterData);

        // Fetch tickets and count total tickets based on the panel type
        if ($panel === "admin") {
            // Fetch tickets for pagination
            $data = $this->fetchTicketsForPagination(action: $action, userId: $userId, currentPage: $currentPage, orderBy: $orderBy, sortBy: $sortBy, table: $table, limit: $limit);

            // Count total tickets for pagination
            $totalItems = $this->countAllTicketsForPagination(action: $action, userId: $userId, allowedValues: $allowedValues, orderBy: $orderBy, sortBy: $sortBy);
        }

        if ($panel === "user") {
            // Fetch tickets for pagination
            $data = $this->fetchTicketsForPagination(action: $action, userId: $userId, currentPage: $currentPage, orderBy: $orderBy, sortBy: $sortBy, table: $table, limit: $limit);

            // Count total tickets for pagination
            $totalItems = $this->countAllTicketsForPagination(action: $action, userId: $userId, allowedValues: $allowedValues, orderBy: $orderBy, sortBy: $sortBy);
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
