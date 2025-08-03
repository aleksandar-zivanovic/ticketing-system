<header class="card-header">
    <p class="card-header-title text-xl">
        <?php
        if ($cardGroup === "all") {
            $cardData = [
                "label"      => "All tickets:",
                "total"      => $countAllTickets,
                "processing" => $countAllInProgressTickets,
                "solved"     => $countAllSolvedTickets,
                "waiting"    => $countAllWaitingTickets
            ];
        } elseif ($cardGroup === "handling") {
            $cardData = [
                "label"      => "Tickets you handle:",
                "total"      => $countHandledTickets,
                "processing" => $countHandledInProgressTickets,
                "solved"     => $countHandledSolvedTickets,
            ];
        }

        echo $cardData["label"];
        ?>
    </p>
</header>

<div class="grid gap-6 grid-cols-1 md:grid-cols-4 m-6 mt-1">

    <?php
    renderDashboardCard("Tickets (total)", $cardData["total"], "text-orange-500", "mdi-ticket");
    renderDashboardCard("Processing", $cardData["processing"], "text-blue-500", "mdi-ticket-confirmation-outline ");
    renderDashboardCard("Solved", $cardData["solved"], "text-green-500", "mdi-ticket-confirmation ");
    if ($cardGroup === "all") {
        renderDashboardCard("Waiting", $cardData["waiting"], "text-red-500", "mdi-ticket-outline ");
    }
    ?>

</div>