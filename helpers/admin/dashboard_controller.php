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

$year = 2025;
$countCreatedTicketsByMonths = Ticket::countMonthlyTicketsByParameter("created_date", $allTicketsData, $year);
$countSolvedTicketsByMonths  = Ticket::countMonthlyTicketsByParameter("closed_date", $allTicketsData, $year);

// Formats data for the chart
$rawData   = [$countCreatedTicketsByMonths, $countSolvedTicketsByMonths];
$opened    = [];
$closed    = [];
$chartData = [];
for ($i = 0; $i < count($rawData); $i++) {
  foreach ($rawData[$i] as $month => $filteredMonthlyTickets) {
    foreach ($filteredMonthlyTickets as $filter => $numberOfTickets) {
      if ($filter === "created_date") {
        $opened[] = $numberOfTickets;
      } elseif ($filter === "closed_date") {
        $closed[] = $numberOfTickets;
      }
    }
  }
}

$chartData["labels"] = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Avg', 'Sep', 'Oct', 'Nov', 'Dec'];
$chartData["datasets"][0] = ["label" => "Opened", "data" => $opened];
$chartData["datasets"][1] = ["label" => "Closed", "data" => $closed];
?>