<?php

require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'services' . DS . 'EmailService.php';

class UserBulkActionNotificationService extends BaseService
{
    private EmailService $emailService;

    public function __construct()
    {
        $this->emailService = new EmailService();
    }

    /**
     * Creates a notification for the user who performed the action.
     *
     * @param array $userIds An array of user IDs.
     * @param array $performedBy An array containing details of the user who performed the action.
     * @param string $timestamp The timestamp when the action was performed.
     * @return void
     * 
     * @throws Exception If a problem occurs during sending the email.
     * @see EmailService::sendEmail()
     */
    public function createActionPerformerNotification(array $userIds, array $performedBy, string $timestamp): void
    {
        $idsString = implode(", ", $userIds);
        $plural    = count($userIds) > 1 ? "s" : "";

        $subject  = "User Action Performed - {$performedBy["action"]}";
        // $message  = "You {$performedBy["action"]} for user{$plural} with ID{$plural}: {$idsString} at {$timestamp}";
        $linkText = "Click Here to Visit Admin Panel";
        $linkUrl  = BASE_URL . "admin/users-listing.php";
        $email    = $performedBy["email"];
        $name     = $performedBy["name"];
        $surname  = $performedBy["surname"];

        // Build the email content
        $body     = require_once ROOT . 'EmailTemplates' . DS . 'action_performer_notification_email.php';

        // Plain text alternative body
        $altBody  =
            "Hello {$name} {$surname},\n" .
            "You {$performedBy["action"]} for user{$plural} with ID{$plural}: {$idsString} at {$timestamp} \n" .
            "You can view the users listing admin panel here: {$linkUrl} \n\n" .
            "Best regards,\n" .
            "The Ticketing System Team";

        // Sends the email
        $this->emailService->sendEmail(email: $email, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
    }

    /**
     * Creates a notification for users whose role has been changed.
     *
     * @param array $usersDetails An array containing details of the affected users.
     * @param int $roleId The ID of the new role.
     * @return void
     *
     * @throws Exception If a problem occurs during sending the email.
     * @see EmailService::sendEmail()
     */
    public function createChangeRoleNotification(array $usersDetails, int $roleId): void
    {
        $roleName = array_flip(USER_ROLES)[$roleId];
        $subject  = "Your role has been changed";
        // $message  = "Your role has been changed to: " . $roleName;
        $linkText = "Click Here to Visit Your Profile";

        foreach ($usersDetails as $user) {
            $linkUrl  = BASE_URL . "profile.php?user=" . $user["id"];
            $email    = $user["email"];
            $name     = $user["name"];
            $surname  = $user["surname"];

            // Build the email content
            $body     = require ROOT . 'EmailTemplates' . DS . 'change_role_notification_email.php';

            // Plain text alternative body
            $altBody =
                "Hello {$name} {$surname},\n" .
                "Your user role is changed to " . USER_ROLES[$roleId] . "\n" .
                "You can view your profile here: {$linkUrl} \n\n" .
                "Best regards,\n" .
                "The Ticketing System Team";

            // Sends the email
            $this->emailService->sendEmail(email: $email, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
        }
    }
}
