<header class="card-header">
    <p class="card-header-title text-xl"><?= $label ?></p>
</header>

<div class="grid gap-6 grid-cols-1 md:grid-cols-4 m-6 mt-1">

    <?php
    renderDashboardCard("Tickets (total)", $total, "text-orange-500", "mdi-ticket");
    renderDashboardCard("Processing", $processing, "text-blue-500", "mdi-ticket-confirmation-outline ");
    renderDashboardCard("Solved", $solved, "text-green-500", "mdi-ticket-confirmation ");
    if ($waiting !== null) {
        renderDashboardCard("Waiting", $waiting, "text-red-500", "mdi-ticket-outline ");
    }
    ?>

</div>