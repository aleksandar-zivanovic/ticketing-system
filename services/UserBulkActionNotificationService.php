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
    public function sendActionPerformerNotification(array $userIds, array $performedBy, string $timestamp): void
    {
        $idsString = implode(", ", $userIds);
        $plural    = count($userIds) > 1 ? "s" : "";
        $subject   = "User Action Performed - {$performedBy["action"]}";
        $linkText  = "Click Here to Visit Admin Panel";
        $linkUrl   = BASE_URL . "admin" . DS . "users-listing.php";
        $email     = $performedBy["email"];
        $name      = $performedBy["name"];
        $surname   = $performedBy["surname"];

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
     * Sends a notification to users when their profile is updated.
     *
     * @param array $usersDetails An array containing details of the affected users.
     * @param string $subject The subject of the email.
     * @param array $messages An array containing the email messages. Format:
     *                        - "altBody": The plain text alternative body.
     *                        - "template": The HTML template body.
     * @return void
     *
     * @throws Exception If a problem occurs during sending the email.
     * @see EmailService::sendEmail()
     */
    private function sendUserNotification(array $usersDetails, string $subject, array $messages): void
    {
        $linkText       = "Click Here to Visit Your Profile";
        foreach ($usersDetails as $user) {
            $linkUrl  = BASE_URL . "profile.php?user=" . $user["id"];
            $email    = $user["email"];
            $name     = $user["name"];
            $surname  = $user["surname"];

            // Build the email content
            $body     = require ROOT . 'EmailTemplates' . DS . 'drop_down_user_notification_email.php';

            // Plain text alternative body
            $altBody  =
                "Hello {$name} {$surname},\n" .
                "{$messages['altBody']}\n" .
                "You can view your profile here: {$linkUrl} \n\n" .
                "Best regards,\n" .
                "The Ticketing System Team";

            // Sends the email
            $this->emailService->sendEmail(email: $email, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
        }
    }

    /**
     * Creates a notification for users whose role has been changed.
     *
     * @param array $usersDetails An array containing details of the affected users.
     * @param int $roleId The ID of the new role.
     * @return void
     *
     * @throws Exception If a problem occurs during sending the email.
     * @see UserBulkActionNotificationService::sendActionPerformerNotification()
     */
    public function sendChangeRoleNotification(array $usersDetails, int $roleId): void
    {
        $roleName             = array_flip(USER_ROLES)[$roleId];
        $subject              = "Your role has been changed";
        $messages["altBody"]  = "Your role has been changed to: " . $roleName;
        $messages["template"] = "Your role has been changed to: <span style='font-style: italic; font-weight:bold;'>{$roleName}</span>.";
        $this->sendUserNotification($usersDetails, $subject, $messages);
    }

    /**
     * Sends a notification to users when their department is changed.
     *
     * @param array $usersDetails An array containing details of the affected users.
     * @param int $departmentId The ID of the new department.
     * @return void
     *
     * @throws Exception If a problem occurs during sending the email.
     * @see UserBulkActionNotificationService::sendActionPerformerNotification()
     */
    public function sendChangeDepartmentNotification(array $usersDetails, int $departmentId): void
    {
        $departmentName       = array_flip(DEPARTMENTS)[$departmentId];
        $subject              = "Your department has been changed";
        $messages["altBody"]  = "Your department has been changed to: " . $departmentName;
        $messages["template"] = "Your department has been changed to: <span style='font-style: italic; font-weight:bold;'>{$departmentName}</span>.";
        $this->sendUserNotification($usersDetails, $subject, $messages);
    }
}
