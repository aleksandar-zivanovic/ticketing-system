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

// Checks if a visitor is logged in.
requireLogin();

// Initializes allowed filter values for tickets
$allTicketFilterData = loadTicketFilterData();
$statuses     = $allTicketFilterData["statuses"];
$priorities   = $allTicketFilterData["priorities"];
$departments  = $allTicketFilterData["departments"];
$ticket       = new Ticket();
$closingTypes = $ticket->closingTypes;

// Sets allowed values list for fetchAllTickets() method
$allowedValues = buildAllowedTicketValues($allTicketFilterData);

// Calls fetchAllTickets() method
if ($panel === "admin") {
    $allTicketsData = $ticket->fetchAllTickets(allowedValues: $allowedValues, images: false);
    $handledTicketsData = $ticket->fetchAllTickets(allowedValues: $allowedValues, images: false, handledByMe: true);
} else {
    $allTicketsData = $ticket->fetchAllTickets(allowedValues: $allowedValues, images: false, userId: cleanString($_SESSION["user_id"]));
}
unset($ticket);

// Removes split tickets from $allTicketsData and counts split and reopened tickets separately
$spitTicketsCount = 0;
$reopenedTickets  = 0;
foreach ($allTicketsData as $key => $value) {
    if ($allTicketsData[$key]["closing_type"] === "split") {
        $spitTicketsCount++;
        unset($allTicketsData[$key]);
        continue;
    }

    // Count reopened tickets. Doesn't count split tickets.
    if ($allTicketsData[$key]["closing_type"] !== "split" && $allTicketsData[$key]["was_reopened"] === 1) {
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
        $allSecondsPerTicket = $solvingTimeTotal / $closedTicketsCount;

        $days    = floor($allSecondsPerTicket / 86400);
        $hours   = floor(($allSecondsPerTicket % 86400) / 3600);
        $minutes = floor(($allSecondsPerTicket % 3600) / 60);

        $formatedTime = $days > 0 ? "$days d $hours h $minutes m" : "$hours h $minutes m";
    }
}

// Counts all existing tickets and their statuses
$allTicketsCountStatuses   = Status::countStatuses($allTicketsData);
$countAllTickets           = $allTicketsCountStatuses["all"];
$countAllInProgressTickets = $allTicketsCountStatuses["in_progress"];
$countAllSolvedTickets     = $allTicketsCountStatuses["closed"];
$countAllWaitingTickets    = $allTicketsCountStatuses["waiting"];

// Counts percentage of tickets per status
$percInProgressTickets     = countPercentage($countAllInProgressTickets, $countAllTickets);
$percSolvedTickets         = countPercentage($countAllSolvedTickets, $countAllTickets);
$percWaitingTickets        = countPercentage($countAllWaitingTickets, $countAllTickets);

if ($panel === "admin" || ($panel === "user" && $countAllTickets > 0)) {

    if ($panel === "admin") {
        // Counts tickets handled by the admin by status
        $handledTicketsCountStatuses   = Status::countStatuses($handledTicketsData);
        $countHandledTickets           = $handledTicketsCountStatuses["all"];
        $countHandledInProgressTickets = $handledTicketsCountStatuses["in_progress"];
        $countHandledSolvedTickets     = $handledTicketsCountStatuses["closed"];
    }

    // Prepare data for dropdown button
    $year = new Year();
    $years = array_reverse($year->getAllYears());
    unset($year);
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
    $arrayTables["Tickets by deparmants"] = Ticket::countDataForDashboardTable($allTicketsData, $departments, "department_name");

    // // Prepares data for statuses stats table
    $arrayTables["Tickets by statuses"] = Ticket::countDataForDashboardTable($allTicketsData, $statuses, "status_name");

    // Prepares data for priority stats table
    $arrayTables["Tickets by priorities"] = Ticket::countDataForDashboardTable($allTicketsData, $priorities, "priority_name");

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
    $user = new User();
    $allUsers = $user->getAllUsers();
    unset($user);

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
    unset($allTicketsData); // Unsets array of all tickets data
    unset($allUsers);       // Unsets array of all users data
    $labelForTicketsPerAdmins = $panel === "admin" ? "Tickets handled by admins" : "Admins who work(ed) on your tickets";
    $arrayTables[$labelForTicketsPerAdmins] = $adminChartAndTableData["table_data"]; // Data for table
    $chartPerAdminData                      = $adminChartAndTableData["chart_data"]; // Data for chart
}
