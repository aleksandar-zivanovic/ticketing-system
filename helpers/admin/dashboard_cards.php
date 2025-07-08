<header class="card-header">
    <p class="card-header-title text-xl">
        <?php
        if ($cardGroup === "all") {
            // Prepares displaying data for all tickets
            echo "All tickets:";
            $ticketsTotal      = $countAllTickets;
            $ticketsProcessing = $countAllInProgressTickets;
            $ticketsSolved     = $countAllSolvedTickets;
        } elseif ($cardGroup === "handling") {
            // Prepares displaying data for tickets handled by the admin
            echo "Tickets you handle:";
            $ticketsTotal      = $countHandledTickets;
            $ticketsProcessing = $countHandledInProgressTickets;
            $ticketsSolved     = $countHandledSolvedTickets;
        }
        ?>
    </p>
</header>

<div class="grid gap-6 grid-cols-1 md:grid-cols-4 mb-6">

    <?php
    renderDashboardCard("Tickets (total)", $ticketsTotal, "text-orange-500", "mdi-ticket");
    renderDashboardCard("Processing", $ticketsProcessing, "text-blue-500", "mdi-ticket-confirmation-outline ");
    renderDashboardCard("Solved", $ticketsSolved, "text-green-500", "mdi-ticket-confirmation ");
    if ($cardGroup === "all") {
        renderDashboardCard("Waiting", $countAllWaitingTickets, "text-red-500", "mdi-ticket-outline ");
    }
    ?>

</div>