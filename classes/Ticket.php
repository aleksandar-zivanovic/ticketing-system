<?php
require_once 'Database.php';

class Ticket
{
    private ?Database $dbInstance = null;
    public string $title;
    public string $description;
    public string $url;
    public int $day;
    public int $month;
    public int $year;
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
     * Processes images from the form.
     * Checks for errors, validates MIME types, and verifies allowed file extensions.
     * Creates the image folder if it doesn't exist.
     * Prepares adequate file names for images.
     * Prepares unique image names and sanitized them.
     * Inserts image names to the database.
     * Uploads images to the designated folder.
     * 
     * @return bool Returns true on succes otherwise false.
     */
    public function processImages(int $ticketId): bool
    {
        // Check for errors
        foreach ($_FILES['error_images']['error'] as $value) {
            if ($value !== UPLOAD_ERR_OK) {
                throw new Exception("Upload failed with error code: " . $_FILES['error'][$value]);
            }
        }

        // Check MIME type
        foreach ($_FILES['error_images']['tmp_name'] as $fileLocation) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimeType = finfo_file($finfo, $fileLocation);
            finfo_close($finfo);

            if (!str_contains($mimeType, "image/jpeg") && !str_contains($mimeType, "image/png")) {
                throw new Exception("Wrong file format!");
            }
        }

        // Check file extension
        $allowedExtensions = ["jpg", "jpeg", "png",];
        foreach ($_FILES['error_images']['name'] as $fileName) {
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new Exception("Wrong file extension!");
            }
        }

        $locationDir = ROOT . DS . "public" . DS . "img" . DS . "ticket_images";

        // Checks if the directory exists and creates it if it doesn't exist
        checkAndCreateDirectory($locationDir);

        // Prepare names and moving files
        $movingResult = [];
        $imageNames = [];
        $iterations = count($_FILES['error_images']['tmp_name']);

        // Initializes the array to store successfully uploaded files.
        $uploadedFiles = [];
        
        for ($i = 0; $i < $iterations; $i++) { 
            $imageName = uniqid() . "-" . strtolower(str_replace(" ", "-", $_FILES['error_images']['name'][$i]));
            $imageNames[] = $imageName;
        
            $movingSuccess = move_uploaded_file($_FILES['error_images']['tmp_name'][$i], $locationDir . DS . $imageName);
            $movingResult[] = $movingSuccess;

            if ($movingSuccess) {
                $uploadedFiles[] = $imageName;
            }
        }

        // Rolls back the process by deleting successfully uploaded files if any file fails to upload.
        if (in_array(false, $movingResult)) {
            $this->deleteAttachmentsFromServer($uploadedFiles, $locationDir);
            return false;
        }

        // Add images to the database
        if ($this->addImagesToDatabase($imageNames, $ticketId)) {
            return true;
        } else {
            // Deletes uploaded files if inserting to the database fails.
            $this->deleteAttachmentsFromServer($imageNames, $locationDir);
            return false;
        }
    }

    /**
     * Inserts image file names into the database.
     * 
     * @return bool Returns true on succes otherwise false.
     */
    private function addImagesToDatabase(string|array $images, int $ticketId): bool 
    {
        try {
            $query = "INSERT INTO ticket_attachments (file_name, ticket) VALUES (:file_name, {$ticketId})";
            $stmt = $this->getConn()->connect()->prepare($query);

            if (is_array($images)) {
                foreach ($images as $value) {
                    $stmt->bindValue(":file_name", $value, PDO::PARAM_STR);
                    $stmt->execute();
                }
            } else {
                $stmt->bindValue(":file_name", $images, PDO::PARAM_STR);
                $stmt->execute();
            }

            return true;
        } catch (\PDOException $e) {
            // Deletes attachment enteries that are added for this ticket.
            $this->deleteAttachmentsFromDBByTicket($ticketId);

            logError("addImagesToDatabase() metod error: Adding images to the database failed! ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            return false;
        }
    }

    /**
     * Creates a new ticket
     */
    public function createTicket(): void
    {
        $this->collectTicketData();
        $conn = $this->getConn()->connect();

        try {
            $query = "INSERT INTO tickets (created_year, created_month, created_day, department, created_by, priority, statusId, title, body) " .
            "VALUES(:cy, :cm, :cd, :de, :us, :pr, :st, :tt, :bd)";

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
            $stmt->execute();
            $ticketId = (int) $conn->lastInsertId();
            $this->processImages($ticketId);

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
        bool $images = true
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
        ?string $sortBy = null
    ): int
    {
        // Validates sorting and ordering values and sets $table value.
        $table = $this->validateSortingAndOrdering($allowedValues, $orderBy, $sortBy);

        try {
            // Initial query to select ticket data and associated table names for join.
            $query = "SELECT COUNT(*) FROM tickets t";

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
                        $query .= " LEFT JOIN users u ON t.handled_by = u.id"; 
                }

                $column = (in_array($tableAllias, ["s", "p", "d"])) ? "name" : "id";
                $query .= " WHERE " . $tableAllias . "." . $column . " = '" . $sortBy . "'";
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

        // Checka if the $orderBy value is valid.
        $allowedOrder = false;
        if ($orderBy === "newest" || $orderBy === "oldest") {
            $allowedOrder = true;
        }

        // Throw an exception if either $sortBy or $orderBy is invalid.
        if ($allowedSort !== true ||  $allowedOrder !== true) {
            throw new DomainException("Invalid order/sort value!");
        }

        return $table;
    }

    /**
     * Deletes all file names for a chosen ticket from ticket_attachments table.
     * 
     * @param int $ticketID ID of the ticket whose files want to remove
     * @return bool Returns true if at least one attachment was deleted, otherwise false.
     */
    public function deleteAttachmentsFromDBByTicket(int $ticketId): bool
    {
        try {
            $sql = "DELETE FROM ticket_attachments WHERE ticket = :ticket";
            $stmt = $this->getConn()->connect()->prepare($sql);
            $stmt->bindValue(":ticket", $ticketId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;

        } catch (\PDOException $e) {
            logError("deleteAttachmentsByTicket() metod error: Failed to delete attachments for ticket ID: {$ticketId}", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            return false;
        }
    }

    /**
     * Removes attachments from server.
     * 
     * @param string|array $attachment Name or names of file(s) should be removed.
     * @param string $locationDirectory File location.
     */
    public function deleteAttachmentsFromServer(string|array $attachment, string $locationDirectory): void
    {
        if (is_array($attachment)) {
            foreach ($attachment as $value) {
                if (!unlink($locationDirectory . DS . $value)) {
                    logError("deleteAttachmentsFromServer() metod error: Failed to delete the attachment: {$value}");
                };
            }
        } else {
            if (!unlink($locationDirectory . DS . $attachment)) {
                logError("deleteAttachmentsFromServer() metod error: Failed to delete the attachment: {$attachment}");
            };
        }
    }
}