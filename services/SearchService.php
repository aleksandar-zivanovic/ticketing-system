<?php
require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'classes' . DS . 'Ticket.php';
require_once ROOT . 'classes' . DS . 'User.php';
require_once ROOT . 'classes' . DS . 'Status.php';
require_once ROOT . 'classes' . DS . 'Priority.php';
require_once ROOT . 'classes' . DS . 'Department.php';

class SearchService extends BaseService
{
    private array $allowedOptions = [
        "Ticketbody",
        "Title",
        "TicketID",
        "NameSurname",
        "UserID",
        "Email"
    ];

    public function validate(array $data): array
    {
        if (!in_array($data["searchOption"], $this->allowedOptions)) {
            return ["success" => false, "message" => "Invalid dropdown option value."];
        }

        if (!in_array($data["searchOption"], ["TicketID", "UserID"])) {
            if (strlen(($data["searchInput"])) < 3) {
                return ["success" => false, "message" => "Search input must be at least 3 characters long."];
            }
        }

        return ["success" => true, "data" => $data];
    }

    public function performSearch(string $option, string $input): array
    {
        if (in_array($option, ["Ticketbody", "Title", "TicketID"])) {
            $table = "tickets";
            $column = match ($option) {
                "Ticketbody" => "body",
                "Title" => "title",
                "TicketID" => "id"
            };
            $model = new Ticket();
        } else {
            $table = "users";
            $column = match ($option) {
                "NameSurname" => ["name", "surname"],
                "UserID" => "id",
                "Email" => "email"
            };
            $model = new User();
        }

        if (!is_array($column)) {
            $where = "{$column} LIKE '%{$input}%'";
        } else {
            $where = "name LIKE '%{$input}%' OR surname LIKE '%{$input}%'";
        }

        $searchResults = $model->getAllResultsWhere($where);

        $departmentModel = new Department();
        $departments = $departmentModel->getAllDepartments();
        $departmentList = array_column($departments, "name", "id");

        if ($table === "tickets") {
            $statusModel = new Status();
            $statuses = $statusModel->getAllstatuses();
            $statusList = array_column($statuses, "name", "id");

            $priorityModel = new Priority();
            $priorities = $priorityModel->getAllPriorities();
            $priorityList = array_column($priorities, "name", "id");

            foreach ($searchResults as &$result) {
                $result["status_name"] = $statusList[$result["statusId"]];
                $result["priority_name"] = $priorityList[$result["priority"]];
                $result["department_name"] = $departmentList[$result["department"]];
            }
            unset($result);
        }

        if ($table === "users") {
            foreach ($searchResults as &$result) {
                $result["role_name"] = array_flip(USER_ROLES)[$result["role_id"]];
                if (empty($result["department_id"])) {
                    $result["department_name"] = "Unassigned";
                } else {
                    $result["department_name"] = $departmentList[$result["department_id"]];
                }
            }
            unset($result);
        }

        // Implementation for performing the search based on option and input
        return ["searchResults" => $searchResults, "table" => $table];
    }
}
