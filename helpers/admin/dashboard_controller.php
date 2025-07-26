<?php
session_start();
require_once '../../classes/Ticket.php';
require_once '../../classes/Department.php';
require_once '../../classes/Priority.php';
require_once '../../classes/Status.php';
require_once '../../classes/User.php';
require_once '../../classes/Year.php';
require_once '../../helpers/functions.php';
require_once '../../helpers/chart_helpers.php';
$page = "Dashboard";

// Initializes allowed filter values for tickets
$allTicketFilterData = loadTicketFilterData();
$statuses    = $allTicketFilterData["statuses"];
$priorities  = $allTicketFilterData["priorities"];
$departments = $allTicketFilterData["departments"];

// Sets allowed values list for fetchAllTickets() method
$allowedValues = buildAllowedTicketValues($allTicketFilterData);

// Calls fetchAllTickets() method
$ticket = new Ticket();
if ($panel === "admin") {
    $allTicketsData = $ticket->fetchAllTickets(allowedValues: $allowedValues, images: false);
    $handledTicketsData = $ticket->fetchAllTickets(allowedValues: $allowedValues, images: false, handledByMe: true);
    unset($ticket);
} else {
    $allTicketsData = $ticket->fetchAllTickets(allowedValues: $allowedValues, images: false, userId: cleanString($_SESSION["user_id"]));
}

// Counts all existing tickets and their statuses
$allTicketsCountStatuses   = Status::countStatuses($allTicketsData);
$countAllTickets           = $allTicketsCountStatuses["all"];
$countAllInProgressTickets = $allTicketsCountStatuses["in_progress"];
$countAllSolvedTickets     = $allTicketsCountStatuses["closed"];
$countAllWaitingTickets    = $allTicketsCountStatuses["waiting"];;

if ($panel === "admin") {
    // Counts tickets handled by the admin and their statuses
    $handledTicketsCountStatuses   = Status::countStatuses($handledTicketsData);
    $countHandledTickets           = $handledTicketsCountStatuses["all"];
    $countHandledInProgressTickets = $handledTicketsCountStatuses["in_progress"];
    $countHandledSolvedTickets     = $handledTicketsCountStatuses["closed"];
}

// Prepare data for dropdown button
$year = new Year();
$years = array_reverse($year->getAllYears());
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
$countCreatedTicketsByMonths = Ticket::countMonthlyTicketsByParameter("created_date", $allTicketsData, $chosenYear);
$countSolvedTicketsByMonths  = Ticket::countMonthlyTicketsByParameter("closed_date", $allTicketsData, $chosenYear);

if ($panel === "admin") {
    // Counts tickets handled by the admin for the chart
    $countHandledCreatedTicketsByMonths = Ticket::countMonthlyTicketsByParameter("created_date", $handledTicketsData, $chosenYear);
    $countHandledSolvedTicketsByMonths  = Ticket::countMonthlyTicketsByParameter("closed_date", $handledTicketsData, $chosenYear);
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
$department = new Department();
$departmentNames = $department->getAllDepartmentNames();
unset($department);

$arrayTables["Tickets by deparmants"] = Ticket::countDataForDashboardTable($allTicketsData, $departmentNames, "department_name");

// Prepares data for departments chart
$chartDepartmentdData = preparePieBarChartData($arrayTables["Tickets by deparmants"]);

// Prepares data for statuses stats table
$countAllInProgressTickets = $allTicketsCountStatuses["in_progress"];
$countAllSolvedTickets     = $allTicketsCountStatuses["closed"];
$countAllWaitingTickets    = $allTicketsCountStatuses["waiting"];

$arrayTables["Tickets by statuses"] = [
    ["Waiting", $countAllWaitingTickets],
    ["In progres", $countAllInProgressTickets],
    ["Solved", $countAllSolvedTickets],
];

// Prepares data for priority stats table
$priority = new Priority();
$priorityNames = $priority->getAllPriorityNames();
unset($priority);
$arrayTables["Tickets by priorities"] = Ticket::countDataForDashboardTable($allTicketsData, $priorityNames, "priority_name");


$user = new User();
$allUsers = $user->getAllUsers();
unset($user);

if ($panel === "admin") {
    // Prepares data for tickets by creators stats table
    $creatorsChartAndTableData = prepareChartAndTableDataByFilterAndUser($allTicketsData, $allUsers, "created_by");
    $arrayTables["Tickets by creators"] = $creatorsChartAndTableData["table_data"];
}

// Prepares data for tickets handled by admins stats table and chart
$adminChartAndTableData = prepareChartAndTableDataByFilterAndUser($allTicketsData, $allUsers, "handled_by");
unset($allTicketsData); // Unsets array of all tickets data
unset($allUsers);       // Unsets array of all users data
$labelForTicketsPerAdmins = $panel === "admin" ? "Tickets handled by admins" : "Admins who work(ed) on your tickets";
$arrayTables[$labelForTicketsPerAdmins] = $adminChartAndTableData["table_data"]; // Data for table
$chartPerAdminData                      = $adminChartAndTableData["chart_data"]; // Data for chart
?>
