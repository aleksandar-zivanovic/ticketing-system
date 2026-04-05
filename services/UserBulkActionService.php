<?php
require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'services' . DS . 'UserBulkActionNotificationService.php';
require_once ROOT . 'classes' . DS . 'User.php';

class UserBulkActionService extends BaseService
{

    private UserBulkActionNotificationService $notificationService;
    private User $user;

    public function __construct()
    {
        $this->notificationService = new UserBulkActionNotificationService();
        $this->user = new User();
    }

    /**
     * Verify the user action and its parameters.
     *
     * @param array $data An associative array containing 'userIds' and 'userActionValue'.
     * @return array An associative array indicating success or failure, and additional data.
     */
    public function verify($data): array
    {
        // Affected users' ids verification
        $idVerificationResult = $this->validateMultipleUsersExistence($data["userIds"], $this->user);
        if (!$idVerificationResult["success"]) {
            return $idVerificationResult;
        }

        // Verify change an user role
        if (str_starts_with($data["userActionValue"], "ur_")) {
            $value = substr($data["userActionValue"], 3);
            foreach (USER_ROLES as $roleName => $roleId) {
                if ($value === $roleName) {
                    return [
                        "success"    => true,
                        "newValueId" => $roleId,
                        "method"     => "changeRole",
                        "action"     => "changed role",
                        "message"    => "Successfully changed role status for users: " . implode(", ", $data["userIds"]),
                    ];
                }
            }
            return ["success" => false, "message" => "Invalid role specified."];
        }

        // Verify change a department
        if (str_starts_with($data["userActionValue"], "dp_")) {
            $value = substr($data["userActionValue"], 3);
            foreach (DEPARTMENTS as $departmentName => $departmentID) {
                if ($value === $departmentName) {
                    return [
                        "success"  => true,
                        "newValueId" => $departmentID,
                        "method"     => "changeDepartment",
                        "action"     => "changed department",
                        "message"    => "Successfully changed department status for users: " . implode(", ", $data["userIds"]),
                    ];
                }
            }
            return ["success" => false, "message" => "Invalid department specified."];
        }

        // Verify advanced actions
        switch ($data["userActionValue"]) {
            case 'send_bulk_email':
                return ["success" => true, "action" => "sendBulkEmail"];
            case 'password_reset':
                return ["success" => true, "action" => "passwordReset"];
            default:
                return ["success" => false, "message" => "Unauthorized user action specified."];
        }
    }

    /**
     * Change the role of one or multiple users.
     *
     * @param array $data An associative array containing 'userIds' and 'roleId'.
     * 
     * @return void
     * @throws RuntimeException if the update in User::updateRowsWithParenthesesOperators() fails
     * @throws RuntimeException if fetching users in User::getAllWhereSafe() fails
     * @throws Exception If a problem occurs during sending the email.
     * @see User::updateRowsWithParenthesesOperators()
     * @see User::getAllWhereSafe()
     * @see UserBulkActionNotificationService::sendChangeRoleNotification()
     */
    public function changeRole(array $data)
    {
        $columns = [
            ["role_id" => $data["newValueId"]],
            USER_ROLES["unverified"] !== $data["newValueId"] ? ["verified" => 1] : ["verified" => 0],
            ["verification_code" => NULL]
        ];

        // Handle notifications for the role change
        $this->handleUserBulkActionNotifications($data, $columns);

        // TODO: Log the role change actions, after the audit system has been created

    }

    /**
     * Change the department of one or multiple users.
     *
     * @param array $data An associative array containing 'userIds' and 'departmentId'.
     * 
     * @return void
     * @throws RuntimeException if the update in User::updateRowsWithParenthesesOperators() fails
     * @throws RuntimeException if fetching users in User::getAllWhereSafe() fails
     * @throws Exception If a problem occurs during sending the email.
     * @see User::getAllWhereSafe()
     * @see User::updateRowsWithParenthesesOperators()
     * @see UserBulkActionNotificationService::sendChangeDepartmentNotification()
     */
    public function changeDepartment(array $data)
    {
        $columns = [
            ["department_id" => $data["newValueId"]],
        ];

        // Handle notifications for the department change
        $this->handleUserBulkActionNotifications($data, $columns);

        // TODO: Log the department change actions, after the audit system has been created

    }

    /**
     * Handle notifications for user bulk actions.
     *
     * @param array $data An associative array containing user action data.
     * @param array $columns An array of columns to be updated in the database.
     * 
     * @return void
     * @throws RuntimeException if the update in User::updateRowsWithParenthesesOperators() fails
     * @throws RuntimeException if fetching users in User::getAllWhereSafe() fails
     * @throws Exception If a problem occurs during sending the email.
     * @see User::getAllWhereSafe()
     * @see User::updateRowsWithParenthesesOperators()
     * @see UserBulkActionNotificationService::sendChangeDepartmentNotification()
     */
    private function handleUserBulkActionNotifications(array $data, array $columns): void
    {
        // Prepare data for sending notifications
        $performedBy = [
            "ids"       => $data["userIds"],
            "email"     => $data["email"],
            "name"      => $data["name"],
            "surname"   => $data["surname"],
            "action"    => $data["action"],
            "plural"    => count($data["userIds"]) > 1 ? "s" : "",
            "idsString" => implode(", ", $data["userIds"]),
        ];

        // Fetch details of affected users for notifications
        $usersDetails = $this->user->getAllWhereSafe("users", "id", "IN", $data["userIds"]);

        $timestamp = date("Y-m-d H:i:s");

        // Update users in the database only if the action is not "sent email"
        if ($performedBy["action"] !== "sent email") {
            $this->user->updateRowsWithParenthesesOperators(
                tableName: "users",
                columns: $columns,                      // columns to be updated
                whereClauses: [                         // WHERE clauses
                    ["id" => $data["userIds"]],
                ],
                operator: "IN"
            );
        }

        $method = "send" . ucfirst($data["method"]) . "Notification";

        if (!method_exists($this->notificationService, $method)) {
            throw new RuntimeException("Notification method $method does not exist.");
        }
        // Send notification/bulk emails to affected users
        $this->notificationService->$method($usersDetails, $data["newValueId"]);

        // Send notification email to the user who performed the action
        $this->notificationService->sendActionPerformerNotification($data["userIds"], $performedBy, $timestamp);
    }
}
