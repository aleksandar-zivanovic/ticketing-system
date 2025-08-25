<?php
require_once ROOT . 'classes' . DS . 'Attachment.php';
require_once ROOT . 'services' . DS . 'TicketService.php';

final class AttachmentService
{
    // upravlja brisanjem i uploadovanjem fajlova i upisom u bazu 
    // pozivajuci metode iz Attachment modela

    private Attachment $attachment;

    public function __construct()
    {
        $this->attachment = new Attachment();
    }

    /**
     * Deletes attachment(s) from server and database.
     * 
     * @param array $ticket Ticket data retrieved with Ticket::fetchTicketDetails().
     * @return void
     * @throws Exception If Attachment::getAttachmentsByIds() failes to fetch attachment(s) ID(s)
     * @throws RuntimeException If deletion files or form database fails.
     * @see Attachment::getAttachmentsByIds()
     * @see Attachment::isAttachmentExisting()
     * @see Attachment::deleteAttachmentsFromServer()
     */
    public function deleteAttachments(array $ticket): void
    {
        // Convert string of IDs to an array of IDs.
        $idsArray = explode(",", $ticket["attachment_id"]);

        // Fetches attachments' details by attachments' IDs.
        $attachments = $this->attachment->getAttachmentsByIds($idsArray, "ticket_attachments");

        // Get attachment names for deleteAttachmentsFromServer() method.
        $attachmentNames = [];
        foreach ($attachments as $anAttachment) {
            $attachmentNames[] = $anAttachment["file_name"];
        }

        // Collect data about existing and missing files.
        $attachmentFilesStatus = $this->attachment->isAttachmentExisting($attachmentNames);

        if (!empty($attachmentFilesStatus["exist"])) {
            // Delete attachments from the server.
            $this->attachment->deleteAttachmentsFromServer($attachmentNames);
        }

        // Delete attachments from the database.
        if ($this->attachment->deleteAttachmentsFromDbById($idsArray, "ticket_attachments") === false) {
            throw new RuntimeException("Deleting attachments from the database failed");
        };
    }
}
