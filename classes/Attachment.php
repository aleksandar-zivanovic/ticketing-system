<?php
require_once 'Database.php';

class Attachment
{
    private ?Database $dbInstance = null;

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
     * Inserts image file names into the appropriate database table.
     * Determines whether the attachment belongs to a ticket or a message
     * based on the form submission context.
     * 
     * If an error occurs, it rolls back the process by deleting attachments linked to the given ID.
     * 
     * @param string|array $images Image file name(s) to be inserted.
     * @param int $id ID of a ticket or a message to which the attachment belongs.
     * 
     * @return bool Returns true on success, otherwise false.
     */
    private function addImagesToDatabase(string|array $images, int $id): bool 
    {
        try {
            if (isset($_POST['create_message'])) {
                // query for adding attachment in 'message_attachments' table
                $query = "INSERT INTO message_attachments (file_name, message) VALUES (:file_name, {$id})";
            } else {
                // query for adding attachment in 'ticket_attachments' table
                $query = "INSERT INTO ticket_attachments (file_name, ticket) VALUES (:file_name, {$id})";
            }

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
            // Deletes attachment enteries that are inserted into database.
            $this->deleteAttachmentsFromDbById($id);

            logError(
                "addImagesToDatabase() metod error: Adding images to the database failed! ", 
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            return false;
        }
    }

    /**
     * Deletes all file names for a chosen ticket from attachment tables.
     * 
     * @param int $id ID of the ticket or the message whose files should be removed.
     * @return bool Returns true if at least one attachment was deleted, otherwise false.
     */
    public function deleteAttachmentsFromDbById(int $id): bool
    {
        try {
            $sql = "DELETE FROM ticket_attachments WHERE ticket = :ticket";
            $stmt = $this->getConn()->connect()->prepare($sql);
            $stmt->bindValue(":ticket", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;

        } catch (\PDOException $e) {
            logError(
                "deleteAttachmentsFromDbById() metod error: Failed to delete attachments for the ticket/message ID: {$id}", 
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
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
                }
            }
        } else {
            if (!unlink($locationDirectory . DS . $attachment)) {
                logError("deleteAttachmentsFromServer() metod error: Failed to delete the attachment: {$attachment}");
            };
        }
    }
}