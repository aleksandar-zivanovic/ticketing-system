<section class="is-hero-bar">
    <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
        <?php if ($ticket['id']): ?>
        <h1 class="title">Ticket ID: <?= $ticket['id'] ?></h1>
        <?php endif; ?>
    </div>
</section>

<section class="section main-section">
    <?php if ($ticket['id']) : ?>
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
                    <th>Created</th>
                    <td data-label="Created"><?= $ticket['created_date']; ?></td>
                    <th>Closed</th>
                    <td data-label="Closed"><?php echo $ticket['closed_date'] ?? "<strong class='text-orange-500'>Opened</strong>"; ?></td>
                    <th>Status</th>
                    <?php
                    $statusClass = $ticket['status_name'] === "closed" ? "class='font-black text-green-500'" : "";
                    ?>
                    <td <?= $statusClass ?> data-label="Status"><?= $ticket['status_name']; ?></td>
                </tr>
                <tr>
                    <th>Handling</th>
                    <td data-label="Handling"><?= $ticket['handled_by'] ? $ticket['admin_name'] . " " . $ticket['admin_surname'] : "<strong class='text-orange-500'>Unassigned yet!</strong>"; ?></td>
                    <th>Priority</th>
                    <td data-label="Priority"><?= $ticket['priority_name']; ?></td>
                    <th>Department</th>
                    <td data-label="Department"><?= $ticket['department_name']; ?></td>
                </tr>
                <tr>
                    <th>Created by</th>
                    <td data-label="Created By"><?= $ticket['creator_name'] . " " . $ticket['creator_surname']; ?></td>
                </tr>
            </table>
        </div>
        <hr>
 
        <?php
        // Take the ticket the ticket button
        if (
            $ticket["statusId"] === 1 && 
            $ticket["handled_by"] === null && 
            trim($_SESSION["user_role"] === "admin") && 
            $_SESSION["user_id"] !== $ticket["created_by"]
        ): 
        ?>
            <form class="px-8" method="POST" action="../actions/process_take_ticket.php">
                <input type="hidden" name="take_ticket_id" value=<?= $ticket['id'] ?>>
                <div class="field grouped">
                    <div class="control w-full">
                        <input type="submit" name="take_ticket" value="Take the Ticket" class="button bg-green-500 hover:bg-green-700 text-white font-bold w-full">
                    </div>
                </div>
            </form>
        <?php endif; // End of take the ticket button block ?>

        <!-- Ticket title -->
        <div class="p-5 mt-4 text-3xl font-semibold"><i><?= $ticket['title']; ?></i></div>

        <!-- Ticket text -->
        <div class="border-2 border-gray-200 p-3 text-lg italic mb-4 bg-yellow-100">
            <div class="p-5">
                <a href="<?= $ticket['url']; ?>" target="_blank">
                    <strong>Error page: </strong> <span class="text-blue-500 hover:text-blue-900"><?= $ticket['url']; ?></span>
                </a>
            </div>
            <div>
                <?= $ticket['body']; ?>
            </div>
        </div>

        <div>
            <?php
            // Adds attachment files
            if ($ticket['file']) {
                $attachments = explode(',', $ticket['file']);

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
        if (($ticket["statusId"] === 2 || $ticket["statusId"] === 3) && !empty($allMessages)): 
        ?>
            <form class="p-8" method="POST" action="../actions/process_close_reopen_ticket.php">
            <input type="hidden" name="ticket_id" value=<?= $ticket['id'] ?>>
                <div class="field grouped">
                    <?php if ($ticket['status_name'] !== "closed"): ?>
                        <div class="control w-full">
                            <input type="submit" name="close_ticket" value="Close Ticket" class="button red w-full">
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

        // Delete the ticket button
        if (
            $ticket["statusId"] === 1 && 
            $ticket["handled_by"] === null && 
            empty($allMessages) && 
            ($ticket["created_by"] == trim($_SESSION['user_id']) || trim($_SESSION["user_role"] === "admin"))
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
        if ($ticket["statusId"] !== 3) require_once '_message-form.php'; 
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