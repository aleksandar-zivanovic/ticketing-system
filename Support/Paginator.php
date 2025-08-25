<?php

class Paginator
{
    public function __construct(
        public int $limit, 
        public int $totalItems, 
    )
    {
        
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
     * @param int $limit The limit value to set in the URL.
     * @return string Updated URL with the "page" parameter.
     */
    public function generateUrl(int $page, int $limit): string
    {
        $currentUrl = $_SERVER['REQUEST_URI'];
        $parsedUrl = parse_url($currentUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);
        unset($queryParams['page']);
        $queryParams['page'] = $page;
        $queryParams['limit'] = $limit;

        return $parsedUrl['path'] . "?" . http_build_query($queryParams);
    }
}