<?php
require_once('Database.php');

class Priority
{
    private ?Database $dbInstance = null;

    private function getConn(): object
    {
        if ($this->dbInstance === null) {
            $this->dbInstance = new Database();
        }

        return $this->dbInstance;
    }

    public function getAllPriorities(): array
    {
        $query = "SELECT * FROM priorities";
        $stmt = $this->getConn()->connect()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPriorityNames(): array
    {
        $statusNames = [];
        
        foreach ($this->getAllPriorities() as $value) {
            $statusNames[] = $value['name'];
        }

        return $statusNames;
    }
}