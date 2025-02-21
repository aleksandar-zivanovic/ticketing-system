<?php
require_once 'Database.php';

class Message
{
    private ?Database $dbInstance = null;
    // private int $ticketId;
    private int $userId;
    private string $message;

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

    public function createMessage(int $ticketId): void
    {
        $this->message = cleanString(filter_input(INPUT_POST, "error_description", FILTER_DEFAULT));
        $conn = $this->getConn()->connect();
        
        try {
            $sql = "INSERT INTO messages (ticket, user, body) VALUES (:tk, :us, :bd)";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(":tk", $ticketId, PDO::PARAM_INT);
            $stmt->bindValue(":us", cleanString($_SESSION["user_id"]), PDO::PARAM_INT);
            $stmt->bindValue(":bd", $this->message, PDO::PARAM_STR);
            $stmt->execute();
            $messageId = (int) $conn->lastInsertId();

            // Proccesses files if they are attached in form:
            if ($_FILES['error_images']['error'][0] != 4) {
                require_once 'Attachment.php';
                $attachment = new Attachment();
                $attachment->processImages($messageId);
            }

            header("Location: ../user-view-ticket.php?ticket={$ticketId}");
        } catch (\PDOException $e) {
            logError(
                "createMessage() metod error: Inserting a message to the database failed! ", 
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );

            throw new \RuntimeException("createMessage method query execution failed");
        }
    }

}