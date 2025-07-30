<?php
$lastMessage = end($allMessages);

foreach ($allMessages as $message):

    // Determinate whether the message creator is also the ticket creator or not
    $messageCreator = $message["user"] === $theTicket["created_by"] ? "ticketCreator" : 
        ($message["user"] === $theTicket["handled_by"] ? "ticketAdmin" : "otherAdmin");
    $position = $messageCreator === "ticketCreator" ? "start" : "end";

    // Selects the message background color by a user's role in the ticket
    switch ($messageCreator) {
        case "ticketCreator":
            $background = "bg-yellow-100";
            break;
        case "ticketAdmin":
            $background = "bg-blue-100";
            break;
        default:
            $background = "bg-green-100";
            break;
    }
?>
    <!-- Message -->
    <div class="w-full p-3 text-lg my-4 flex flex-col items-start">
        <div class="self-<?= $position ?> font-thin text-sm font-mono mb-2">
            <?= $message["created_at"] . " " ?> <span class="text-blue-600"><?= $message["creator_name"] . " " . $message["creator_surname"] ?></span> 
        </div>
        <div class="w-5/6 <?= $background ?> p-5 self-<?= $position ?> rounded-xl">
            <div class="w-full">
                <?php 
                // Message text
                echo $message["body"];
                
                // Editing message
                if (
                    $theTicket["statusId"] !== 3 && 
                    $lastMessage["id"] === $message["id"] && 
                    $message["user"] === $_SESSION['user_id']
                ) {
                    require_once '_message-edit-button.php';
                }
                ?>
            </div>
        </div>
    </div>

    <div>
    <?php
    // Adds attachment files
    if ($message['file']) {
        $attachments = explode(',', $message['file']);

        foreach ($attachments as $attachment) {
    ?>
        <a class="m-2 inline-block" href='../img/ticket_images/<?= $attachment ?>' target="_blank"><img width='150' src='../img/ticket_images/<?= $attachment ?>'/></a>
    
    <?php
        } // closing attachment foreach
    } // closing attachment if
    ?>
    </div>
    <hr>

<?php endforeach; // closing main foreach ?>