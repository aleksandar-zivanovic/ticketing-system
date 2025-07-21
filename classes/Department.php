<?php
require_once 'BaseModel.php';

class Department extends BaseModel
{
    /**
     * Fetches all data from `departments` table.
     * 
     * @return array
     */
    public function getAllDepartments(): array
    {
        return $this->getAll("departments");
    }

    /**
     * Returns indexed array of all department names.
     * Example: ['Unassigned', 'Human Resources', 'Finance', ...]
     * 
     * @return array List of department names.
     */
    public function getAllDepartmentNames(): array
    {
        return $this->getAllNames("departments", "name");
    }
}