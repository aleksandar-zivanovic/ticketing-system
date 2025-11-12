<header class="card-header">
    <p class="card-header-title text-xl">
        Additional ticket statistics:
    </p>
</header>
<div class="grid gap-6 grid-cols-1 md:grid-cols-2 m-6 mt-1">
    <?php
    renderDashboardCard("Average ticket resolution time", $formatedTime, "text-yellow-500", "mdi-timer-check-outline");
    ?>
    <?php if ($spitTicketsCount > 0 || $reopenedTickets > 0) : ?>
        <div class="grid gap-6 grid-cols-1 md:grid-cols-2">
            <?php
            if ($spitTicketsCount > 0) {
                renderDashboardCard("Split", $spitTicketsCount, "text-gray-500", "mdi-table-split-cell");
            }

            if ($reopenedTickets > 0) {
                renderDashboardCard("Reopened", $reopenedTickets, "text-gray-500", "mdi-lock-open-variant-outline");
            }
            ?>
        </div>
    <?php endif; ?>
</div>