<?php

trait PaginationTrait
{
    /**
     * Gets current page from query string parameter "page".
     * 
     * @return int Returns current page if page parameter from query strng is set and valid, otherwise 1.
     */
    public function getCurrentPage(): int
    {
        if (isset($_GET["page"])) {
            $currentPage = $this->validateId($_GET["page"]);
            if ($currentPage === false || $currentPage < 1) {
                $currentPage = 1;
            }
        }

        return $currentPage ?? 1;
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

    /**
     * Validates the 'limit' parameter from the GET request and manages session storage.
     * Limit parameter controls the number of results per page for ticket listings.
     * 
     * @return int The validated limit value. Returns 0 for "all" or if no limit is set, otherwise returns a positive integer.
     */
    public function validateLimitRequest(): array
    {
        // Set results per page
        if (isset($_GET["limit"])) {
            if ($_GET["limit"] !== "all") {
                $limit = $this->validateId($_GET["limit"]);
                $_SESSION["limit"] = $limit = $limit === false ? 0 : $limit;
            } else {
                $limit = $_SESSION["limit"] = 0;
            }
        } elseif (!isset($_GET["limit"]) && isset($_SESSION["limit"])) {
            $limit = (int)trim($_SESSION["limit"]);
        } else {
            $limit = 10;
        }

        $preparedLimit = $this->prepareLimitOptions($limit);

        $_SESSION["limit"] = $preparedLimit["limit"];

        return ["limit" => $preparedLimit["limit"], "options" => $preparedLimit["options"]];
    }

    /**
     * Validates the 'order_by' parameter from the GET request.
     *
     * @return string Returns "ASC" for "oldest" and "DESC" for any other value.
     */
    public function validateOrderByRequest(): string
    {
        return (isset($_GET["order_by"]) && trim($_GET["order_by"]) === "oldest") ?  "ASC" : "DESC";
    }

    /**
     * Validates the 'sort' parameter from the GET request.
     * 
     * @return array{table: string|null, cleanSortBy: string|null}
     *         - 'table': The corresponding table for the sortBy value or null if not applicable.
     *         - 'cleanSortBy': The sanitized sortBy value or null if not provided.
     * 
     * @see TicketListingService::validateSortBy()
     */
    public function validateSortByRequest(): array
    {
        if (isset($_GET['sort']) && !empty(trim($_GET['sort']))) {

            // Trims and sanatizates
            $sortBy = cleanString($_GET['sort']);

            return $this->service->validateSortBy($sortBy);
        } else {
            return ["table" => null, "cleanSortBy" => null];
        }
    }
}
