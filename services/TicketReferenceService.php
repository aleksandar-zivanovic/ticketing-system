<?php
require_once ROOT . 'classes' . DS . 'Status.php';
require_once ROOT . 'classes' . DS . 'Priority.php';
require_once ROOT . 'classes' . DS . 'Department.php';

class TicketReferenceService
{
    private Status $statusModel;
    private Priority $priorityModel;
    private Department $departmentModel;

    public function __construct()
    {
        $this->statusModel = new Status();
        $this->priorityModel = new Priority();
        $this->departmentModel = new Department();
    }

    /**
     * Fetches ticket filter data including statuses, priorities, and departments.
     *
     * @return array{
     *     statuses: string[],
     *     priorities: string[],
     *     departments: string[]
     * }
     * @throws RuntimeException if request failed.
     * @see Status::getAllStatusNames()
     * @see Priority::getAllPriorityNames()
     * @see Department::getAllDepartmentNames()
     */
    public function getReferenceData(): array
    {
        return [
            "statuses" => $this->statusModel->getAllStatusNames(),
            "priorities" => $this->priorityModel->getAllPriorityNames(),
            "departments" => $this->departmentModel->getAllDepartmentNames(),
        ];
    }
}
