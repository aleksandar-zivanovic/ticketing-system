<?php
require_once ROOT . 'classes' . DS . 'BaseModel.php';

class Ticket extends BaseModel
{
    public string $title;
    public string $description;
    public string $url;
    public int $departmentId;
    public int $priorityId;
    public int $statusId;
    public int $userId;
    public ?array $images;
    public array $closingTypes = [
        "normal",    // Ticket was resolved and closed in the usual way.
        "abandoned", // Ticket was closed automatically due to no response in a set period of time.
        "canceled",  // Ticket was closed because the user or admin decided it’s no longer needed.
        "invalid",   // Ticket was closed because it was not valid (e.g. wrong issue, mistake).
        "duplicate", // Ticket was closed because the same issue exists in another ticket (merged or linked).
        "spam",      // Ticket was closed because it was spam or irrelevant. 
        "split",     // Ticket was closed because it was split into new tickets. 
    ];

    /**
     * Fetches all data from priorities table
     * 
     * @return array Return associative array of priorities
     * @throws RuntimeException if request failed.
     * @see BaseModel::getAll()
     */
    public function getAllPriorities(): array
    {
        return $this->getAll("priorities");
    }

    /**
     * Fetches all data from departments table
     * 
     * @return array Return associative array of departments
     * @throws RuntimeException if request failed.
     * @see BaseModel::getAll()
     */
    public function getAllDepartments(): array
    {
        return $this->getAll("departments");
    }

