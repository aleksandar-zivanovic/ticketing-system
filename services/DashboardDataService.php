<?php
require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'services' . DS . 'TicketReferenceService.php';
require_once ROOT . 'classes' . DS . 'Ticket.php';
require_once ROOT . 'classes' . DS . 'User.php';
require_once ROOT . 'classes' . DS . 'Year.php';
require_once ROOT . 'classes' . DS . 'Status.php';
require_once ROOT . 'classes' . DS . 'Priority.php';
require_once ROOT . 'classes' . DS . 'Department.php';
require_once ROOT . 'helpers' . DS . 'chart_helpers.php';

class DashboardDataService extends BaseService
{
    private Ticket $ticketModel;
    private User $userModel;
    private Year $yearModel;
    private TicketReferenceService $ticketReferenceService;

    public function __construct()
    {
        $this->ticketModel     = new Ticket();
        $this->userModel       = new User();
        $this->yearModel       = new Year();
        $this->ticketReferenceService = new TicketReferenceService();
    }

    public function getDashboardData(string $panel, ?string $userId = null): array
    {
        // Initializes allowed filter values for tickets
        $allTicketFilterData = $this->ticketReferenceService->getReferenceData();
        $statuses     = $allTicketFilterData["statuses"];
        $priorities   = $allTicketFilterData["priorities"];
        $departments  = $allTicketFilterData["departments"];

        // Calls fetchAllTickets() method
        if ($panel === "admin") {
            $allTicketsData = $this->ticketModel->fetchAllTickets(images: false);
            $handledTicketsData = $this->ticketModel->fetchAllTickets(images: false, handledByMe: true, userRole: "admin");
        } else {
            $allTicketsData = $this->ticketModel->fetchAllTickets(images: false, userId: $userId);
        }
        unset($this->ticketModel);

        // Removes split tickets from $allTicketsData and counts split and reopened tickets separately
        $spitTicketsCount = 0;
        $reopenedTickets  = 0;
        foreach ($allTicketsData as $key => $_) {
            if ($allTicketsData[$key]["closing_type"] === "split") {
                $spitTicketsCount++;
                unset($allTicketsData[$key]);
                continue;
            }

            // Counts reopened tickets. Doesn't count split tickets.
            if (
                $allTicketsData[$key]["closing_type"] !== "split" &&
                $allTicketsData[$key]["was_reopened"] === 1
            ) {
                $reopenedTickets++;
            }
        }

        // Prepares data for average ticket resolution time stats
        if ($panel === "admin") {
            $allClosedTickets = [];
            $solvingTimeTotal = 0;
            foreach ($allTicketsData as $ticketData) {
                if ($ticketData["closed_date"]) {
                    $allClosedTickets[] = $ticketData;
                    $openedDate = new DateTime($ticketData["created_date"]);
                    $closedDate = new DateTime($ticketData["closed_date"]);
                    $solvingTimeTotal += $closedDate->getTimestamp() - $openedDate->getTimestamp();
                }
            }

            $closedTicketsCount = count($allClosedTickets);
            unset($allClosedTickets);

            if ($closedTicketsCount > 0) {
                $allSecondsPerTicket = (int)($solvingTimeTotal / $closedTicketsCount);

                $days    = floor($allSecondsPerTicket / 86400);
                $hours   = floor(($allSecondsPerTicket % 86400) / 3600);
                $minutes = floor(($allSecondsPerTicket % 3600) / 60);

                $formatedTime = $days > 0 ? "$days d $hours h $minutes m" : "$hours h $minutes m";
            }
        }

        // Counts all existing tickets and their statuses
        $allTicketsCountStatuses   = $this->countStatuses($allTicketsData);
        $countAllTickets           = $allTicketsCountStatuses["all"];
        $countAllInProgressTickets = $allTicketsCountStatuses["in_progress"];
        $countAllSolvedTickets     = $allTicketsCountStatuses["closed"];
        $countAllWaitingTickets    = $allTicketsCountStatuses["waiting"];

        if ($panel === "admin" || ($panel === "user" && $countAllTickets > 0)) {

            if ($panel === "admin") {
                // Counts tickets handled by the admin by status
                $handledTicketsCountStatuses   = $this->countStatuses($handledTicketsData);
                $countHandledTickets           = $handledTicketsCountStatuses["all"];
                $countHandledInProgressTickets = $handledTicketsCountStatuses["in_progress"];
                $countHandledSolvedTickets     = $handledTicketsCountStatuses["closed"];
            }

            // Prepare data for dropdown button
            $years = array_reverse($this->yearModel->getAllYears());
            unset($this->yearModel);
            if (isset($_GET["year"]) && !empty($_GET["year"])) {
                $chosenYear = filter_input(INPUT_GET, "year", FILTER_VALIDATE_INT);
                if (!$chosenYear) {
                    throw new InvalidArgumentException("Wrong year parameter!");
                }
            } else {
                // Sets highest year in the array
                $chosenYear = $years[0];
            }

            // Counts all tickets count for chart
            $countCreatedTicketsByMonths = $this->countMonthlyTicketsByParameter("created_date", $allTicketsData, $chosenYear);
            $countSolvedTicketsByMonths  = $this->countMonthlyTicketsByParameter("closed_date", $allTicketsData, $chosenYear);

            if ($panel === "admin") {
                // Counts tickets handled by the admin for the chart
                $countHandledCreatedTicketsByMonths = $this->countMonthlyTicketsByParameter("created_date", $handledTicketsData, $chosenYear);
                $countHandledSolvedTicketsByMonths  = $this->countMonthlyTicketsByParameter("closed_date", $handledTicketsData, $chosenYear);
            }

            // Formats all tickets data for chart
            $foramtedAllData = formatMonthlyDataForChartjs($countCreatedTicketsByMonths, $countSolvedTicketsByMonths);
            $chartAllData["labels"] = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Avg', 'Sep', 'Oct', 'Nov', 'Dec'];
            $chartAllData["datasets"][0] = ["label" => "Opened", "data" => $foramtedAllData["opened"]];
            $chartAllData["datasets"][1] = ["label" => "Closed", "data" => $foramtedAllData["closed"]];

            if ($panel === "admin") {
                // Formats chart data for tickets handled by the admin
                $foramtedHandledData = formatMonthlyDataForChartjs($countHandledCreatedTicketsByMonths, $countHandledSolvedTicketsByMonths);
                $chartHandledData["labels"] = $chartAllData["labels"];
                $chartHandledData["datasets"][0] = ["label" => "Opened", "data" => $foramtedHandledData["opened"]];
                $chartHandledData["datasets"][1] = ["label" => "Closed", "data" => $foramtedHandledData["closed"]];
            }

            // Prepares data for departments stats table
            $arrayTables["Tickets by deparmants"] = $this->countDataForDashboardTable($allTicketsData, $departments, "department_name");

            // // Prepares data for statuses stats table
            $arrayTables["Tickets by statuses"] = $this->countDataForDashboardTable($allTicketsData, $statuses, "status_name");

            // Prepares data for priority stats table
            $arrayTables["Tickets by priorities"] = $this->countDataForDashboardTable($allTicketsData, $priorities, "priority_name");

            // Creates closed tickets by closing type table if there are closed tickets
            if (isset($closedTicketsCount) && $closedTicketsCount > 0) {
                // Creates array of closed tickets and format it for countDataForDashboardTable() method
                $closedTickets = [];
                foreach ($allTicketsData as $ticket) {
                    if ($ticket["status_name"] === "closed") {
                        $closedTickets[] = $ticket["closing_type"];
                    }
                }

                $closedTicketsPerType = array_count_values($closedTickets);
                if (!isset($closedTicketsCount)) {
                    $closedTicketsCount = count($closedTickets);
                }

                // Prepares data for priority stats table
                $arrayTables["Closed tickets by closing type"] = [];
                foreach ($closedTicketsPerType as $type => $total) {
                    $percentage = countPercentage($total, $closedTicketsCount);
                    $arrayTables["Closed tickets by closing type"][] = [ucfirst($type), $total, $percentage];
                }
            }

            // Prepares data for departments chart
            $chartDepartmentdData = preparePieBarChartData($arrayTables["Tickets by deparmants"]);

            // Gets all users for filtering tickets by users
            $allUsers = $this->userModel->getAllUsers();
            unset($this->userModel);

            if ($panel === "admin") {
                // Prepares data for tickets by creators stats table
                $creatorsChartAndTableData = prepareChartAndTableDataByFilterAndUser($allTicketsData, $allUsers, "created_by");
                $arrayTables["Tickets by creators"] = $creatorsChartAndTableData["table_data"];
            }

            // Removes tickets not assigned to an admin form $allTicketsData
            foreach ($allTicketsData as $key => $value) {
                if ($allTicketsData[$key]["handled_by"] === NULL) {
                    unset($allTicketsData[$key]);
                }
            }

            // Prepares data for tickets handled by admins stats table and chart
            $adminChartAndTableData = prepareChartAndTableDataByFilterAndUser($allTicketsData, $allUsers, "handled_by");
            // unset($allTicketsData); // Unsets array of all tickets data
            unset($allUsers);       // Unsets array of all users data
            $labelForTicketsPerAdmins = $panel === "admin" ? "Tickets handled by admins" : "Admins who work(ed) on your tickets";
            $arrayTables[$labelForTicketsPerAdmins] = $adminChartAndTableData["table_data"]; // Data for table
            $chartPerAdminData                      = $adminChartAndTableData["chart_data"]; // Data for chart
        }

        $returnArray = [
            "countAllTickets" => $countAllTickets,
            "countAllInProgressTickets" => $countAllInProgressTickets,
            "countAllSolvedTickets" => $countAllSolvedTickets,
            "countAllWaitingTickets" => $countAllWaitingTickets,
        ];

        if ($countAllTickets > 0) {
            $returnArray = [
                "years" => $years,
                "chartAllData" => $chartAllData,
                "arrayTables" => $arrayTables,
                ...$returnArray
            ];
        }

        if ($panel === "admin") {
            $returnArray = [
                "countHandledTickets" => $countHandledTickets,
                "countHandledInProgressTickets" => $countHandledInProgressTickets,
                "countHandledSolvedTickets" => $countHandledSolvedTickets,
                "chartHandledData" => $chartHandledData,
                "handledTicketsData" => $handledTicketsData,
                "closedTicketsCount" => $closedTicketsCount,
                "spitTicketsCount" => $spitTicketsCount,
                "reopenedTickets" => $reopenedTickets,
                "formatedTime" => $formatedTime,
                "chartPerAdminData" => $chartPerAdminData,
                "chartDepartmentdData" => $chartDepartmentdData,
                ...$returnArray
            ];
        }

        return $returnArray;
    }

