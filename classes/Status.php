<?php
require_once('Database.php');

class Status
{
    private ?Database $dbInstance = null;

    private function getConn(): object
    {
        if ($this->dbInstance === null) {
            $this->dbInstance = new Database();
        }

        return $this->dbInstance;
    }

    public function getAllstatuses(): array
    {
        $query = "SELECT * FROM statuses";
        $stmt = $this->getConn()->connect()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllStatusNames(): array
    {
        $statusNames = [];

        foreach ($this->getAllstatuses() as $value) {
            $statusNames[] = $value['name'];
        }

        return $statusNames;
    }
}