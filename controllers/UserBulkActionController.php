<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';
require_once ROOT . 'services' . DS . 'UserBulkActionService.php';


class UserBulkActionController extends BaseController
{
    private UserBulkActionService $userBulkActionService;

    public function __construct()
    {
        $this->userBulkActionService = new UserBulkActionService();
    }

    public function validateRequest(): array
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return ["success" => false, "message" => "Invalid request method.", "url" => "error"];
        }

        // Determine the action to be performed
        if ($this->hasValue($_POST["user_actions"]) === false) {
            return ["success" => false, "message" => "No user action specified.", "url" => "error"];
        }
        $userActionValue = $_POST["user_actions"];

        // Check if user IDs are provided
        if (!isset($_POST["user_ids"]) || empty($_POST["user_ids"])) {
            return ["success" => false, "message" => "No user IDs provided.", "url" => "error"];
        }

        // Validate user IDs
        $userIds = $_POST["user_ids"];
        if (is_string($userIds)) {
            $validatedIds = $this->validateId($userIds);
        } elseif (is_array($userIds)) {
            $validatedIds = [];
            foreach ($userIds as $id) {
                $userId = $this->validateId($id);
                if ($userId === false) {
                    return ["success" => false, "message" => "Invalid user ID provided: {$id}."];
                }
                $validatedIds[] = $userId;
            }
        }

        // Verify the action using the service
        $serviceVerification = $this->userBulkActionService->verify([
            "userIds" => $validatedIds,
            "userActionValue" => $userActionValue
        ]);

        if ($serviceVerification["success"] === false) {
            return $serviceVerification;
        }

        return [
            "success" => true,
            "data" => [
                "userIds" => $validatedIds,
                ...$serviceVerification
            ]
        ];
    }

    /**
     * Create a success message based on the action performed.
     *
     * @param array $ids An array of user IDs.
     * @param string $action The action performed.
     * @return string The success message.
     */
    private function createSuccessMessage(array $ids, string $action): string
    {
        $message = "";

        switch ($action) {
            case "changeRole":
                $message = "Successfully changed role status for users: " . implode(", ", $ids);
                break;
            case "changeDepartment":
                $message = "Successfully changed department status for users: " . implode(", ", $ids);
                break;
            case "sendBulkEmail":
                # code...
                break;
            case "passwordReset":
                # code...
                break;
            case "deleteUser":
                # code...
                break;
        }

        return $message;
    }

    public function execute(): void
    {
        $this->redirectUrl = BASE_URL . "admin" . DS . "users-listing";
        $validation = $this->validateRequest();
        $this->handleValidation($validation);

        try {
            $method = $validation["data"]["action"];
            $this->userBulkActionService->$method([
                ...$validation["data"],
                "email"   => cleanString($_SESSION["user_email"]),
                "name"    => cleanString($_SESSION["user_name"]),
                "surname" => cleanString($_SESSION["user_surname"]),
            ]);
            $message = $this->createSuccessMessage($validation["data"]["userIds"], $validation["data"]["action"]);
            redirectAndDie($this->redirectUrl, $message, "info");
        } catch (\Throwable $th) {
            dd("Error executing action: " . $th->getMessage());
        }
    }
}
