<?php
require_once('BaseModel.php');

class Status extends BaseModel
{
    /**
     * Fetches all data from `statuses` table.
     * 
     * @return array
     */
    public function getAllstatuses(): array
    {
        return $this->getAll("statuses");
    }

    /**
     * Returns indexed array of all status names.
     * Example: ['in progres', 'waiting', 'closed', ...]
     * 
     * @return array List of status names.
     */
    public function getAllStatusNames(): array
    {
        return $this->getAllByColumn("statuses", "name");
    }
}
