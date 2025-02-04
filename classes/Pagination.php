<?php

class Pagination
{
    public function __construct(
        public int $limit, 
        public int $totalItems, 
    )
    {
        
    }

    /**
     * Gets current page from query string parameter "page".
     * 
     * @return int Returns current page if page parameter from query strng is set and valid, otherwise 1.
     */
    public function getCurrentPage(): int
    {
        if (isset($_GET["page"])) {
            $currentPage = trim(filter_input(INPUT_GET, "page", FILTER_VALIDATE_INT, [
                "options" => ["default" => 1, "min_range" => 1, "max_range" => $this->getTotalPages()] 
            ]));
        }

        return $currentPage ?? 1;
    }

    /**
     * Calculates total number of pages.
     * 
     * @return int Total number of pages.
     */
    public function getTotalPages(): int
    {
        return $this->limit === 0 ? 1 : ceil($this->totalItems / $this->limit);
    }

    /**
     * Builds a URL with the updated page parameter.
     * 
     * @param int $page The page number to set in the URL.
     * @return string Updated URL with the "page" parameter.
     */
    public function generateUrl($page)
    {
        $currentUrl = $_SERVER['REQUEST_URI'];
        $parsedUrl = parse_url($currentUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);
        unset($queryParams['page']);
        $queryParams['page'] = $page;

        return $parsedUrl['path'] . "?" . http_build_query($queryParams);
    }
}