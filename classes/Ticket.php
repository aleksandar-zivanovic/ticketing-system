<?php
require_once('Database.php');

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
     * Creates new ticket
     */
    public function createTicket(): void
    {
        $this->collectTicketData();

        try {
            $query = "INSERT INTO tickets (created_year, created_month, created_day, department, created_by, priority, statusId, title, body) " .
            "VALUES(:cy, :cm, :cd, :de, :us, :pr, :st, :tt, :bd)";

            $stmt = $this->getConn()->connect()->prepare($query);
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
            $_SESSION["info_message"] = "The issue is reported! Thank you!";
            header("Location: {$this->url}");
        } catch (\PDOException $e) {
            logError("createTicket error: INSERT query failed!", ["message" => $e->getMessage(), "code" => $e->getCode()]);

            throw new \RuntimeException("createTicket method query execution failed");
        }

    }
}