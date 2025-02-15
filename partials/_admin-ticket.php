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
                    <td data-label="Closed"><?php echo $ticket['closed_date'] ?? "<strong class='text-orange-500'>Not closed yet!</strong>"; ?></td>
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

        <!-- Ticket title -->
        <div class="p-5 mt-4"><i class="text-2xl font-bold"><?= $ticket['title']; ?></i></div>

        <!-- Ticket text -->
        <div class="border-2 border-gray-200 p-3 text-lg italic mb-4 bg-yellow-100">
            <?= $ticket['body']; ?>
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
                } // closing freach
            } // closing if
            ?>
        </div>
        <hr>


        <!-- 
        TODO: Implement messages between ticket creator and administrtor handling the ticket.
        -->


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