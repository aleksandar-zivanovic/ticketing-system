<?php
require_once 'BaseModel.php';

class Message extends BaseModel
{
    private string $message;

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
                $attachment->processImages($messageId, "message_attachments", "error_images");
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
            logError(
                "allMessagesByTicket() method error: Failed to retrieve the ticket messages.",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new Exception("Something went wrong. Try again later!");
        }
    }

    /**
     * Retrieves the message and its attachments.
     * 
     * @param int $id Message id.
     * @return array An array with message details.
     */
    public function getMessageWithAttachments(int $id): array
    {
        try {
            $sql = "SELECT 
                m.* , 
                u.id as creator_id, 
                GROUP_CONCAT(ma.id) as attachment_id, 
                GROUP_CONCAT(ma.file_name) as file 
                FROM messages m
                LEFT JOIN message_attachments ma ON ma.message = m.id 
                LEFT JOIN users u ON u.id = m.user 
                WHERE m.id = :id";
            $stmt = $this->getConn()->connect()->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logError(
                "getMessageWithAttachments() method error: Failed to retrieve the message.",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new Exception("Something went wrong. Try again later!");
        }
    }

    public function getAllMessageIdsByTicket(int $ticketId): array
    {
        try {
            $sql = "SELECT id FROM messages WHERE ticket = :ti";
            $stmt = $this->getConn()->connect()->prepare($sql);
            $stmt->bindValue(":ti", $ticketId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            logError(
                "getAllMessageIdsByTicket() metod error: Fetching message ids from the database failed!",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
        }
    }

    /**
     * Updates the body column in the messages table for a specific message ID.
     * This method only updates the message text; attachments are handled separately.
     * 
     * @param int $id ID of the message in the messages table.
     * @param string $message Text of the message.
     */
    public function editMessage(int $id, string $message): void
    {
        try {
            $sql = "UPDATE messages SET body = :msg WHERE id = :id";
            $stmt = $this->getConn()->connect()->prepare($sql);
            $stmt->bindValue(":msg", $message, PDO::PARAM_STR);
            $stmt->bindValue("id", $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            logError(
                "editMessage() metod error: Updating message failed!",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new RuntimeException("Error Updating the Message!");
        }
    }
}
