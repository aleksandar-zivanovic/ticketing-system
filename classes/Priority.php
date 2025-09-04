<?php
require_once 'BaseModel.php';

class Priority extends BaseModel
{
    /**
     * Fetches all data from `priorities` table.
     * 
     * @return array
     * @see @see BaseModel::getAll()
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
     * @see BaseModel::getAllByColumn()
     */
    public function getAllPriorityNames(): array
    {
        return $this->getAllByColumn("priorities", "name");
    }

    /**
     * Fetches all prioroty IDs.
     * 
     * @return array Integer list of prioroty IDs.
     * @see BaseModel::getAllByColumn()
     */
    public function getAllPriorotyIds(): array
    {
        return $this->getAllByColumn("priorities", "id");
    }
}