    /**
     * Counts tickets' statuses.
     *
     * Expects each ticket to contain a 'status_name' field with one of the expected values:
     * - "in progress"
     * - "closed"
     * - "waiting"
     *
     * Returns an array with the following structure:
     * [
     *     "in_progress" => int, 
     *     "closed"      => int, 
     *     "waiting"     => int,  
     *     "all":        => int, 
     * ]
     *
     * @param array $tickets Array of tickets with status data
     * @return array Associative array with status counts
     */
    private function countStatuses(array $tickets): array
    {
        $counts = ['in_progress' => 0, 'closed' => 0, 'waiting' => 0, 'all' => count($tickets)];

        foreach ($tickets as $values) {
            // Counts tickets with status `in progress` 
            if ($values["status_name"] === "in progress") {
                $counts['in_progress']++;
            }

            // Counts tickets with status `closed`
            if ($values["status_name"] === "closed") {
                $counts['closed']++;
            }

            // Counts tickets with status `closed`
            if ($values["status_name"] === "waiting") {
                $counts['waiting']++;
            }
        }

        return $counts;
    }

    /**
     * Counts tickets received from `getMonthlyTicketsByParameter` and groupes them by months.
     * Returns an array formatted like: 
     * [
     *    "Jan" => [
     *        "parameter_name" => int
     *    ],
     *    "Feb" => [
     *        "parameter_name" => int 
     *    ],
     *    // ... rest of the months
     * ]
     * 
     * @param string $param Parameter name that exists as a key in each ticket returned by the `fetchAllTickets` method.
     * @param array $allTicketsData The array of all tickets returned by the `fetchAllTickets` method.
     * @param int $year The year to filter tickets by.
     * 
     * @return array Array with month abbreviations as keys.
     *     Each month key maps specified parameter name as a key and integer as value.
     */
    private function countMonthlyTicketsByParameter(string $param, array $allTicketsData, int $year): array
    {
        $counts = [];
        $tickets = $this->getMonthlyTicketsByParameter($param, $allTicketsData, $year);
        foreach ($tickets as $month => $arraysByParamNames) {
            if (empty($arraysByParamNames)) {
                $counts[$month][$param] = 0;
            }
            foreach ($arraysByParamNames as $tickets) {
                $counts[$month][$param] = count($tickets);
            }
        }

        return $counts;
    }

