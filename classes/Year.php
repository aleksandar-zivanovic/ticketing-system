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
        return $this->getAllNames("years", "year");
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
     * First checks if there is the year in the table 
     * and if there is not creates new row.
     * 
     * @param int $year
     */
    public function createYear(int $year): void
    {
        if ($this->checkIfTheYearExists($year) === false) {
            try {
                $query = "INSERT INTO years (year) VALUES ($year)";
                $stmt = $this->getConn()->connect()->prepare($query);
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
}
