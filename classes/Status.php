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

    /**
     * Counts tickets' statuses.
     *
     * Expects each ticket to contain a 'status_name' field with one of the expected values:
     * - "in progress"
     * - "closed"
     * - "waiting"
     *
     * Returns an array with the following structure:
     * [
     *     "in_progress" => int, 
     *     "closed"      => int, 
     *     "waiting"     => int,  
     *     "all":        => int, 
     * ]
     *
     * @param array $tickets Array of tickets with status data
     * @return array Associative array with status counts
     */
    public static function countStatuses(array $tickets): array
    {
        $counts = ['in_progress' => 0, 'closed' => 0, 'waiting' => 0, 'all' => count($tickets)];

        foreach ($tickets as $ticket => $values) {
            // Counts tickets with status `in progress` 
            if ($values["status_name"] === "in progress") {
                $counts['in_progress']++;
            }

            // Counts tickets with status `closed`
            if ($values["status_name"] === "closed") {
                $counts['closed']++;
            }

            // Counts tickets with status `closed`
            if ($values["status_name"] === "waiting") {
                $counts['waiting']++;
            }
        }

        return $counts;
    }
}
