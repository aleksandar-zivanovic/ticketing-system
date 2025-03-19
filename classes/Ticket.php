<?php
require_once 'Database.php';

class Ticket
{
    private ?Database $dbInstance = null;
    public string $title;
    public string $description;
    public string $url;
    public string|int $day;
    public string|int $month;
    public string|int $year;
    public int $departmentId;
    public int $priorityId;
    public int $statusId;
    public int $userId;
    public ?array $images;

    /**
     * Sets connection with the database
     */
    private function getConn(): object
    {
        if ($this->dbInstance === null) {
            $this->dbInstance = new Database();
        }

        return $this->dbInstance;
    }

    /**
     * Fetches all data from priorities table
     * 
     * @return array Return associative array of priorities
     */
    public function getAllPriorities(): array
    {
        $query = "SELECT * FROM priorities";
        $stmt = $this->getConn()->connect()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Collects and sanitizes data from the form for creating a new ticket.
     */
    public function collectTicketData(): void 
    {
        // Validates the URL from the form input.
        $url = cleanString(filter_input(INPUT_POST, "error_page", FILTER_SANITIZE_URL));
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('URL is not valid!');
        }

        $this->url = $url;
        $this->title = cleanString(filter_input(INPUT_POST, "error_title", FILTER_DEFAULT));
        $this->description = cleanString(filter_input(INPUT_POST, "error_description", FILTER_DEFAULT));
        $this->day = date("d");
        $this->month = date("m");
        $this->year = date("Y");
        $this->departmentId = cleanString(filter_input(INPUT_POST, "department", FILTER_SANITIZE_NUMBER_INT));
        $this->priorityId = cleanString(filter_input(INPUT_POST, "priority", FILTER_SANITIZE_NUMBER_INT));
        $this->statusId = 1;
        $this->userId = cleanString($_SESSION["user_id"]);
    }

    /**
     * Creates a new ticket
     */
    public function createTicket(): void
    {
        $this->collectTicketData();
        $conn = $this->getConn()->connect();

        try {
            $query = "INSERT INTO tickets (created_year, created_month, created_day, department, created_by, priority, statusId, title, body, url) " .
            "VALUES(:cy, :cm, :cd, :de, :us, :pr, :st, :tt, :bd, :ul)";

            $stmt = $conn->prepare($query);
            $stmt->bindValue(":cy", $this->year, PDO::PARAM_INT);
            $stmt->bindValue(":cm", $this->month, PDO::PARAM_INT);
            $stmt->bindValue(":cd", $this->day, PDO::PARAM_INT);
            $stmt->bindValue(":de", $this->departmentId, PDO::PARAM_INT);
            $stmt->bindValue(":us", $this->userId, PDO::PARAM_INT);
            $stmt->bindValue(":pr", $this->priorityId, PDO::PARAM_INT);
            $stmt->bindValue(":st", $this->statusId, PDO::PARAM_INT);
            $stmt->bindValue(":tt", $this->title, PDO::PARAM_STR);
            $stmt->bindValue(":bd", $this->description, PDO::PARAM_STR);
            $stmt->bindValue(":ul", $this->url, PDO::PARAM_STR);
            $stmt->execute();
            $ticketId = (int) $conn->lastInsertId();

            // Proccesses files if they are attached in form:
            if ($_FILES['error_images']['error'][0] != 4) {
                require_once 'Attachment.php';
                $attachment = new Attachment();
                $attachment->processImages($ticketId, "ticket_attachments", "error_images");
            }

            $_SESSION["info_message"] = "The issue is reported! Thank you!";

            // Redirects the user to the reported page after the successful ticket creation.
            header("Location: {$this->url}");
        } catch (\PDOException $e) {
            logError("createTicket error: INSERT query failed!", ["message" => $e->getMessage(), "code" => $e->getCode()]);

            throw new \RuntimeException("createTicket method query execution failed");
        }
    }

