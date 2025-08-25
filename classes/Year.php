<?php
require_once('BaseModel.php');

class Year extends BaseModel
{
    public int $year;

    /**
     * Fetches existing year from the database.
     * Returns indexed array of integers (e.g. [2005, 2006, ...])
     * 
     * @return array
     */
    public function getAllYears(): array
    {
        return $this->getAllByColumn("years", "year");
    }

    /**
     * Checks if the year exists in the table.
     * 
     * @param int $year
     * 
     * @return bool True if the year exists, false if doesn't exist
     */
    public function checkIfTheYearExists(int $year): bool
    {
        return $this->checkTheRecordExists("years", "year", $year);
    }

    /**
     * Creates year entry in `years` table if the year doesn't exist.
     * 
     * @param int $year The year to be added
     */
    public function createYear(int $year): void
    {
        try {
            $query = "INSERT INTO years (year) VALUES ($year)";
            $stmt = $this->getConn()->prepare($query);
            $stmt->execute();
        } catch (\PDOException $e) {
            logError(
                "createYear() metod error: Inserting a year failed!",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new RuntimeException("Error inserting a year!");
        }
    }
}
