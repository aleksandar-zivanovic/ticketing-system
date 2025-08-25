<?php
require_once ROOT . 'Support' . DS . 'Paginator.php';

class PaginationViewModel
{
    public function __construct(private Paginator $paginator) {}

    public function getPageButtons(int $currentPage, int $totalPages): array
    {
        $pages = [];

        // Creates an array of two pages before the current page and the current page
        for ($i = $currentPage - 2; $i <= $currentPage; $i++) {
            if ($i > 0) {
                $pages[] = $i;
            }
        }

        // Adds two pages after the current page to the array of pages
        for ($i = $currentPage + 1; $i <= $currentPage + 2; $i++) {
            if ($i <= $totalPages) {
                $pages[] = $i;
            }
        }

        return $pages;
    }
}
