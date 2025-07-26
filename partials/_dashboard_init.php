<?php
require_once '../../helpers/admin/dashboard_controller.php';
require_once '../../config/features-config.php';
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
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

</head>

<body>
    <div id="app">
        <?php

        // import header navigation bar
        include_once '../../partials/_navigation-bar.php';

        // import side menu
        include_once '../../partials/_side-menu.php';

        // import breadcrumbs
        include_once '../../partials/_navigation-breadcrumbs.php';

        include_once '../../partials/_dashboard_hero.php';
        ?>
        <?php
        // Statistic for all tickets
        $cardGroup = "all";
        require '../../helpers/admin/dashboard_cards.php';

        if ($panel === "admin") {
            // Statistic for tickets handled by the administrator
            $cardGroup = "handling";
            require '../../helpers/admin/dashboard_cards.php';
        }
        ?>

        <!-- Dropdown button -->
        <section class="is-hero-bar">
            <div class="flex flex-col md:flex-row items-center justify-end space-y-6 md:space-y-0">
                <div class="pr-5 text-xl font-medium text-gray-900">
                    Select year for the chart<?= $panel === "admin" ? "s" : "" ?>:
                </div>
                <form action="">
                    <select name="year" id="year_drop_down" class='p-2 text-xl' onchange="this.form.submit()">
                        <optgroup label="Choose year:">
                            <?php
                            foreach ($years as $singleYear) {
                                echo "<option value='{$singleYear}' " . addSelectedTag("year", $singleYear) . ">" . $singleYear . "</option>";
                            }
                            ?>
                        </optgroup>
                    </select>
                </form>
            </div>
        </section>

        <!-- Charts -->
        <?php
        $commonChartLabel = $panel === "admin" ? "All tickets chart" : "Your tickets chart";
        renderChart($commonChartLabel, "line", $chartAllData);
        if ($panel === "admin") {
            renderChart("Tickets you are handling", "line", $chartHandledData);
        }
        ?>
        <div class="card has-table grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
            <?php
            renderChart("Tickets per department", TICKETS_PER_DEPARTMENT_CHART_TYPE, $chartDepartmentdData);
            renderChart("Tickets per admin", TICKETS_PER_ADMIN_CHART_TYPE, $chartPerAdminData);
            ?>
        </div>

        <!-- Tables -->
        <div class="card has-table grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
            <?php
            foreach ($arrayTables as $category  => $items) {
                $pieces = explode(' ', $category);
                $columnName = array_pop($pieces);
                include '../../partials/_admin_dashboard_table.php';
            }
            ?>
            </section>
        </div>

        <?php
        // import footer
        include_once '../../partials/_footer.php';
        ?>

</body>

</html>