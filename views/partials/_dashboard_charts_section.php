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
                        echo "<option value='{$singleYear}' " . addSelectedTag($singleYear, "year") . ">" . $singleYear . "</option>";
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
<div class="card has-table grid grid-cols-1 gap-6 lg:grid-cols-2 m-6">
    <?php
    if (!empty($chartDepartmentdData["datasets"][0]["data"])) {
        renderChart("Tickets per department", TICKETS_PER_DEPARTMENT_CHART_TYPE, $chartDepartmentdData);
    }

    if (!empty($chartPerAdminData["datasets"][0]["data"])) {
        renderChart("Tickets per admin", TICKETS_PER_ADMIN_CHART_TYPE, $chartPerAdminData);
    }
    ?>
</div>