<?php
session_start();
require_once '../../classes/Ticket.php';
require_once '../../classes/Department.php';
require_once '../../classes/Priority.php';
require_once '../../classes/Status.php';
require_once '../../helpers/functions.php';
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
$allTicketsData = $ticket->fetchAllTickets(allowedValues: $allowedValues, images: false);
$handledTicketsData = $ticket->fetchAllTickets(allowedValues: $allowedValues, images: false, handledByMe: true);

// Counts all existing tickets and their statuses
$allTicketsCountStatuses   = Status::countStatuses($allTicketsData);
$countAllTickets           = $allTicketsCountStatuses["all"];
$countAllInProgressTickets = $allTicketsCountStatuses["in_progress"];
$countAllSolvedTickets     = $allTicketsCountStatuses["closed"];
$countAllWaitingTickets    = $allTicketsCountStatuses["waiting"];;

// Counts tickets handled by the admin and their statuses
$handledTicketsCountStatuses   = Status::countStatuses($handledTicketsData);
$countHandledTickets           = $handledTicketsCountStatuses["all"];
$countHandledInProgressTickets = $handledTicketsCountStatuses["in_progress"];
$countHandledSolvedTickets     = $handledTicketsCountStatuses["closed"];

// TODO: Make dropdown button for $year value
$year = 2025;

// Counts all tickets count for chart
$countCreatedTicketsByMonths = Ticket::countMonthlyTicketsByParameter("created_date", $allTicketsData, $year);
$countSolvedTicketsByMonths  = Ticket::countMonthlyTicketsByParameter("closed_date", $allTicketsData, $year);

// Counts tickets handled by the admin for the chart
$countHandledCreatedTicketsByMonths = Ticket::countMonthlyTicketsByParameter("created_date", $handledTicketsData, $year);
$countHandledSolvedTicketsByMonths  = Ticket::countMonthlyTicketsByParameter("closed_date", $handledTicketsData, $year);

// Formats all tickets data for chart
$foramtedAllData = formatDataForChartjs($countCreatedTicketsByMonths, $countSolvedTicketsByMonths);
$chartAllData["labels"] = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Avg', 'Sep', 'Oct', 'Nov', 'Dec'];
$chartAllData["datasets"][0] = ["label" => "Opened", "data" => $foramtedAllData["opened"]];
$chartAllData["datasets"][1] = ["label" => "Closed", "data" => $foramtedAllData["closed"]];

// Formats chart data for tickets handled by the admin
$foramtedHandledData = formatDataForChartjs($countHandledCreatedTicketsByMonths, $countHandledSolvedTicketsByMonths);
$chartHandledData["labels"] = $chartAllData["labels"];
$chartHandledData["datasets"][0] = ["label" => "Opened", "data" => $foramtedHandledData["opened"]];
$chartHandledData["datasets"][1] = ["label" => "Closed", "data" => $foramtedHandledData["closed"]];

// Prepares data for departments stats table
$departmen = new Department();
$departmentNames = $departmen->getAllDepartmentNames();
$arrayTables["Tickets by deparmants"] = Ticket::countDataForDashboardCard($allTicketsData, $departmentNames, "department_name");

// Prepares data for statuses stats table
$countAllInProgressTickets = $allTicketsCountStatuses["in_progress"];
$countAllSolvedTickets     = $allTicketsCountStatuses["closed"];
$countAllWaitingTickets    = $allTicketsCountStatuses["waiting"];

$arrayTables["Tickets by status"] = [
    ["Waiting", $countAllWaitingTickets], 
    ["In progres", $countAllInProgressTickets], 
    ["Solved", $countAllSolvedTickets], 
];

// Prepares data for urgent stats table
$priority = new Priority();
$priorityNames = $priority->getAllPriorityNames();
$arrayTables["Tickets by priority"] = Ticket::countDataForDashboardCard($allTicketsData, $priorityNames, "priority_name");
?>