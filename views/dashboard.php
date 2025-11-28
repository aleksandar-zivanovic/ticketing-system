<?php
require_once ROOT . 'config' . DS . 'features-config.php';
?>

<!DOCTYPE html>
<html lang="en" class="">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page ?></title>

    <link rel="stylesheet" href="/ticketing-system/public/css/admin-one-main.css">
    <!-- Tailwind is included -->
    <link rel="stylesheet" href="/ticketing-system/public/css/tailwind-output.css">
    <link rel="stylesheet" href="/ticketing-system/public/css/font-awesome.min.css">
    <!-- Chart.js-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

</head>

<body>
    <div id="app">
        <?php
        // import header navigation bar
        require_once ROOT . 'views' . DS . 'partials' . DS . '_navigation_bar.php';

        // import side menu
        require_once ROOT . 'views' . DS . 'partials' . DS . '_side_menu.php';

        // import breadcrumbs
        require_once ROOT . 'views' . DS . 'partials' . DS . '_navigation_breadcrumbs.php';

        // import hero section
        require_once ROOT . 'views' . DS . 'partials' . DS . '_dashboard_hero.php';

        // Statistic for all tickets
        renderDashboardCardsRow(
            label: "All tickets:",
            total: $countAllTickets,
            processing: $countAllInProgressTickets,
            solved: $countAllSolvedTickets,
            waiting: $countAllWaitingTickets
        );

        if ($panel === "admin") {
            // Statistic for tickets handled by the administrator
            renderDashboardCardsRow(
                label: "Tickets you handle:",
                total: $countHandledTickets,
                processing: $countHandledInProgressTickets,
                solved: $countHandledSolvedTickets,
            );

            // Average ticket resolution time card
            if ($closedTicketsCount > 0) {
                require_once ROOT . 'views' . DS . 'partials' . DS . '_dashboard_additional_ticket_statistics_cards.php';
            }

            require_once ROOT . 'views' . DS . 'partials' . DS . '_dashboard_user_statistic_cards.php';
        }

        // Avoids rendering tables and chart for no tickets for users
        if ($panel === "admin" || ($panel === "user" && $countAllTickets > 0)) {

            // Charts section
            require_once ROOT . 'views' . DS . 'partials' . DS . '_dashboard_charts_section.php';

            // Tables section
            if ($countAllTickets > 0) :
        ?>
                <!-- Tables -->
                <div class="card has-table grid grid-cols-1 gap-6 lg:grid-cols-2 m-6">
                    <?php
                    foreach ($arrayTables as $category  => $items) {
                        // Prevents rendering tables without data
                        if (!empty($items)) {
                            $pieces = explode(' ', $category);
                            $columnName = array_pop($pieces);
                            include ROOT . 'views' . DS . 'partials' . DS . '_dashboard_table.php';
                        }
                    }
                    ?>
                    </section>
                </div>
        <?php
            endif; // End rendering tables.
        }

        // import footer
        require_once ROOT . 'views' . DS . 'partials' . DS . '_footer.php';
        ?>

</body>

</html>