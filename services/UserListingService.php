<?php
require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'services' . DS . 'SortingAndOrderingService.php';
require_once ROOT . 'classes' . DS . 'User.php';
require_once ROOT . 'classes' . DS . 'Department.php';
require_once ROOT . 'Support' . DS . 'Paginator.php';
require_once ROOT . 'ViewModel' . DS . 'PaginationViewModel.php';

class UserListingService extends BaseService
{
    private User $user;
    private Department $department;
    private SortingAndOrderingService $sortingAndOrderingService;

    public function __construct()
    {
        $this->user = new User();
        $this->department = new Department();
        $this->sortingAndOrderingService = new SortingAndOrderingService();
    }

    /**
     * Retrieves all users from the database.
     *
     * @param string|null $where Optional SQL WHERE clause to filter users.
     * @param int|null $limit Optional limit on the number of users to retrieve.
     * @param int|null $offset Optional offset for the returned users.
     * @param string $orderBy Order by direction, either "ASC" or "DESC". Defaults to "ASC".
     * @return array An array of users.
     * @throws \RuntimeException If a request to the database fails.
     * @see User::getAll()
     */
    private function getAllUsersAndCounts(?string $where = null, ?int $limit = null, ?int $offset = null, string $orderBy = "ASC"): array
    {
        $users = $this->user->getAllUsersWithTicketsCount(where: $where, limit: $limit, offset: $offset, orderBy: $orderBy);
        return $users;
    }

    public function preparedData(int $limit, int $currentPage, string $orderBy): array
    {
        $offset          = ($currentPage - 1) * $limit;
        $departments     = $this->department->getAllDepartments();
        $departmentNames = $this->department->getAllDepartmentNames() + ["" => "None"];
        $allowedValues   = [
            "departments" => $departmentNames,
            "roles"       => array_keys(USER_ROLES)
        ];

        $sortAndOrder = $this->sortingAndOrderingService->validateSortByRequest($allowedValues);

        $where = null;
        if ($sortAndOrder["table"] !== null && !empty($sortAndOrder["cleanSortBy"])) {
            $table = $sortAndOrder["table"];
            if ($table === "roles") {
                $roleId = array_search($sortAndOrder["cleanSortBy"], array_keys(USER_ROLES));
                if ($roleId === false) {
                    throw new RuntimeException("Invalid role name provided for filtering users.");
                }
                $where = "u.role_id = " . ($roleId + 1);
            } elseif ($table === "departments") {
                $departmentId = array_search($sortAndOrder["cleanSortBy"], $departmentNames);
                if ($departmentId === false) {
                    throw new RuntimeException("Invalid department name provided for filtering users.");
                }
                
                $where = "u.department_id " . (!empty($departmentId) ? "= " . ($departmentId + 1) : "IS NULL");
            }
        }

        $users = $this->getAllUsersAndCounts($where, $limit, $offset, $orderBy);
        foreach ($users as &$user) {
            foreach (USER_ROLES as $roleId) {
                if ($user["role_id"] === $roleId) {
                    $user["role_name"] = array_search($roleId, USER_ROLES);
                }
            }

            if ($user["department_id"] !== null) {
                foreach ($departments as $department) {
                    if ($user["department_id"] === $department["id"]) {
                        $user["department_name"] = $department["name"];
                    }
                }
            } else {
                $user["department_name"] = "none";
            }
        }

        // Total number of users in the database
        if ($where !== null) {
            $where = str_replace("u.", "", $where);
        }

        $userCount   = $this->user->countUsers($where);

        $paginator   = new Paginator($limit, $userCount);
        $totalPages  = $paginator->getTotalPages();

        $viewModel   = new PaginationViewModel($paginator);
        $pages       = $viewModel->getPageButtons($currentPage, $totalPages);

        return [
            "users" => $users,
            "departments" => $departmentNames,
            "roles" => USER_ROLES,
            "userCount" => $userCount,
            "pagination" => $paginator,
            "totalPages" => $totalPages,
            "pages" => $pages
        ];
    }
}