    /**
     * Counts data by a filter for a dashboard table.
     * Returns array in format: 
     *  [
     *      ["FilterNameValue1", int], ["FilterNameValue2", int], ["FilterNameValue3", int], ...
     *  ] 
     * 
     * @param array $data Array of that returned by `fetchAllTickets` method.
     * @param array $filters List of filter name strings. The list contains possible values 
     *              those $ticketFilter parametr can have (e.g. "Human Resources", "Marketing", etc.).
     * @param string $ticketFilter Key in a single ticket array from $data to 
     *               group tickets by (e.g. "department_name", "status_name", etc.).
     * 
     * @return array Array of array triplets: [filter name, ticket count for the filter, percentage of filtered tickets in total tickets].
     * Example: 
     *  [
     *      ["Human Resources", 23, 11.5], 
     *      ["Marketing", 4, 2], 
     *      ["Sales", 186, 9.3], 
     *      ["Information Technology", 3, 1.5], 
     *   ...
     *  ]
     */
    private function countDataForDashboardTable(array $data, array $filters, string $ticketFilter): array
    {
        $ticketsByFilters = [];
        // Prepares array of tickets sorted by appropriate filters
        foreach ($data as $ticket) {
            foreach ($filters as $filterName) {
                if ($ticket[$ticketFilter] === null) continue;
                if (str_contains(haystack: $ticket[$ticketFilter], needle: $filterName)) {
                    $ticketsByFilters[$filterName][] = $ticket;
                    break; // stop looping filters when matched
                }
            }
        }

        $totalTickets = count($data);
        $countTicketsByFilters = [];
        for ($i = 0; $i < count($filters); $i++) {
            foreach ($ticketsByFilters as $name => $_) {
                if (str_contains(haystack: $name, needle: $filters[$i])) {
                    $totalByFilter = count($ticketsByFilters[$filters[$i]]);
                    $countTicketsByFilters[] = [
                        ucfirst($filters[$i]),
                        $totalByFilter,
                        countPercentage($totalByFilter, $totalTickets)
                    ];
                    break;
                }
            }
        }

        return $countTicketsByFilters;
    }

