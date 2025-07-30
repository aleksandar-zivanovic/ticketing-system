<section class="is-hero-bar">
    <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
        <?php if ($theTicket['id']): ?>
        <h1 class="title">Ticket ID: <?= $theTicket['id'] ?></h1>
        <?php endif; ?>
    </div>
</section>

<section class="section main-section">
    <?php if ($theTicket['id']) : ?>
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-account-multiple"></i></span>
                Ticket Details
            </p>
            <a href="#" class="card-header-icon">
                <span class="icon"><i class="mdi mdi-reload"></i></span>
            </a>
        </header>
        <div class="card-content">
            <table>
                <tr>
                    <th class="hidden lg:table-cell">Created</th>
                    <td data-label="Created"><?= $theTicket['created_date']; ?></td>
                    <th class="hidden lg:table-cell">Closed</th>
                    <td data-label="Closed"><?php echo $theTicket['closed_date'] ?? "<strong class='text-orange-500'>Opened</strong>"; ?></td>
                    <th class="hidden lg:table-cell">Status</th>
                    <?php
                    $statusClass = $theTicket['status_name'] === "closed" ? "class='font-black text-green-500'" : "";
                    ?>
                    <td <?= $statusClass ?> data-label="Status"><?= $theTicket['status_name']; ?></td>
                </tr>
                <tr>
                    <th class="hidden lg:table-cell">Created by</th>
                    <td data-label="Created By"><?= $theTicket['creator_name'] . " " . $theTicket['creator_surname']; ?></td>
                    <th class="hidden lg:table-cell">Priority</th>
                    <td data-label="Priority"><?= $theTicket['priority_name']; ?></td>
                    <th class="hidden lg:table-cell">Department</th>
                    <td data-label="Department"><?= $theTicket['department_name']; ?></td>
                </tr>
                <tr>
                    <th class="hidden lg:table-cell">Handling</th>
                    <td data-label="Handling"><?= $theTicket['handled_by'] ? $theTicket['admin_name'] . " " . $theTicket['admin_surname'] : "<strong class='text-orange-500'>Unassigned yet!</strong>"; ?></td>
                    <th class="hidden lg:table-cell">Reopened</th>
                    <td data-label="Reopened"><?= $theTicket['was_reopened'] === 1 ? "Yes" : "No" ?></td>
                    <?php if ($theTicket['status_name'] === "closed"): ?>
                        <th class="hidden lg:table-cell">Closing reason</th>
                        <td data-label="Closing reason"><?= $theTicket['closing_type'] ?></td>
                    <?php endif; ?>
                </tr>
            </table>
        </div>
        <hr>
 
        <?php
        // Take the ticket the ticket button
        if (
            $theTicket["statusId"] === 1 && 
            $theTicket["handled_by"] === null && 
            trim($_SESSION["user_role"] === "admin") && 
            $_SESSION["user_id"] !== $theTicket["created_by"]
        ): 
        ?>
            <form class="px-8" method="POST" action="../actions/process_take_ticket.php">
                <input type="hidden" name="take_ticket_id" value=<?= $theTicket['id'] ?>>
                <div class="field grouped">
                    <div class="control w-full">
                        <input type="submit" name="take_ticket" value="Take the Ticket" class="button bg-green-500 hover:bg-green-700 text-white font-bold w-full">
                    </div>
                </div>
            </form>
        <?php endif; // End of take the ticket button block ?>

        <!-- Ticket title -->
        <div class="p-5 mt-4 text-3xl font-semibold"><i><?= $theTicket['title']; ?></i></div>

        <!-- Ticket text -->
        <div class="border-2 border-gray-200 p-3 text-lg italic mb-4 bg-yellow-100">
            <div class="p-5">
                <a href="<?= $theTicket['url']; ?>" target="_blank">
                    <strong>Error page: </strong> <span class="text-blue-500 hover:text-blue-900"><?= $theTicket['url']; ?></span>
                </a>
            </div>
            <div>
                <?= $theTicket['body']; ?>
            </div>
        </div>

        <div>
            <?php
            // Adds attachment files
            if ($theTicket['file']) {
                $attachments = explode(',', $theTicket['file']);

                foreach ($attachments as $attachment) {
            ?>
                <a class="m-2 inline-block" href='../img/ticket_images/<?= $attachment ?>' target="_blank"><img width='150' src='../img/ticket_images/<?= $attachment ?>'/></a>
            
            <?php
                } // closing foreach
            } // closing if
            ?>
        </div>
        <hr>

        <!-- Close / Reopen ticket button -->
        <?php
        // Prevents closing ticket has status different from "in progress" or "closed" and no messages.
        if (($theTicket["statusId"] === 2 || $theTicket["statusId"] === 3) && !empty($allMessages)): 
        ?>
            <form class="p-8" method="POST" action="../actions/process_close_reopen_ticket.php">
            <input type="hidden" name="ticket_id" value=<?= $theTicket['id'] ?>>
                <div class="field grouped">
                    <?php if ($theTicket['status_name'] !== "closed"): ?>
                        <div class="control w-full">
                            <button class="button red w-full --jb-modal" data-target="ticket-close-modal" type="button">
                                Close Ticket
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="control w-full">
                            <input type="submit" name="reopen_ticket" value="Reopen Ticket" class="button blue w-full">
                        </div>
                    <?php endif; ?>
                </div>
            </form>
            <hr>
        <?php 
        endif; // End of Close / Reopen ticket button block

        // Adds _ticket_close_modal.php
        if ($theTicket['status_name'] === "in progress" || $theTicket['status_name'] !== "split") {
            require_once __DIR__ . "/_ticket_close_modal.php";
        }

        // Delete the ticket button
        if (
            $theTicket["statusId"] === 1 && 
            $theTicket["handled_by"] === null && 
            empty($allMessages) && 
            ($theTicket["created_by"] == trim($_SESSION['user_id']) || trim($_SESSION["user_role"] === "admin"))
        ): 
            require_once '_ticket_delete_modal.php';
        ?>
            <div class="p-8">
                <button class="button red w-full --jb-modal"  data-target="ticket-delete-modal" type="button">
                    Delete Ticket
                </button>
            </div>
            <hr>
        <?php 
        endif; // End of delete ticket button block
        
        // Messages
        require_once '_message.php';
        
        // Message form
        if ($theTicket["statusId"] !== 3) require_once '_message-form.php'; 
        ?>

    </div>

    <?php else : ?>

    <div class="card empty">
        <div class="card-content">
            <div>
                <span class="icon large"><i class="mdi mdi-emoticon-sad mdi-48px"></i></span>
            </div>
            <p>Nothing's here…</p>
        </div>
    </div>

    <?php endif ?>

</section>