    /**
     * Creates a new ticket or mutliple new tickets.
     * 
     * @param ?array $data Associative array of ticket data or null. Default is null
     * @param bool $split If true used in splitting process, otherwise in creating a new ticket. Default is false
     * @param ?int $parentId ID of the parent ticket when splitting tickets, otherwise null. Default is null
     * @return int Returns the ID of the newly created ticket. Throws exception if the process fails.
     * 
     * @throws RuntimeException If the query execution fails.
     * @throws UnexpectedValueException If the table name is invalid.
     * @throws Exception Exception If there is an error in images upload.
     * @see Attachment::processImages()
     */
    public function createTicket(
        ?array $data,
        bool $split = false,
        ?int $parentId = null
    ): int {
        try {
            $data['statusId'] = 1;

            $conn = $this->getConn();

            $query = "INSERT INTO tickets (department, created_by, priority, statusId, title, body, url";
            if ($split === true) $query .= ", parent_ticket";
            $query .= ") VALUES(:de, :us, :pr, :st, :tt, :bd, :ul";
            $query .= $split === true ? ", :pi)" : ")";


            $stmt = $conn->prepare($query);
            $stmt->bindValue(":de", $data['departmentId'], PDO::PARAM_INT);
            $stmt->bindValue(":us", $data['userId'], PDO::PARAM_INT);
            $stmt->bindValue(":pr", $data['priorityId'], PDO::PARAM_INT);
            $stmt->bindValue(":st", $data['statusId'], PDO::PARAM_INT);
            $stmt->bindValue(":tt", $data['title'], PDO::PARAM_STR);
            $stmt->bindValue(":bd", $data['description'], PDO::PARAM_STR);
            $stmt->bindValue(":ul", $data['url'], PDO::PARAM_STR);
            if ($split === true) $stmt->bindValue(":pi", $parentId, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $conn->lastInsertId();
        } catch (\PDOException $e) {
            logError("createTicket error: INSERT query failed!", ["message" => $e->getMessage(), "code" => $e->getCode()]);
            throw new \RuntimeException("createTicket method query execution failed");
        }
    }

    /**
     * Fetches ticket data and associated details from related tables.
     *
     * Builds and executes a SQL query with optional filtering, sorting, 
     * and inclusion of attachments.
     *
     * @param string  $action Action type for the listing (e.g., "all", "my", "handling")
     * @param int     $userId The ID of the user whose tickets are to be fetched.
     * @param string  $orderBy Order direction: "newest" (default) or "oldest".
     * @param ?string $sortBy Column value used for filtering, depends on $table.
     * @param ?string $table Table name for filtering ("statuses", "priorities", "departments", "users").
     * @param ?string $table The table name for sorting, defaults to null if not provided and will look in user table.
     * @param int  $limit Maximum number of tickets to fetch. 0 = no limit.
     * @param bool $images A flag to include image attachments in the result, default is true.
     * @return array The result set containing ticket information, including optional image attachments.
     * @throws RuntimeException If a database query fails.
     */
    public function fetchAllTickets(
        string $action,
        int $userId,
        int $currentPage,
        string $orderBy = "newest",
        ?string $sortBy = null,
        ?string $table = null,
        int $limit = 0,
        bool $images = true
    ): array {
        try {
            // Initial query to select ticket data and associated table names for join.
            $query = "SELECT 
                        t.*, 
                        d.name AS department_name, 
                        p.name AS priority_name, 
                        s.name AS status_name, 
                        u.name AS admin_name, 
                        u.surname AS admin_surname
                    ";

            // If $images = TRUE attachments will be included in result.
            if ($images) {
                $query .= " , GROUP_CONCAT(ta.id) AS attachment_id, 
                            GROUP_CONCAT(ta.file_name) AS file, 
                            ta.ticket AS from_ticket
                        ";

                $queryJoin = " LEFT JOIN ticket_attachments ta on t.id = ta.ticket";
            }

            $query .= " FROM tickets t";

            // Adding joins for departments, priorities, statuses and users
            $query .= " LEFT JOIN departments d ON t.department = d.id 
                        LEFT JOIN priorities p ON t.priority = p.id 
                        LEFT JOIN statuses s ON t.statusId = s.id 
                        LEFT JOIN users u ON t.handled_by = u.id 
                    ";

            // If $images is true, includes the join for attachments table
            if ($images) $query .= $queryJoin;

            // Sets WHERE clause
            if (isset($table) && $table !== null) {
                switch ($table) {
                    case 'statuses':
                        $tableAllias = "s";
                        break;
                    case 'priorities':
                        $tableAllias = "p";
                        break;
                    case 'departments':
                        $tableAllias = "d";
                        break;
                    default:
                        $tableAllias = "u";
                }

                $column = (in_array($tableAllias, ["s", "p", "d"])) ? "name" : "id";
                $query .= " WHERE " . $tableAllias . "." . $column . " = '" . $sortBy . "'";
            }

            // Fetches only tickets created by the user
            if ($action === "my") {
                if (!isset($table) || $table == null) $query .= " WHERE t.created_by = " . $userId;
            }

            // Fetches only tickets handled by the current admin role user
            if ($action === "handling") {
                if (isset($table) && $table !== null) {
                    $query .= " AND t.handled_by = " . $userId;
                } else {
                    $query .= " WHERE t.handled_by = " . $userId;
                }
            }

            // Adds GROUP BY clause to group results by ticket ID
            $query .= " GROUP BY t.id";

            // Determines the ordering based on the value of $orderBy
            $queryOrder = $orderBy === "oldest" ? " ORDER BY t.id ASC" : " ORDER BY t.id DESC";
            if ($queryOrder) $query .= $queryOrder;

            // Setting limit and offset value
            if ($limit !== 0) {
                $offset = $currentPage * $limit - $limit;
                $query .= " LIMIT :limit OFFSET {$offset}";
            }

            // Prepares and executes the SQL query
            $stmt = $this->getConn()->prepare($query);

            // Binds the value of limit to the query if it is greater than 0
            if ($limit !== 0) $stmt->bindValue("limit", $limit, PDO::PARAM_INT);

            $stmt->execute();

            // Returns the fetched result set
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Logs the error and throws RuntimeException if a PDOException occurs
            logError("Ticket::fetchAllTickets() error!", ["message" => $e->getMessage(), "code" => $e->getCode()]);
            throw new \RuntimeException("Something went wrong. Try again later!");
        }
    }

    /**
     * Counts total tickets based on optional filtering criteria.
     * 
     * @param string  $action Action type for the listing (e.g., "all", "my", "handling")
     * @param int     $userId The ID of the user whose tickets are to be counted.
     * @param ?string $sortBy Column value used for filtering, depends on $table.
     * @param ?string $table Table name for filtering ("statuses", "priorities", "departments", "users").
     * 
     * @return int The total number of tickets matching the specified criteria.
     * @throws Exception If user doesn't have permission for this action.
     * @throws RuntimeException If a database query fails.
     */
    public function countAllTickets(
        string $action,
        int $userId,
        ?string $sortBy = null,
        ?string $table = null,
    ): int {
        try {
            // Initial query to select ticket data and associated table names for join.
            $query = "SELECT COUNT(*) FROM tickets t LEFT JOIN users u ON t.handled_by = u.id";

            // Sets WHERE clause
            if (isset($table) && $table !== null) {
                switch ($table) {
                    case 'statuses':
                        $tableAllias = "s";
                        $query .= " LEFT JOIN statuses s ON t.statusId = s.id";
                        break;
                    case 'priorities':
                        $tableAllias = "p";
                        $query .= " LEFT JOIN priorities p ON t.priority = p.id";
                        break;
                    case 'departments':
                        $tableAllias = "d";
                        $query .= " LEFT JOIN departments d ON t.department = d.id";
                        break;
                    default:
                        $tableAllias = "u";
                }

                $column = (in_array($tableAllias, ["s", "p", "d"])) ? "name" : "id";
                $query .= " WHERE " . $tableAllias . "." . $column . " = '" . $sortBy . "'";
            }

            if ($action === "my") {
                if (isset($table) && $table !== null) {
                    $query .= " AND t.created_by = " . $userId;
                }

                // Fetches only tickets opened by a specified user $table is not specified
                if (!isset($table) || $table == null) $query .= " WHERE t.created_by = " . $userId;
            }

            // Fetch only tickets handled by the current admin role user
            if ($action === "handling") {
                if (isset($table) && $table !== null) {
                    $query .= " AND t.handled_by = " . $userId;
                } else {
                    $query .= " WHERE t.handled_by = " . $userId;
                }
            }

            // Prepares and executes the SQL query
            $stmt = $this->getConn()->prepare($query);
            $stmt->execute();

            // Returns the fetched result set
            return $stmt->fetchColumn();
        } catch (\PDOException $e) {
            // Logs the error and throws an exception if a PDOException occurs
            logError("Ticket::countAllTickets() error!", ["message" => $e->getMessage(), "code" => $e->getCode()]);
            throw new RuntimeException("Something went wrong. Try again later!");
        }
    }

    /**
     * Fetches row from `tickets` table by ID.
     * 
     * @param int $ticketId Ticket ID.
     * @return array|false Returns associative array of ticket data or false if ticket is not found.
     * @throws RuntimeException if request failed.
     * @see BaseModel::getAllWhere()
     */
    public function fetchTicketById(int $ticketId): array|false
    {
        return $this->getAllWhere("tickets", "id = {$ticketId}")[0] ?? false;
    }

    /**
     * Fetches ticket data and associated details from related tables.
     * This method builds and executes a SQL query to retrieve a ticket data.
     *
     * @param int $ticketId A ticket id.
     * @return array|false The result set containing ticket information, including optional image attachments, or false if ticket is not found.
     * @throws RuntimeException If there is a PDOException while executing the SQL query.
     */
    public function fetchTicketDetails(int $ticketId): array|false
    {
        try {
            // Initial query to select ticket data and associated table names for join.
            $query = "SELECT 
                        t.*, 
                        d.name AS department_name, 
                        p.name AS priority_name, 
                        s.name AS status_name, 
                        u1.name AS admin_name, 
                        u1.surname AS admin_surname, 
                        u2.name AS creator_name, 
                        u2.surname AS creator_surname,
                        GROUP_CONCAT(ta.id) AS attachment_id, 
                        GROUP_CONCAT(ta.file_name) AS file, 
                        ta.ticket AS from_ticket 
                    FROM tickets t 
                    LEFT JOIN ticket_attachments ta on t.id = ta.ticket 
                    LEFT JOIN departments d ON t.department = d.id 
                    LEFT JOIN priorities p ON t.priority = p.id 
                    LEFT JOIN statuses s ON t.statusId = s.id 
                    LEFT JOIN users u1 ON t.handled_by = u1.id 
                    LEFT JOIN users u2 ON t.created_by = u2.id 
                    WHERE t.id = :ticket";

            // Prepares and executes the SQL query
            $stmt = $this->getConn()->prepare($query);

            // Binds the value of limit to the query if it is greater than 0
            $stmt->bindValue("ticket", $ticketId, PDO::PARAM_INT);

            $stmt->execute();

            // Returns the fetched result set
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Logs the error and throws an exception if a PDOException occurs
            logError("fetchTicketDetails() method error: Failed to retrive the ticket data", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Something went wrong. Try again later!");
        }
    }

    /**
     * Sets ticket status to "closed" or "in progress".
     * Creates a log entry and throws an exception if the process fails.
     * 
     * @param int $ticketId ID of the ticket that should be closed.
     * @param string $action Determines if a ticket should be closed or reopened. Allowed values are "close" and "reopen"
     * @return bool Returns true if the process was successful, otherwise throws RuntimeException.
     * @throws RuntimeException If there is a PDOException while executing the SQL query.
     */
    public function closeReopenTicket(int $ticketId, string $action): bool
    {
        try {
            $sql = "UPDATE tickets SET statusId = :si, ";

            if ($action === "close") {
                $curentDate = date("Y-m-d H:i:s");
                $curentDateSql = "'{$curentDate}'";
                $statusId = 3;
                $closingType = cleanString($_POST['closingSelect']);

                $sql .= "closing_type = :ct, ";
            }

            if ($action === "reopen") {
                $curentDateSql = "NULL";
                $statusId = 2;
                $sql .= "closing_type = NULL, was_reopened = TRUE, ";
            }

            $sql .= "closed_date = {$curentDateSql} WHERE id = :tid";

            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(":si", $statusId, PDO::PARAM_INT);
            if ($action === "close") $stmt->bindValue(":ct", $closingType, PDO::PARAM_STR);
            $stmt->bindValue(":tid", $ticketId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() === 1;
        } catch (\PDOException $e) {
            // Logs the error and throws an exception if a PDOException occurs.
            logError(
                "closeReopenTicket() method error: Failed to {$action} the ticket",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new RuntimeException("Something went wrong. Try again to {$action} the ticket.");
        }
    }

    /**
     * Deletes one or multiple tickets from the database.
     * Accepts ticket ID(s) as int, string (comma-separated), or array.
     * 
     * @param int|string|array $id Ticket ID(s) for deletion.
     * @return void
     * @throws Exception If database deletion fails
     */
    public function deleteTicketRow(array $ids, array $params): void
    {
        try {
            $sql = "DELETE FROM tickets WHERE id IN (" . implode(",", $params) . ")";
            $stmt = $this->getConn()->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($value, $ids[$key], PDO::PARAM_INT);
            }

            $stmt->execute();
        } catch (\PDOException $e) {
            // Logs the error and throws an exception if a PDOException occurs.
            logError(
                "deleteTicketRow() method error: Failed to delete the ticket from the database",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new Exception("Something went wrong with deleting the ticket. Try again.");
        }
    }

    /**
     * Set an admin as the ticket handler.
     * This method allows an admin to take the administration over the ticket.
     * 
     * @param int $ticketId ID of the ticket that will be assigned to an admin.
     * @param int $adminId ID of the admin who is taking the ticket.
     * @return void
     * @throws RuntimeException If there is a PDOException while executing the SQL query.
     */
    public function takeTicket(int $ticketId, int $adminId): void
    {
        try {
            $sql = "UPDATE tickets SET handled_by = :adm, statusId = 2 WHERE id = {$ticketId}";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(":adm", $adminId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            logError(
                "takeTicket() method error: Failed to assign the ticket (ID: {$ticketId}) the administrator (ID: {$adminId}).",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new RuntimeException("Something went wrong. The ticket is not assigned to the administrator.");
        }
    }

    /**
     * Updates ticket data in the database (wrapper for "tickets" table).
     * 
     * @param array $columns An array of associative arrays, each containing column-value pairs to update.
     * @param array $whereClauses An array of associative arrays, each containing column-value pairs for the WHERE clause.
     * @throws InvalidArgumentException if the number of rows and where values do not match,
     * or if unsupported parameter types are provided.
     * @throws RuntimeException if the update fails.
     * @see BaseModel::updateRows()
     */
    public function updateTicket(array $columns, array $whereClauses): void
    {
        $this->updateRows("tickets", $columns, $whereClauses);
    }

    /**
     * Checks if the ticket has children tickets.
     * 
     * @param int $parentId Id of ticket whose children is lookng for.
     * @return bool True if there are children, otherwise false.
     * @throws RuntimeException if the query execution fails.
     */
    public function hasChildren(int $parentId): bool
    {
        try {
            $sql = "SELECT COUNT(id) FROM tickets WHERE parent_ticket = :pt";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(":pt", $parentId, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            return $count > 0;
        } catch (\PDOException $e) {
            logError(
                "Ticket::hasChildren() method error: Query failed when checking for child tickets of parent ticket ID: {$parentId}",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new RuntimeException("Something went wrong. Try again later.");
        }
    }
}
