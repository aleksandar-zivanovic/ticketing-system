<?php
require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'services' . DS . 'UserBulkActionNotificationService.php';

class UserBulkActionService extends BaseService
{

    private UserBulkActionNotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new UserBulkActionNotificationService();
    }

    /**
     * Verify the user action and its parameters.
     *
     * @param array $data An associative array containing 'userIds' and 'userActionValue'.
     * @return array An associative array indicating success or failure, and additional data.
     */
    public function verify($data): array
    {
        // Verify change an user role
        if (str_starts_with($data["userActionValue"], "ur_")) {
            $value = substr($data["userActionValue"], 3);
            foreach (USER_ROLES as $roleName => $roleId) {
                if ($value === $roleName) {
                    return ["success" => true, "action" => "changeRole", "roleId" => $roleId];
                }
            }
            return ["success" => false, "message" => "Invalid role specified."];
        }

        // Verify change a department
        if (str_starts_with($data["userActionValue"], "dp_")) {
            $value = substr($data["userActionValue"], 3);
            foreach (DEPARTMENTS as $departmentName => $departmentID) {
                if ($value === $departmentName) {
                    return ["success" => true, "action" => "changeDepartment", "departmentId" => $departmentID];
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
            case 'delete_user':
                return ["success" => true, "action" => "deleteUser"];
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
        require_once ROOT . 'classes' . DS . 'User.php';
        $user = new User();

        $columns = [
            ["role_id" => $data["roleId"]],
            USER_ROLES["unverified"] !== $data["roleId"] ? ["verified" => 1] : ["verified" => 0],
            ["verification_code" => NULL]
        ];

        $user->updateRowsWithParenthesesOperators(
            tableName: "users",
            columns: $columns,                      // columns to be updated
            whereClauses: [                         // WHERE clauses
                ["id" => $data["userIds"]],
            ],
            operator: "IN"
        );

        $timestamp = date("Y-m-d H:i:s");

        // Prepare data for sending notifications
        $performedBy = [
            "ids"       => $data["userIds"],
            "email"     => $data["email"],
            "name"      => $data["name"],
            "surname"   => $data["surname"],
            "action"    => "changed role",
            "plural"    => count($data["userIds"]) > 1 ? "s" : "",
            "idsString" => implode(", ", $data["userIds"]),
        ];

        // Fetch details of affected users for notifications
        $usersDetails = $user->getAllWhereSafe("users", "id", "IN", $data["userIds"]);

        // Send notification emails to affected users
        $this->notificationService->sendChangeRoleNotification($usersDetails, $data["roleId"]);

        // Send notification email to the user who performed the action
        $this->notificationService->sendActionPerformerNotification($data["userIds"], $performedBy, $timestamp);

        // TODO: Log the role change actions, after the audit system has been created


    }

    public function changeDepartment(array $data) 
    {
        require_once ROOT . 'classes' . DS . 'User.php';
        $user = new User();

        $columns = [
            ["department_id" => $data["departmentId"]],
        ];

        $user->updateRowsWithParenthesesOperators(
            tableName: "users",
            columns: $columns,                      // columns to be updated
            whereClauses: [                         // WHERE clauses
                ["id" => $data["userIds"]],
            ],
            operator: "IN"
        );

        $timestamp = date("Y-m-d H:i:s");

        // Prepare data for sending notifications
        $performedBy = [
            "ids"       => $data["userIds"],
            "email"     => $data["email"],
            "name"      => $data["name"],
            "surname"   => $data["surname"],
            "action"    => "changed department",
            "plural"    => count($data["userIds"]) > 1 ? "s" : "",
            "idsString" => implode(", ", $data["userIds"]),
        ];

        // Fetch details of affected users for notifications
        $usersDetails = $user->getAllWhereSafe("users", "id", "IN", $data["userIds"]);

        // Send notification emails to affected users
        $this->notificationService->sendChangeDepartmentNotification($usersDetails, $data["departmentId"]);

        // Send notification email to the user who performed the action
        $this->notificationService->sendActionPerformerNotification($data["userIds"], $performedBy, $timestamp);

        // TODO: Log the department change actions, after the audit system has been created

    }
}
