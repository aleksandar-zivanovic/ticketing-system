<?php

require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'services' . DS . 'EmailService.php';
require_once ROOT . 'classes' . DS . 'Message.php';
require_once ROOT . 'classes' . DS . 'User.php';

class MessageNotificationsService extends BaseService
{
    private EmailService $emailService;
    private Message $messageModel;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->messageModel = new Message();
    }

    // $emailData = [string $email, string $name, string $surname, string $participantType]
    public function sendNewMessageNotification(
        string $title,
        int $ticketId,
        string $date,
        int $ticketCreatorId,
        int $messageCreatorId,
        string $messageCreatorFullName,
        string $messageContent,
        bool $is_creator // whether the message creator is also the ticket creator
    ): void {
        $subject  = "New Message Notification";
        $linkText = "Click Here to View the Message:";

        $emailData = $this->messageModel->getConversationParticipantsExceptMessageCreator(
            ticketId: $ticketId,
            messageCreator: $messageCreatorId
        );

        $ticketCreatorInList = false;
        foreach ($emailData as $data) {
            if ($data["user"] === $ticketCreatorId) {
                $ticketCreatorInList = true;
                break;
            }
        }

        // Adds ticket creator to the email list if they are not the message creator and is not already in the participants list
        if ($is_creator === false && $ticketCreatorInList === false) {
            $userModel = new User();
            $ticketCreatorData = $userModel->getUserById($ticketCreatorId);
            $emailData[] = [
                'user'    => $ticketCreatorData['id'],
                'email'   => $ticketCreatorData['email'],
                'name'    => $ticketCreatorData['name'],
                'surname' => $ticketCreatorData['surname']
            ];
        }

        // Sends email to each participant except the message creator if there are any participant found
        if (!empty($emailData)) {
            foreach ($emailData as $singleEmailData) {
                // Unpacks email data
                [
                    'user'    => $user,
                    'email'   => $email,
                    'name'    => $name,
                    'surname' => $surname
                ] = $singleEmailData;

                // Create URL link based on participant type
                $interface = ($ticketCreatorId === $user) ? "user/user-" : "admin/";
                $linkUrl   = BASE_URL . $interface . "view-ticket.php?ticket=" . $ticketId;

                // Build the email content
                $body = require ROOT . 'EmailTemplates' . DS . 'message_create_notification_email.php';

                // Plain text alternative body
                $altBody =
                    "Hello {$name} {$surname},\n\n" .
                    "You have received a new message in the ticket: {$title}.\n\n" .
                    "Message Content: {$messageContent}.\n" .
                    "You can view the message here: {$linkUrl}\n\n" .
                    "Best regards,\n" .
                    "The Messaging System Team";

                // Sends the email
                $this->emailService->sendEmail(email: $email, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
            }
        }
    }
}
