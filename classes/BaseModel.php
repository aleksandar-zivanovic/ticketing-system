<?php
require_once 'Database.php';

abstract class BaseModel
{
    private ?Database $dbInstance = null;
    protected ?PDO $pdo = null;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        }
    }

    /**
     * Gets connection with the database.
     */
    public function getConn(): PDO
    {
        if ($this->pdo === null) {
            if ($this->dbInstance === null) {
                $this->dbInstance = new Database();
            }
            $this->pdo = $this->dbInstance->connect();
        }

        return $this->pdo;
    }

    /**
     * Begins a database transaction.
     */
    public function beginTransaction(): void
    {
        $this->getConn()->beginTransaction();
    }

    /**
     * Commits a database transaction.
     */
    public function commitTransaction(): void
    {
        $this->getConn()->commit();
    }

    /**
     * Rolls back a database transaction.
     */
    public function rollBackTransaction(): void
    {
        $this->getConn()->rollBack();
    }

    /**
     * Fetches all data for a certain table.
     * Returns multidimensional associative array or empty array if no data found.
     * 
     * @param string $table Database table name you are fetching data from.
     * @throws RuntimeException if request failed.
     * @return array
     */
    public function getAll(string $table): array
    {
        try {
            $query = "SELECT * FROM {$table}";
            $stmt = $this->getConn()->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logError("BaseModel::getAll failed. Failed to fetch data from {$table} table. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Request failed. Try again.");
        }
    }

    /**
     * Fetches all data for a certain table for the WHERE condition.
     * Returns multidimensional associative array.
     * 
     * @param string $table Database table name you are fetching data from.
     * @param string $where content of WHERE clause (e.g. "parent_ticket = {$ticketId} AND statusId = 1")
     * 
     * @return array An associative array containing row details from the specified table or an empty array if not found.
     * @throws RuntimeException if request failed.
     */
    public function getAllWhere(string $table, string $where): array
    {
        $query = "SELECT * FROM {$table} WHERE {$where}";
        $stmt = $this->getConn()->prepare($query);
        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logError("BaseModel::getAllWhere failed. Failed to fetch data from {$table} table. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Request failed. Try again.");
        }
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
    public function getAllByColumn(string $table, string $column): array
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
        $records = $this->getAllByColumn($table, $column);

        return in_array($record, $records);
    }

    /**
     * Updates rows in the database. Update is done with transction.
     * 
     * @param string $tableName Table for update.
     * @param array  $columns Array of columns and values to update. Each sub-array must contain 
     * the same set of keys. Expects array formatted as: 
     *  [
     *      ["column1" => "value1", "column2" => "value2"], 
     *      ["column1" => "value3", "column2" => "value4"], 
     *  ]
     * @param array $whereClauses Each sub-array must have one or more key-value pairs 
     * representing column(s) and their match value(s), e.g.: [["id" => 5], ["statusId" => 3]]
     * 
     * @return void
     * @throws InvalidArgumentException if the number of rows and where values do not match,
     * or if unsupported parameter types are provided.
     * @throws RuntimeException if the update fails.
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
            throw new RuntimeException("BaseModel::updateRows. Failed to update {$tableName} table. " . $e->getMessage());
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
     * @throws InvalidArgumentException if key of $row arry is not supported.
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

    /**
     * Inserts a new row into the specified table.
     * 
     * @param string $tableName Name of the table to insert into.
     * @param array $data Associative array of column-value pairs to insert.
     * 
     * @return void
     * @throws RuntimeException if the insert fails.
     */
    public function insertRow(string $tableName, array $data): void
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $query = "INSERT INTO {$tableName} (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";

        try {
            $stmt = $this->getConn()->prepare($query);
            $this->bindValues($stmt, $data);
            $stmt->execute();
        } catch (\PDOException $e) {
            logError("BaseModel::insertRow. Insert into {$tableName} failed.", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Failed to insert into {$tableName} table. " . $e->getMessage());
        }
    }

    /**
     * Deletes a row from the specified table by ID.
     * 
     * @param string $table Name of the table to delete from.
     * @param int $id ID of the row to delete.
     * 
     * @return void
     * @throws RuntimeException if the delete fails.
     */
    protected function deleteRowById(string $table, int $id): void
    {
        try {
            $sql = "DELETE FROM {$table} WHERE id = :id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            logError("BaseModel::deleteRowById failed. Failed to delete record from {$table} table. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Request failed. Try again.");
        }
    }
}
