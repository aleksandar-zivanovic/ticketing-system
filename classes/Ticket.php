<?php
require_once 'BaseModel.php';

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

    /**
     * Fetches all data from priorities table
     * 
     * @return array Return associative array of priorities
     */
    public function getAllPriorities(): array
    {
        return $this->getAll("priorities");
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
            $query = "INSERT INTO tickets (department, created_by, priority, statusId, title, body, url) " .
                "VALUES(:de, :us, :pr, :st, :tt, :bd, :ul)";

            $stmt = $conn->prepare($query);
            $stmt->bindValue(":de", $this->departmentId, PDO::PARAM_INT);
            $stmt->bindValue(":us", $this->userId, PDO::PARAM_INT);
            $stmt->bindValue(":pr", $this->priorityId, PDO::PARAM_INT);
            $stmt->bindValue(":st", $this->statusId, PDO::PARAM_INT);
            $stmt->bindValue(":tt", $this->title, PDO::PARAM_STR);
            $stmt->bindValue(":bd", $this->description, PDO::PARAM_STR);
            $stmt->bindValue(":ul", $this->url, PDO::PARAM_STR);
            $stmt->execute();
            $ticketId = (int) $conn->lastInsertId();

            // Add the year in `years` table.
            $this->addCurrentYear();

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
     * Inserts a year in 'years' table.
     */
    private function addCurrentYear(): void
    {
        require_once 'Year.php';
        $yearInstance = new Year();
        $yearInstance->createYear(date("Y")); // Add the year in `years` table.
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
     * @param bool $handledByMe If true, fetches only tickets handled by the currently logged-in admin.
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
        ?int $userId = null,
        bool $handledByMe = false
    ): array {
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

            // Fetch only tickets handled by the current admin role user
            if ($handledByMe === true) {
                if (trim($_SESSION["user_role"]) !== "admin") {
                    logError("Error: Non admin users can't have tickets they handle!");
                    throw new Exception("User doesn't have permission for this action!");
                }

                if (isset($table) && $table !== null) {
                    $query .= " AND t.handled_by = " . trim($_SESSION["user_id"]);
                } else {
                    $query .= " WHERE t.handled_by = " . trim($_SESSION["user_id"]);
                }
            }

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
        ?int $userId = null,
        bool $handledByMe = false
    ): int {
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

            // Fetch only tickets handled by the current admin role user
            if ($handledByMe === true) {
                if (trim($_SESSION["user_role"]) !== "admin") {
                    logError("Error: Non admin users can't have tickets they handle!");
                    throw new Exception("User doesn't have permission for this action!");
                }

                if (isset($table) && $table !== null) {
                    $query .= " AND t.handled_by = " . trim($_SESSION["user_id"]);
                } else {
                    $query .= " WHERE t.handled_by = " . trim($_SESSION["user_id"]);
                }
            }

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
     * This method builds and executes a SQL query to retrieve a ticket data.
     *
     * @param int $ticketId A ticket id.
     * @return array The result contains ticket information, including optional image attachments.
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
    ): string|null {
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
            $curentDate = date("Y-m-d H:i:s");
            $curentDateSql = "'{$curentDate}'";
            $statusId = 3;
        }

        if ($action === "reopen") {
            $curentDateSql = "NULL";
            $statusId = 2;
        }

        try {
            $sql = "UPDATE tickets SET statusId = :si, closed_date = {$curentDateSql} WHERE id = :tid";
            $stmt = $this->getConn()->connect()->prepare($sql);
            $stmt->bindValue(":si", $statusId, PDO::PARAM_INT);
            $stmt->bindValue(":tid", $ticketId, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() === 1) {
                if ($action === "close") {
                    // Add the year in `years` table.
                    $this->addCurrentYear();
                }
                return true;
            }
            return false;
        } catch (\PDOException $e) {
            // Logs the error and throws an exception if a PDOException occurs.
            logError(
                "closeReopenTicket() metod error: Failed to {$action} the ticket",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new Exception("Something went wrong. Try again to {$action} ticket.");
        }
    }

    /**
     * Delete a ticket from database. 
     * If the ticket ID is int or string it will be converted to array type.
     * 
     * @param int|string|array $ticketId Ticket ID(s) for delation.
     */
    public function deleteTicketRow($id): bool
    {
        // Convert string to array type.
        if (is_string($id)) $id = explode(",", $id);

        // Convert integer to array type.
        if (is_int($id)) $id = [$id];

        require_once "helpers/IdValidator.php";
        list($ids, $params) = IdValidator::prepareIdsForQuery($id);

        try {
            $sql = "DELETE FROM tickets WHERE id IN (" . implode(",", $params) . ")";
            $stmt = $this->getConn()->connect()->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($value, $ids[$key], PDO::PARAM_INT);
            }

            return $stmt->execute();
        } catch (\PDOException $e) {
            // Logs the error and throws an exception if a PDOException occurs.
            logError(
                "deleteTicketRow() metod error: Failed to delete the ticket from the database",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new Exception("Something went wrong with deleting the ticket. Try again.");
        }
    }

    /**
     * Deletes the attachment(s) from the server and the database.
     * 
     * @param int $id ID of the ticket whose attachment(s) should be deleted.
     * @return bool Returns true on success. Throws an exception on failure.
     */
    public function deleteTicket(int $id): bool
    {
        // Get ticket data.
        $ticket = $this->fetchTicketDetails($id);

        // Validate user's deletion premission. 
        if (
            $ticket["statusId"] !== 1 &&
            $ticket["handled_by"] != null &&
            !empty($allMessages) &&
            ($ticket["created_by"] !== trim($_SESSION['user_id']) && trim($_SESSION["user_role"] !== "admin"))
        ) {
            $panel = $ticket["created_by"] !== trim($_SESSION['user_id']) && trim($_SESSION["user_role"] === "admin") ? "admin" : "user";
            $redirectionUrl = $panel === "admin" ? "../public/admin/admin-ticket-listing.php" : "../public/user/user-ticket-listing.php";
            die(header("Location: {$redirectionUrl}"));
        }

        // Delete attachments from the database and the server.
        if (!empty($ticket["attachment_id"])) {
            require_once 'Attachment.php';
            $attachment = new Attachment();

            // Convert string of IDs to an array of IDs.
            $idsArray = explode(",", $ticket["attachment_id"]);

            $attachments = $attachment->getAttachmentsByIds($idsArray, "ticket_attachments");

            // Get attachment names for deleteAttachmentsFromServer() method.
            $attachmentNames = [];
            foreach ($attachments as $anAttachment) {
                $attachmentNames[] = $anAttachment["file_name"];
            }

            // Collect data about existing and missing files.
            $attachmentFilesStatus = $attachment->isAttachmentExisting($attachmentNames);

            if (!empty($attachmentFilesStatus["exist"])) {
                // Delete attachments from the server.
                $attachment->deleteAttachmentsFromServer($attachmentNames);
            }

            // Delete attachments from the database.
            if ($attachment->deleteAttachmentsFromDbById($idsArray, "ticket_attachments") === false) {
                throw new RuntimeException("Deleting attachments from the database failed");
            };
        }

        // Delete the ticket from the database.
        return $this->deleteTicketRow($id);
    }

    /**
     * Set an admin as the ticket handler.
     * This method allows an admin to take the administration over the ticket.
     * 
     * @param int $ticketId ID of the ticket that will be assigned to an admin.
     * @return bool True on success, otherwise throws an exception.
     * @throws Exception If the assignment fails.
     */
    public function takeTicket(int $ticketId): bool
    {
        $adminId = trim($_SESSION["user_role"]) === "admin" ? trim($_SESSION["user_id"]) : false;
        if ($adminId === false) die(header("Location: ../user/user-ticket-listing.php"));

        try {
            $sql = "UPDATE tickets SET handled_by = :adm, statusId = 2 WHERE id = {$ticketId}";
            $stmt = $this->getConn()->connect()->prepare($sql);
            $stmt->bindValue(":adm", $adminId, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (\PDOException $e) {
            logError(
                "takeTicket() metod error: Failed to assign the ticket (ID: {$ticketId}) the administrator (ID: {$adminId}).",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new Exception("Something went wrong. The ticket is not assigned to the administrator.");
        }
    }

    /**
     * Gets an array of tickets filtered by a given parameter and year, grouped by months.
     * 
     * Returns an array formatted like: 
     * [
     *    ["Jan" => [
     *        "parameter_name" => array  // Contains values of any type (int, string, bool, null, etc.) 
     *    ],
     *    ["Feb" => [
     *        "parameter_name" => array  // Contains values of any type (int, string, bool, null, etc.)  
     *    ],
     *    // ... rest of the months
     * ]
     * 
     * @param string $param Parameter name that exists as a key in each ticket returned by the fetchAllTickets() method.
     * @param array $allTicketsData The array of all tickets returned by the `fetchAllTickets` method.
     * @param int $year The year to filter tickets by.
     * 
     * @return array Array with month abbreviations as keys.
     *     Each month key maps to an array of values of mixed types (int, string, bool, null)
     *     corresponding to the specified parameter.
     */
    public static function getMonthlyTicketsByParameter(string $param, array $allTicketsData, int $year): array
    {
        $months = [
            'Jan' => '01',
            'Feb' => '02',
            'Mar' => '03',
            'Apr' => '04',
            'May' => '05',
            'Jun' => '06',
            'Jul' => '07',
            'Avg' => '08',
            'Sep' => '09',
            'Oct' => '10',
            'Nov' => '11',
            'Dec' => '12',
        ];

        // Prepares empty array buckets to prevent undefined keys
        $monthsData = [];
        foreach ($months as $monthName => $_) {
            $monthsData[$monthName] = [];
        }

        // Fills buckets with tickets grouped by month
        foreach ($allTicketsData as $ticket) {
            foreach ($months as $monthName => $MonthNumber) {
                if (str_contains(haystack: $ticket[$param], needle: "{$year}-{$MonthNumber}-")) {
                    $monthsData[$monthName][$param][] = $ticket;
                    break; // stop looping months when matched
                }
            }
        }

        return $monthsData;
    }

    /**
     * Counts tickets received from `getMonthlyTicketsByParameter` and groupes them by months.
     * Returns an array formatted like: 
     * [
     *    "Jan" => [
     *        "parameter_name" => int
     *    ],
     *    "Feb" => [
     *        "parameter_name" => int 
     *    ],
     *    // ... rest of the months
     * ]
     * 
     * @param string $param Parameter name that exists as a key in each ticket returned by the `fetchAllTickets` method.
     * @param array $allTicketsData The array of all tickets returned by the `fetchAllTickets` method.
     * @param int $year The year to filter tickets by.
     * 
     * @return array Array with month abbreviations as keys.
     *     Each month key maps specified parameter name as a key and integer as value.
     */
    public static function countMonthlyTicketsByParameter(string $param, array $allTicketsData, int $year): array
    {
        $counts = [];
        $tickets = static::getMonthlyTicketsByParameter($param, $allTicketsData, $year);
        foreach ($tickets as $month => $arraysByParamNames) {
            if (empty($arraysByParamNames)) {
                $counts[$month][$param] = 0;
            }
            foreach ($arraysByParamNames as $tickets) {
                $counts[$month][$param] = count($tickets);
            }
        }

        return $counts;
    }

    /**
     * Counts data by a filter for a dashboard card table.
     * Returns array in format: 
     *  [
     *      ["FilterNameValue1", int], ["FilterNameValue2", int], ["FilterNameValue3", int], ...
     *  ] 
     * 
     * @param array $data Array of that returned by `fetchAllTickets` metod.
     * @param array $filters List of filter name strings.
     * @param string $ticketFilter Key of a single ticket array from $data, that you want to sort all tickets by.
     * 
     * @return array Array of array pairs of ticketFilter names and ticket counts for every ticketFilter name.
     */
    public static function countDataForDashboardTable(array $data, array $filters, string $ticketFilter): array
    {
        $ticketsByFilters = [];
        // Prepares array of tickets sorted by appropriate filters
        foreach ($data as $ticket) {
            foreach ($filters as $filterName) {
                if (str_contains(haystack: $ticket[$ticketFilter], needle: $filterName)) {
                    $ticketsByFilters[$filterName][] = $ticket;
                    break; // stop looping filters when matched
                }
            }
        }

        $countTicketsByFilters = [];
        for ($i = 0; $i < count($filters); $i++) {
            foreach ($ticketsByFilters as $name => $_) {
                if (str_contains(haystack: $name, needle: $filters[$i])) {
                    $countTicketsByFilters[] = [ucfirst($filters[$i]), count($ticketsByFilters[$filters[$i]])];
                    break;
                }
            }
        }

        return $countTicketsByFilters;
    }
}
