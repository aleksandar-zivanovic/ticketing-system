<?php
require_once 'BaseModel.php';

class Attachment extends BaseModel
{
    public string $attachmentDirectory = ROOT . "public" . DS . "img" . DS . "ticket_images";

    /**
     * Processes images from the form.
     * Checks for errors, validates MIME types, and verifies allowed file extensions.
     * Creates the image folder if it doesn't exist.
     * Prepares adequate file names for images.
     * Prepares unique image names and sanitized them.
     * Inserts image names to the database.
     * Uploads images to the designated folder.
     * 
     * @param ?array $ticketAttachments Formatted array of attachments for multiple tickets, null a single ticket. Default is null.
     * @param int $id ID of a ticket or message related to the files.
     * @param string $table Name of the table (`ticket_attachments` or `message_attachments`).
     * @param string $fieldName The name of the file input field in the form.
     * 
     * @return bool Returns true on success, otherwise false. 
     * 
     * @throws UnexpectedValueException If the table name is invalid.
     * @throws Exception If there is an error in upload process, file format or file extension is wrong.
     */
    public function processImages(array $ticketAttachments, int $id, string $table, string $fieldName): bool
    {
        // Ensure the provided table name is valid
        if ($table !== "ticket_attachments" && $table !== "message_attachments") {
            logError("processImages() method error: Invalid table name: {$table}. Allowed values are 'ticket_attachments' and 'message_attachments'.");
            throw new UnexpectedValueException("Invalid table name provided.");
        }

        // Check for errors
        foreach ($ticketAttachments[$fieldName]['error'] as $value) {
            if ($value !== UPLOAD_ERR_OK) {
                throw new Exception("Upload failed with error code: " . $ticketAttachments['error'][$value]);
            }
        }

        // Check MIME type
        foreach ($ticketAttachments[$fieldName]['tmp_name'] as $fileLocation) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimeType = finfo_file($finfo, $fileLocation);
            finfo_close($finfo);

            if (!str_contains($mimeType, "image/jpeg") && !str_contains($mimeType, "image/png")) {
                throw new Exception("Wrong file format!");
            }
        }

