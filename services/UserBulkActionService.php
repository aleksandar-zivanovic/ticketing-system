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
            require_once ROOT . 'classes' . DS . 'Department.php';
            $department = new Department();
            $departments = $department->getAllDepartments();
            foreach ($departments as $dept) {
                if ($value === $dept['name']) {
                    return ["success" => true, "action" => "changeDepartment", "departmentId" => $dept['id']];
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
     * @return void
     */
    public function changeRole(array $data)
    {
        require_once ROOT . 'classes' . DS . 'User.php';
        $user = new User();

        $user->updateRowsWithParenthesesOperators(
            tableName: "users",
            columns: [                              // columns to be updated
                ["role_id" => $data["roleId"]],
            ],
            whereClauses: [                         // WHERE clauses
                ["id" => $data["userIds"]],
            ],
            operator: "IN"
        );

        // Process user(s) for actions blocked and unverified

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
        $this->notificationService->createChangeRoleNotification($usersDetails, $data["roleId"]);

        // Send notification email to the user who performed the action
        $this->notificationService->createActionPerformerNotification($data["userIds"], $performedBy, $timestamp);

        // TODO: Log the role change actions, after the audit system has been created


    }
}
