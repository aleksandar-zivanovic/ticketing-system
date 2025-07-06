<header class="card-header">
    <p class="card-header-title text-xl">
        <?php
        if ($cardGroup === "all") {
            echo "All tickets:";
        } elseif ($cardGroup === "handling") {
            echo "Tickets you handle:";
        }
        ?>
    </p>
</header>

<?php // TODO: fetch data from the DB 
?>
<div class="grid gap-6 grid-cols-1 md:grid-cols-4 mb-6">

    <?php
    renderDashboardCard("Tickets (total)", 525, "text-orange-500", "mdi-ticket");
    renderDashboardCard("Processing", 20, "text-blue-500", "mdi-ticket-confirmation-outline ");
    renderDashboardCard("Solved", 470, "text-green-500", "mdi-ticket-confirmation ");
    if (isset($cardGroup) && $cardGroup === "all") {
        renderDashboardCard("Waiting", 45, "text-red-500", "mdi-ticket-outline ");
    }
    ?>

</div>