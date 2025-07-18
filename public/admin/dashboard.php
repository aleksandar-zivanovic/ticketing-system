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

<!DOCTYPE html>
<html lang="en" class="">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $page ?></title>

  <link rel="stylesheet" href="../css/admin-one-main.css">
  <!-- Tailwind is included -->
  <link rel="stylesheet" href="../css/tailwind-output.css">
  <link rel="stylesheet" href="../css/font-awesome.min.css">
  <!-- Chart.js-->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

  <div id="app">

    <?php
    $panel = "admin";
    // import header navigation bar
    include_once '../../partials/_navigation-bar.php';

    // import side menu
    include_once '../../partials/_side-menu.php';

    // import breadcrumbs
    include_once '../../partials/_navigation-breadcrumbs.php';

    include_once '../../partials/_dashboard_hero.php';
    ?>

    <section class="section main-section">
      <?php
      // Statistic for all tickets
      $cardGroup = "all";
      require '../../helpers/admin/dashboard_cards.php';

      // Statistic for tickets handled by the administrator
      $cardGroup = "handling";
      require '../../helpers/admin/dashboard_cards.php';
      ?>

      <!-- Charts -->
      <?php 
      renderChart("All tickets chart", "line", $chartData);
      ?>

      <!-- Tables -->
      <div class="card has-table grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        <?php
        // TODO: fetch the data from DB
        $arrayTables = [
          "Deparmants" => [
            ["Dep1", 6],
            ["Dep2", 2],
            ["Dep3", 36],
          ],
          "Tickets by users" => [
            ["User1", 16],
            ["User2", 2],
            ["User3", 86],
            ["User4", 23],
            ["User5", 1],
          ],
          "Statuses" => [
            ["Status1", 0],
            ["Status2", 2],
            ["Status3", 86],
          ],
        ];


        foreach ($arrayTables as $category  => $items) {
          include '../../partials/_admin_dashboard_table.php';
        }
        ?>
    </section>

    <?php

    // import footer
    include_once '../../partials/_footer.php';
    ?>
  </div>

</body>

</html>