<?php
if (!empty($parent)) {
    $infoMessage = "Ticket is split on {$date}  by {$adminFirstName} {$adminSecondName}.";
}

if (!empty($child)) {
    $infoMessage = "Ticket is created during splitting ticket:";
    $infoUrl     = '<a href="/ticketing-system/user/user-view-ticket.php?ticket=' . $parentTicket[0]["id"] . '" class="font-bold text-blue-600 hover:text-blue-700" target="_blank">' . ucfirst($parentTicket[0]["title"]) . '</a>';
}

$stringLenght = strlen($infoMessage);
$hyphens      = str_repeat("-", $stringLenght + ceil($stringLenght / 10));
?>
<div class="border-2 border-red-600 bg-yellow-100 mt-2 p-2 text-center truncate">
    <?php
    echo $hyphens . "<br>";
    echo $infoMessage . "<br>";
    if (!empty($child)) {
        echo $infoUrl . "<br>";
    }
    echo $hyphens . "<br>";
    ?>
    <?php if (!empty($parent)) { ?>
        Tickets created by splitting this ticket:<br>
        <ul>
            <?php
            foreach ($childrenTickets as $ticket) :
            ?>
                <li>
                    <a href="/ticketing-system/user/user-view-ticket.php?ticket=<?= $ticket["id"] ?>" class="font-bold text-blue-600 hover:text-blue-700" target="_blank"><?= ucfirst($ticket["title"]) ?></a>
                </li>
            <?php
            endforeach;
            ?>
        </ul>
    <?php
    }
    ?>
</div>