    /**
     * Fetches ticket data and associated details from related tables.
     *
     * This method builds and executes a SQL query to retrieve ticket data.
     *
     * @param array $allowedValues An array of allowed values for ordering tickets.
     * @param string $orderBy The value by which to order the tickets, default is "newest".
     * @param ?string $sortBy The table name for sorting, defaults to null if not provided.
     * @param int $limit Value for LIMIT clause in the SQL query. If 0, no limit is applied. Default value is 0.
     * @param bool $images A flag to include image attachments in the result, default is true.
     * @param ?int $userId The ID of the user whose tickets are to be fetched. If `null`, all tickets will be fetched (default).
     * 
     * @return array The result set containing ticket information, including optional image attachments.
     * 
     * @throws DomainException If the provided $sortBy or $orderBy value is not in the allowed values.
     * @throws Exception If there is a PDOException while executing the SQL query.
     */
    public function fetchAllTickets(
        array $allowedValues, 
        string $orderBy = "newest", 
        ?string $sortBy = null, 
        int $limit = 0, 
        bool $images = true, 
        ?int $userId = null
    ): array
    {
        // Validates sorting and ordering values and sets $table value.
        $table = $this->validateSortingAndOrdering($allowedValues, $orderBy, $sortBy);

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
                    default: 
                        $tableAllias = "u";
                }

                $column = (in_array($tableAllias, ["s", "p", "d"])) ? "name" : "id";
                $query .= " WHERE " . $tableAllias . "." . $column . " = '" . $sortBy . "'";

