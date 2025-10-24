<?php

class SortingAndOrderingService
{
    /**
     * Validates the 'sort' parameter from the GET request.
     * 
     * @param array $allowedValues An associative array of allowed values for ordering table rows.
     * @return array{table: string|null, cleanSortBy: string|null}
     *         - 'table': The corresponding table for the sortBy value or null if not applicable.
     *         - 'cleanSortBy': The sanitized sortBy value or null if not provided.
     * 
     */
    public function validateSortByRequest(array $allowedValues): array
    {
        if (isset($_GET['sort']) && !empty(trim($_GET['sort']))) {

            // Trims and sanatizates
            $sortBy = cleanString($_GET['sort']);

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
        } else {
            return ["table" => null, "cleanSortBy" => null];
        }
    }

    /**
     * Validates sorting and ordering values.
     * This method is used in methods for making queries for results listing. 
     * Provides table name for the WHERE clause in a query.
     * 
     * @param array $allowedValues An associative array of allowed values for ordering table rows.
     * @param ?string $sortBy The table name for sorting, defaults to null if not provided.
     * @return array An array containing validated 'table' and 'orderBy' values.
     * @throws DomainException If the provided $sortBy or $orderBy value is not in the allowed values.
     */
    public function validateSortingAndOrdering(
        array $allowedValues,
        ?string $sortBy = null
    ): array {
        // Checks if the $sortBy value is valid.
        $allowedSort = false;
        if ($sortBy === null || $sortBy === "all") {
            $allowedSort   = true;
            $data["table"] = null;
        } else {
            foreach ($allowedValues as $key => $value) {
                if (in_array($sortBy, $value)) {
                    $allowedSort   = true;
                    $data["table"] = $key;
                }
            }
        }

        // Throws an exception if either $sortBy or $orderBy is invalid.
        if ($allowedSort !== true) {
            throw new DomainException("Invalid sort value!");
        }

        return $data;
    }
}
