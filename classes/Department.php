<?php
require_once 'BaseModel.php';

class Department extends BaseModel
{
    /**
     * Fetches all data from `departments` table.
     * 
     * @return array
     * @see BaseModel::getAll()
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
     * @see BaseModel::getAllByColumn()
     */
    public function getAllDepartmentNames(): array
    {
        return $this->getAllByColumn("departments", "name");
    }

    /**
     * Fetches all department IDs.
     * 
     * @return array Integer list of department IDs.
     * @see BaseModel::getAllByColumn()
     */
    public function getAllDepartmentIds(): array
    {
        return $this->getAllByColumn("departments", "id");
    }
}