                // Fetches only tickets opened by a specified user if $table is specified
                if ($userId != null) $query .= " AND t.created_by = " . $userId;
            }

            // Fetches only tickets opened by a specified user $table is not specified
            if ($userId != null && (!isset($table) || $table == null)) $query .= " WHERE t.created_by = " . $userId;

            // Adds GROUP BY clause to group results by ticket ID
            $query .= " GROUP BY t.id";

            // Determines the ordering based on the value of $orderBy
            $queryOrder = $orderBy === "oldest" ? " ORDER BY t.id ASC" : " ORDER BY t.id DESC";
            if ($queryOrder) $query .= $queryOrder;

            // Setting limit and offset value
            if ($limit !== 0) {
                $page = isset($_GET['page']) ? filter_input(INPUT_GET, "page", FILTER_SANITIZE_NUMBER_INT) : 1;
                $offset = $page * $limit - $limit;

                $query .= " LIMIT :limit OFFSET {$offset}";
            }

            // Prepares and executes the SQL query
            $stmt = $this->getConn()->connect()->prepare($query);

            // Binds the value of limit to the query if it is greater than 0
            if ($limit !== 0) $stmt->bindValue("limit", $limit, PDO::PARAM_INT);

            $stmt->execute();

            // Returns the fetched result set
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Logs the error and throws an exception if a PDOException occurs
            logError($e->getMessage() . $e->getCode());
            throw new Exception($e->getMessage() . $e->getCode());
        }
    }

    /**
     * Counts all tickets in the database by criteria.
     */
    public function countAllTickets(
        array $allowedValues, 
        string $orderBy = "newest", 
        ?string $sortBy = null, 
        ?int $userId = null
    ): int
    {
        // Validates sorting and ordering values and sets $table value.
        $table = $this->validateSortingAndOrdering($allowedValues, $orderBy, $sortBy);

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
                // Fetches only tickets opened by a specified user if $table is specified
                if ($userId != null) $query .= " AND t.created_by = " . $userId;
            }

            // Fetches only tickets opened by a specified user $table is not specified
            if ($userId != null && (!isset($table) || $table == null)) $query .= " WHERE t.created_by = " . $userId;

            // Prepares and executes the SQL query
            $stmt = $this->getConn()->connect()->prepare($query);
            $stmt->execute();
            
            // Returns the fetched result set
            return $stmt->fetchColumn();
        } catch (\PDOException $e) {
            // Logs the error and throws an exception if a PDOException occurs
            logError($e->getMessage() . $e->getCode());
            throw new Exception($e->getMessage() . $e->getCode());
        }
    }

    /**
     * Fetches ticket data and associated details from related tables.
     *
     * This method builds and executes a SQL query to retrieve a ticket data.
     *
     * @param int $ticketId A ticket id.
     * 
     * @return array The result contains ticket information, including optional image attachments.
     * 
     * @throws Exception If there is a PDOException while executing the SQL query.
     */
    public function fetchTicketDetails(int $ticketId): array
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
            $stmt = $this->getConn()->connect()->prepare($query);

            // Binds the value of limit to the query if it is greater than 0
            $stmt->bindValue("ticket", $ticketId, PDO::PARAM_INT);

            $stmt->execute();

            // Returns the fetched result set
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Logs the error and throws an exception if a PDOException occurs
            logError("fetchTicketDetails() metod error: Failed to retrive the ticket data", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new Exception("Something went wrong. Try again later!");
        }
    }

    /**
     * Validates sorting and ordiering values.  
     * This method is used in methods for making queries for ticket listings. 
     * Provides table name for the WHERE clause in a query.
     * 
     * @param array $allowedValues An associative array of allowed values for ordering tickets.
     * @param ?string $sortBy The table name for sorting, defaults to null if not provided.
     * @return string|null Returns table name or null if everything is valid, otherwise throws exception;
     * @throws DomainException If the provided $sortBy or $orderBy value is not in the allowed values.
     */
    private function validateSortingAndOrdering(
        array $allowedValues, 
        string $orderBy = "newest", 
        ?string $sortBy = null
    ): string|null
    {
        // Checks if the $sortBy value is valid.
        $allowedSort = false;
        if ($sortBy === null || $sortBy === "all") {
            $allowedSort = true;
            $table = null;
        } else {
            foreach ($allowedValues as $key => $value) {
                if (in_array($sortBy, $value)) {
                    $allowedSort = true;
                    $table = $key;
                }
            }
        }

        // Checks if the $orderBy value is valid.
        $allowedOrder = false;
        if ($orderBy === "newest" || $orderBy === "oldest") {
            $allowedOrder = true;
        }

        // Throws an exception if either $sortBy or $orderBy is invalid.
        if ($allowedSort !== true ||  $allowedOrder !== true) {
            throw new DomainException("Invalid order/sort value!");
        }

        return $table;
    }

    /**
     * Sets ticket status to "closed" or "in progress".
     * Creates a log entry and throws an exception if the process fails.
     * 
     * @param int $ticketId ID of the ticket should be closed.
     * @param string $action Detrmines if needs to close or reopen ticket. Allowed values are "close" and "reopen"
     * @return bool Returns true if the process was successful, otherwise returns false.
     */
    public function closeReopenTicket(int $ticketId, string $action): bool
    {
        // Verifies that the $action parameter contains a valid value ("close" or "reopen").
        if ($action !== "close" && $action !== "reopen") {
            throw new DomainException("The action parameter is invalid!");
        }

        if ($action === "close") {
            $this->day = date("d");
            $this->month = date("m");
            $this->year = date("Y");
            $curentDate = date("Y-m-d H:i:s");
            $curentDateSql = "'{$curentDate}'";
            $statusId = 3;
        }

        if ($action === "reopen") {
            $this->day = $this->month = $this->year = $curentDateSql = "NULL";
            $statusId = 2;
        }

        try {
            $sql = "UPDATE tickets SET statusId = :si, closed_date = {$curentDateSql}, closed_year = {$this->year}, closed_month = {$this->month}, closed_day = {$this->day} WHERE id = :tid";
            $stmt = $this->getConn()->connect()->prepare($sql);
            $stmt->bindValue(":si", $statusId, PDO::PARAM_INT);
            $stmt->bindValue(":tid", $ticketId, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->rowCount() === 1;
        } catch (\PDOException $e) {
            // Logs the error and throws an exception if a PDOException occurs.
            logError(
                "closeReopenTicket() metod error: Failed to {$action} the ticket", 
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new Exception("Something went wrong. Try again to {$action} ticket.");
        }
    }
}