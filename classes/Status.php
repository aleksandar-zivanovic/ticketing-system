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
        return $this->getAllNames("statuses", "name");
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
