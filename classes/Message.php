<?php
require_once 'BaseModel.php';

class Message extends BaseModel
{
    /**
     * Inserts a new message into the messages table.
     * 
     * @param int $ticketId ID of the ticket the message is related to.
     * @param int $userId ID of the user creating the message.
     * @param string $message Text of the message.
     * @return int Returns the ID of the newly created message.
     * 
     * @throws RuntimeException If there is an error during the database operation.
     */
    public function createMessage(int $ticketId, int $userId, string $message): int
    {
        $conn = $this->getConn();

        try {
            $sql = "INSERT INTO messages (ticket, user, body) VALUES (:tk, :us, :bd)";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(":tk", $ticketId, PDO::PARAM_INT);
            $stmt->bindValue(":us", $userId, PDO::PARAM_INT);
            $stmt->bindValue(":bd", $message, PDO::PARAM_STR);
            $stmt->execute();
            return (int) $conn->lastInsertId();
        } catch (\PDOException $e) {
            logError(
                "createMessage() metod error: Inserting a message to the database failed! ",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );

            throw new \RuntimeException("Creating message failed");
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

            $stmt = $this->getConn()->prepare($sql);
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
     * @return array An array with message details and its attachments or an empty array if not found.
     * @throws RuntimeException If there is an error during the database operation.
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
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logError(
                "getMessageWithAttachments() method error: Failed to retrieve the message.",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new RuntimeException("Something went wrong. Try again later!");
        }
    }

    /**
     * Retrieves all message IDs associated with a specific ticket.
     * 
     * @param int $ticketId ID of the ticket.
     * @return array An array of message IDs.
     * @throws RuntimeException If there is an error during the database operation.
     */
    public function getAllMessageIdsByTicket(int $ticketId): array
    {
        try {
            $sql = "SELECT id FROM messages WHERE ticket = :ti";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(":ti", $ticketId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            logError(
                "getAllMessageIdsByTicket() metod error: Fetching message ids from the database failed!",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new \RuntimeException("Error fetching message ids!");
        }
    }

    /**
     * Updates the body column in the messages table for a specific message ID.
     * This method only updates the message text; attachments are handled separately.
     * 
     * @param int $id ID of the message in the messages table.
     * @param string $message Text of the message.
     * @return void
     * @throws RuntimeException If there is an error during the database operation.
     */
    public function editMessage(int $id, string $message): void
    {
        try {
            $sql = "UPDATE messages SET body = :msg WHERE id = :id";
            $stmt = $this->getConn()->prepare($sql);
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

    /**
     * Deletes a message from the messages table by its ID.
     * 
     * @param int $id ID of the message to be deleted.
     * @return void
     * @throws RuntimeException If there is an error during the deletion process.
     * @see BaseModel::deleteRowById()
     */
    public function deleteMessage(int $id): void
    {
        $this->deleteRowById("messages", $id);
    }
}
