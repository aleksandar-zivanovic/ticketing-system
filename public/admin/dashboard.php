<?php require_once '../../helpers/admin/dashboard_controller.php'; ?>

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
      renderChart("Tickets you are handling", "line", $chartData);
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