    /**
     * Gets an array of tickets filtered by a given parameter and year, grouped by months.
     * 
     * Returns an array formatted like: 
     * [
     *    ["Jan" => [
     *        "parameter_name" => array  // Contains values of any type (int, string, bool, null, etc.) 
     *    ],
     *    ["Feb" => [
     *        "parameter_name" => array  // Contains values of any type (int, string, bool, null, etc.)  
     *    ],
     *    // ... rest of the months
     * ]
     * 
     * @param string $param Parameter name that exists as a key in each ticket returned by the fetchAllTickets() method.
     * @param array $allTicketsData The array of all tickets returned by the `fetchAllTickets` method.
     * @param int $year The year to filter tickets by.
     * 
     * @return array Array with month abbreviations as keys.
     *     Each month key maps to an array of values of mixed types (int, string, bool, null)
     *     corresponding to the specified parameter.
     */
    private function getMonthlyTicketsByParameter(string $param, array $allTicketsData, int $year): array
    {
        $months = [
            'Jan' => '01',
            'Feb' => '02',
            'Mar' => '03',
            'Apr' => '04',
            'May' => '05',
            'Jun' => '06',
            'Jul' => '07',
            'Avg' => '08',
            'Sep' => '09',
            'Oct' => '10',
            'Nov' => '11',
            'Dec' => '12',
        ];

        // Prepares empty array buckets to prevent undefined keys
        $monthsData = [];
        foreach ($months as $monthName => $_) {
            $monthsData[$monthName] = [];
        }

        // Fills buckets with tickets grouped by month
        foreach ($allTicketsData as $ticket) {
            foreach ($months as $monthName => $MonthNumber) {
                if ($ticket[$param] === null) continue;
                if (str_contains(haystack: $ticket[$param], needle: "{$year}-{$MonthNumber}-")) {
                    $monthsData[$monthName][$param][] = $ticket;
                    break; // stop looping months when matched
                }
            }
        }

        return $monthsData;
    }
}
