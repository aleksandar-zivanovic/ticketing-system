<?php
require_once 'Database.php';

class Message
{
    private ?Database $dbInstance = null;
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

            header("Location: ../user/user-view-ticket.php?ticket={$ticketId}");
        } catch (\PDOException $e) {
            logError(
                "createMessage() metod error: Inserting a message to the database failed! ", 
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );

            throw new \RuntimeException("createMessage method query execution failed");
        }
    }

    /**
     * Retrives all messages for the ticket with message details.
     * 
     * @param int $ticketId Id of the ticket the messages are related to.
     * @return array Returns array of messages and their details.
     */
    public function allMessagesByTicket(int $ticketId): array
    {
        try {
            $sql = "SELECT 
                m.*, 
                u.id as creator_id, 
                u.name as creator_name, 
                u.surname as creator_surname, 
                GROUP_CONCAT(a.id) AS attachment_id, 
                GROUP_CONCAT(a.file_name) AS file 
                FROM messages AS m 
                LEFT JOIN message_attachments a ON a.message = m.id 
                LEFT JOIN users u ON u.id = m.user 
                WHERE m.ticket = :tk 
                GROUP BY m.id";
            
            $stmt = $this->getConn()->connect()->prepare($sql);
            $stmt->bindValue(":tk", $ticketId, PDO::PARAM_INT);
            $stmt->execute();

            // Returns all messages related to the ticket
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logError("
                allMessagesByTicket() method error: Failed to retrive the ticket messages.", 
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new Exception("Something went wrong. Try again later!");
        }
    }
}