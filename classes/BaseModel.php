<?php
require_once('Database.php');

abstract class BaseModel
{
    private ?Database $dbInstance = null;

    /**
     * Gets connection with the database.
     */
    private function getConn(): object
    {
        if ($this->dbInstance === null) {
            $this->dbInstance = new Database();
        }

        return $this->dbInstance;
    }

    /**
     * Fetches all data for a certain table.
     * Returns multidimensional associative array.
     * 
     * @param string $table Database table name you are fetching data from.
     * 
     * @return array
     */
    public function getAll(string $table): array
    {
        $query = "SELECT * FROM {$table}";
        $stmt = $this->getConn()->connect()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns indexed array of the targeted column values.
     * Example: ['Unassigned', 'Human Resources', 'Finance', ...]
     * 
     * @param string $table Database table name.
     * @param string $column Column name used for data extraction.
     * 
     * @return array List of strings.
     */
    public function getAllNames(string $table, string $column): array
    {
        $names = [];
        foreach ($this->getAll($table) as $value) {
            $names[] = $value[$column];
        }

        return $names;
    }
}
