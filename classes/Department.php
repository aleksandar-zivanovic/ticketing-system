<?php
require_once('Database.php');

class Department
{
    private ?Database $dbInstance = null;

    private function getConn(): object
    {
        if ($this->dbInstance === null) {
            $this->dbInstance = new Database();
        }

        return $this->dbInstance;
    }

    public function getAllDepartments(): array
    {
        $query = "SELECT * FROM departments";
        $stmt = $this->getConn()->connect()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns indexed array of all department names.
     * Example: ['Unassigned', 'Human Resources', 'Finance', ...]
     * 
     * @return array Indexed list of department names.
     */
    public function getAllDepartmentNames(): array
    {
        $departmentNames = [];

        foreach ($this->getAllDepartments() as $value) {
            $departmentNames[] = $value['name'];
        }

        return $departmentNames;
    }
}