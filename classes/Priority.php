<?php
require_once 'BaseModel.php';

class Priority extends BaseModel
{
    /**
     * Fetches all data from `priorities` table.
     * 
     * @return array
     */
    public function getAllPriorities(): array
    {
        return $this->getAll("priorities");
    }

    /**
     * Returns indexed array of all priority names.
     * Example: ['low', 'medium', 'high', ...]
     * 
     * @return array List of priority names.
     */
    public function getAllPriorityNames(): array
    {
        return $this->getAllNames("priorities", "name");
    }
}