        // Check file extension
        $allowedExtensions = ["jpg", "jpeg", "png",];
        foreach ($ticketAttachments[$fieldName]['name'] as $fileName) {
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new Exception("Wrong file extension!");
            }
        }

        // Checks if the directory exists and creates it if it doesn't exist
        checkAndCreateDirectory($this->attachmentDirectory);

        // Prepare names and moving files
        $movingResult = [];
        $imageNames = [];
        $iterations = count($ticketAttachments[$fieldName]['tmp_name']);

        // Initializes the array to store successfully uploaded files.
        $uploadedFiles = [];

        for ($i = 0; $i < $iterations; $i++) {
            $imageName = uniqid() . "-" . strtolower(str_replace(" ", "-", $ticketAttachments[$fieldName]['name'][$i]));
            $imageNames[] = $imageName;

            $movingSuccess = move_uploaded_file($ticketAttachments[$fieldName]['tmp_name'][$i], $this->attachmentDirectory . DS . $imageName);
            $movingResult[] = $movingSuccess;

            if ($movingSuccess) {
                $uploadedFiles[] = $imageName;
            }
        }

        // Rolls back the process by deleting successfully uploaded files if any file fails to upload.
        if (in_array(false, $movingResult)) {
            $this->deleteAttachmentsFromServer($uploadedFiles);
            return false;
        }

        // Add images to the database
        if ($this->addImagesToDatabase($imageNames, $id, $table)) {
            return true;
        } else {
            // Deletes uploaded files if inserting to the database fails.
            $this->deleteAttachmentsFromServer($imageNames);
            return false;
        }
    }

    /**
     * Returns array of files sent by a split ticket form, formatted as: 
     * [   
     *     0 => 
     *         [
     *             "name"      => [string, string, ...], 
     *             "full_path" => [string, string, ...], 
     *             "type"      => [string, string, ...], 
     *             "tmp_name"  => [string, string, ...], 
     *             "error"     => [string, string, ...], 
     *         ],
     *     1 => 
     *         [
     *             "name"      => [string, string, ...], 
     *             "full_path" => [string, string, ...], 
     *             "type"      => [string, string, ...], 
     *             "tmp_name"  => [string, string, ...], 
     *             "error"     => [string, string, ...], 
     *         ],
     * 
     *     // rest of the array ...
     * ]
     */
    public function processImagesForSplit(): array
    {
        $elements = ["name", "full_path", "type", "tmp_name", "error", "size",];
        $files = [];

        foreach ($elements as $element) {
            foreach ($_FILES["error_images"][$element] as $key => $value) {
                $files[$key]["error_images"][$element] = $value;
            }
        }

        return $files;
    }

    /**
     * Inserts image file names into the appropriate database table.
     * 
     * If an error occurs, it rolls back the process by deleting attachments linked to the given ID.
     * 
     * @param string|array $images Image file name(s) to be inserted.
     * @param int $id ID of a ticket or a message to which the attachment belongs. 
     * @param string $table Name of the table (`ticket_attachments` or `message_attachments`).
     * 
     * @return bool Returns true on success, otherwise false.
     */
    public function addImagesToDatabase(string|array $images, int $id, string $table): bool
    {
        $column = $table === "message_attachments" ? "message" : "ticket";

        // Convert a string to an array to unify processing for both types.
        if (is_string($images)) $images = [$images];

        $placeholders = [];
        $params = [];
        foreach ($images as $key => $image) {
            $placeholders[] = "(:img{$key}, :id)";
            $params[] = $image;
        }

        try {
            // Create query.
            $query = "INSERT INTO {$table} (file_name, {$column}) VALUES " . implode(", ", $placeholders);

            $stmt = $this->getConn()->prepare($query);

            foreach ($params as $key => $param) {
                $stmt->bindValue(":img{$key}", $param, PDO::PARAM_STR);
            }

            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (\PDOException $e) {
            // Deletes attachment enteries that are inserted into database.
            $ids = $this->getAttachmentsIdsForTicket($id);
            $this->deleteAttachmentsFromDbById($ids, "ticket_attachments");

            logError(
                "addImagesToDatabase() metod error: Adding images to the database failed! ",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            return false;
        }
    }

    /**
     * Deletes all file names for a chosen ticket from ticket_attachments or message_attachments table.
     * 
     * @param int $id ID of the ticket or the message whose files should be removed.
     * @param string $table Database table (`message_attachments` or `ticket_attachments`).
     * @return bool Returns true if at least one attachment was deleted, otherwise false.
     */
    public function deleteAttachmentsFromDbById(int|array $id, string $table): bool
    {
        if ($table !== "message_attachments" && $table !== "ticket_attachments") {
            throw new Exception("Wrong Database Table!");
        }

        // Convert an integer to an array to unify processing for both types.
        if (is_int($id)) $id = [$id];

        // Validate and prepare IDs for placeholders and binding in SQL query.
        require_once 'helpers/IdValidator.php';
        list($integerIds, $params) = IdValidator::prepareIdsForQuery($id);

        try {
            $sql = "DELETE FROM {$table} WHERE id in (" . implode(',', $params) . ")";
            $stmt = $this->getConn()->prepare($sql);
            // Bind values
            $this->bindParamsToQuery($stmt, $params, $integerIds);

            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            logError(
                "deleteAttachmentsFromDbById() metod error: Failed to delete attachments for the ticket/message ID in this range of IDs: " . implode(", ", $params),
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            return false;
        }
    }

    /**
     * Deletes attachments from server.
     * If there is deletion problem, the same file 
     * will be tried to delete three more times.
     * Unsuccessful tries log to php_errors.log file.
     * Undeleted file names will be logged in image_error.log.
     * If there are undeleted files at the end RuntimeException will be thrown.
     * 
     * @param string|array $attachments Name or names of file(s) should be removed.
     * @return void
     * @throws RuntimeException If failed to delete.
     */
    public function deleteAttachmentsFromServer(string|array $attachments): void
    {
        // Convert a string to an array to unify processing for both types.
        $deleteFiles = is_string($attachments) ? explode(",", $attachments) : $attachments;

        $allowedAttempts = 3; // Maximum number of deletion attempts allowed.
        $failedToDelete = []; // Array of files failed to delete.

        // Delete files from server and log unsuccessful deletes.
        foreach ($deleteFiles as $value) {
            $fileLocation = $this->attachmentDirectory . DS . $value;
            $attempts = 0;
            $success = true;

            while ($attempts < $allowedAttempts) {
                if (file_exists($fileLocation)) {
                    $attempts++;

                    if (unlink($fileLocation)) {
                        $deleteImages[] = $value;
                        break; // Exit loop if file is successfully deleted.
                    } else {
                        sleep(1); // Sleep for 1 second.
                        // Log failure to php_errors.log
                        logError("Failed to delete image", ['image' => $value, 'path' => $fileLocation]);
                        $success = false;
                    }
                } else {
                    logError("File doesn't exist!", ['image' => $value, 'path' => $fileLocation]);
                    throw new RuntimeException("Failed to delete file after {$allowedAttempts} attempts: {$fileLocation}");
                }
            }

            if (!$success) {
                $failedToDelete[] = $value;
                // Log undeleted files to image_error.log
                logError(message: $fileLocation, logFileName: "image_error.log");
            }
        }

        if (!empty($failedToDelete)) {
            $reportFail = implode(", ", $failedToDelete);
            throw new RuntimeException("Failed to delete file(s): " . $reportFail . " from server.");
        }
    }

    /**
     * Fetches attachments' details by attachments' IDs.
     * 
     * @param array $ids Array of attachment IDs.
     * @param string $table Name of the table from which the data should be fetched. 
     *               Allowed table names are "message_attachments" and "ticket_attachments"
     * @return array Returns an associative array of attachments' details.
     */
    public function getAttachmentsByIds(array $ids, string $table): array
    {
        // Validate the database ticket name.
        if ($table !== "message_attachments" && $table !== "ticket_attachments") {
            throw new Exception("Trying to fetch data from invalid table.");
        }

        // Validate and prepare IDs for placeholders and binding in SQL query.
        require_once 'helpers/IdValidator.php';
        list($integerIds, $params) = IdValidator::prepareIdsForQuery($ids);

        try {
            $sql = "SELECT * FROM {$table} WHERE id in (" . implode(",", $params) . ")";
            $stmt = $this->getConn()->prepare($sql);

            // Bind values
            $this->bindParamsToQuery($stmt, $params, $integerIds);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logError(
                "getAttachmentsByIds() metod error: Failed to fetch attachments",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new Exception("Error Fetching Request!");
        }
    }

    /** 
     * Bind values to placeholders in an SQL query for a specific PDO parameter type. 
     * This method binds each value in the `$values` array to the corresponding placeholder 
     * in the prepared SQL statement (`$stmt`), using the specified parameter type 
     * (default is PDO::PARAM_INT).
     * 
     * @param PDOStatement $stmt The prepared PDO statement with placeholders.
     * @param array $params Array of placeholders (e.g., ':id1', ':id2').
     * @param array $values Array of values to bind to the placeholders.
     * @param $paramType The PDO parameter type to bind (default is PDO::PARAM_INT).
     * 
     */
    protected function bindParamsToQuery($stmt, array $params, array $values, $paramType = PDO::PARAM_INT): void
    {
        foreach ($params as $key => $param) {
            $stmt->bindValue($param, $values[$key], $paramType);
        }
    }

    /**
     * Fetches all attachment IDs associated with a given ticket.
     * 
     * This method retrieves the `id` values from the `ticket_attachments` table 
     * where the ticket ID matches the provided one. 
     * If no attachments are found, an empty array is returned.
     * 
     * 
     * 
     * 
     * @param int $id The ID of the ticket for which attachment IDs are being fetched.
     * @return array An array of attachment IDs related to the specified ticket. 
     *               Returns an empty array if no attachments are found.
     */
    public function getAttachmentsIdsForTicket(int $id): array
    {
        try {
            $sql = "SELECT id from ticket_attachments WHERE ticket = :id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            logError(
                "getAttachmentsIdsForTicket() metod error: Failed to fetch attachments IDs for ticket ID: {$id}",
                ['message' => $e->getMessage(), 'code' => $e->getCode()]
            );
            throw new Exception("Error Fetching Request!");
        }
    }

    /**
     * Checks if attachment files exist on the server 
     * and returns an array of existing and missing attachments.
     * If the list of files is a string, the method converts it to an array.
     * 
     * @param string|array $attachment Attachment(s) to check for existence.
     * @return array An associative array with existing and missing attachments.
     *               The scructure of the array: 
     *               ["exist" => array of existing attachments, "missing" => array of missing attachments]
     */
    public function isAttachmentExisting(string|array $attachment): array
    {
        // If attachment is a string, convert it to an array.
        $attachments = is_string($attachment) ? explode(",", $attachment) : $attachment;

        $existingFiles = [];
        $missingFiles = [];
        foreach ($attachments as $fileName) {
            if (file_exists($this->attachmentDirectory . DS . $fileName)) {
                $existingFiles[] = $fileName;
            } else {
                $missingFiles[] = $fileName;
            }
        }

        return ["exist" => $existingFiles, "missing" => $missingFiles];
    }
}
