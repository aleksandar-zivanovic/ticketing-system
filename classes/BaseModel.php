<?php
require_once 'Database.php';

abstract class BaseModel
{
    private ?Database $dbInstance = null;

    /**
     * Gets connection with the database.
     */
    protected function getConn(): PDO
    {
        if ($this->dbInstance === null) {
            $this->dbInstance = new Database();
        }

        return $this->dbInstance->connect();
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
        $stmt = $this->getConn()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches all data for a certain table for the WHERE condition.
     * Returns multidimensional associative array.
     * 
     * @param string $table Database table name you are fetching data from.
     * @param string $where content of WHERE clause.
     * 
     * @return array
     */
    public function getAllWhere(string $table, string $where): array
    {
        $query = "SELECT * FROM {$table} WHERE {$where}";
        $stmt = $this->getConn()->prepare($query);
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

    /**
     * Checks if a record exists in a table.
     * 
     * @param string $table Name of the table.
     * @param string $column Name of the table's column.
     * @param int|string $record Value of the record.
     * 
     * @return bool True if exists, false if doesn't exist
     */
    protected function checkTheRecordExists(string $table, string $column, int|string $record): bool
    {
        $records = $this->getAllNames($table, $column);

        return in_array($record, $records);
    }

    /**
     * Updates rows in the database. Update is done with tranasction.
     * 
     * @param string $tableName Table for update.
     * @param array  $columns Array of columns and values to update. Each sub-array must contain 
     * the same set of keys. Expects array formatted as: 
     *  [
     *      ["column1" => "value1", "column2" => "value2"], 
     *      ["column1" => "value3", "column2" => "value4"], 
     *  ]
     * @param array $whereClauses Each sub-array must have one or more key-value pairs 
     * representing column(s) and their match value(s), e.g.: [["id" => 5], ["id" => 6]]
     * 
     * @return void
     */
    public function updateRows(string $tableName, array $columns, array $whereClauses): void
    {
        if (count($columns) !== count($whereClauses)) {
            throw new InvalidArgumentException("The number of rows and where values must match.");;
        }

        $setClauses = [];
        foreach ($columns as $row) {
            foreach ($row as $key => $_) {
                $setClauses[] = "{$key} = :{$key}";
            }
            break;
        }

        $where = implode(", ", array_keys($whereClauses[0]));
        $query = "UPDATE {$tableName} SET " . implode(", ", $setClauses) . " WHERE {$where} = :{$where}";

        $conn = $this->getConn();

        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare($query);

            foreach ($columns as $key => $row) {
                $this->bindValues($stmt, $row);
                $this->bindValues($stmt, $whereClauses[$key]);
                $stmt->execute();
            }

            $conn->commit();
        } catch (\PDOException $e) {
            $conn->rollBack();
            logError("Update for {$tableName} failed. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Failed to update {$tableName} table " . $e->getMessage());
        }
    }

    /**
     * Binds values for the array and throws exception 
     * if the values are of unallowed types.
     * 
     * @param PDOStatement $stmt Statement
     * @param array $row Array of values for bindding. Expects flat associative array like: 
     *  ["column1" => "value1", "column2" => "value2"]
     * 
     * @return void
     */
    private function bindValues(PDOStatement $stmt, array $row): void
    {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $type = PDO::PARAM_STR;
            } elseif (is_int($value)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $type = PDO::PARAM_NULL;
            } else {
                throw new InvalidArgumentException("Unsupported parameter type for :$key");
            }

            $stmt->bindValue(":{$key}", $value, $type);
        }
    }
}
