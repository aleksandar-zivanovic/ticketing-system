<?php

class BaseController
{
    /**
     * Validates an ID to ensure it is a positive integer.
     * 
     * @param int|string $id The ID to validate.
     * @return int|false The validated ID as an integer, or false if invalid.
     */
    public function validateId(int|string $id): int|false
    {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        if ($id === false) return false;
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id;
    }
}
