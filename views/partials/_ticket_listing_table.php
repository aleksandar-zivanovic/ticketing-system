<section class="is-hero-bar">
    <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
        <h1 class="title">Tickets listing | <span class="text-gray-500 font-thin">Total: <?= $totalItems ?></span></h1>
        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
            <form action="">
                <button
                    type="submit"
                    class="button <?php echo $orderBy === "oldest" ? "green" : "light"; ?>"
                    name="order_by"
                    value="oldest">
                    <i class="fa fa-solid fa-arrow-up"></i>
                </button>

                <button
                    type="submit"
                    class="button 
                    <?php echo $orderBy === "newest" ? "green" : "light"; ?>"
                    name="order_by"
                    value="newest">
                    <i class="fa fa-solid fa-arrow-down"></i>
                </button>

                <select name="sort" id="sort" onchange="this.form.submit()">
                    <option value="all" <?php echo addSelectedTag("newest", "sort"); ?>>All</option>
                    <optgroup label="status">
                        <?php
                        foreach ($statuses as $singleStatus) {
                            echo "<option value='{$singleStatus}' " . addSelectedTag($singleStatus, "sort") . ">" . ucfirst($singleStatus) . "</option>";
                        }
                        ?>
                    </optgroup>
                    <optgroup label="priority">
                        <?php
                        foreach ($priorities as $singlePriority) {
                            echo "<option value='{$singlePriority}' " . addSelectedTag($singlePriority, "sort") . ">" . ucfirst($singlePriority) . "</option>";
                        }
                        ?>
                    </optgroup>
                    <?php if ($departments) : ?>
                        <optgroup label="department">
                            <?php
                            foreach ($departments as $singleDepartment) {
                                echo "<option value='{$singleDepartment}' " . addSelectedTag($singleDepartment, "sort") . ">" . ucfirst($singleDepartment) . "</option>";
                            }
                            ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </form>
        </div>
    </div>
</section>

<section class="section main-section">
    <?php if ($data) : ?>
        <div class="card has-table">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-account-multiple"></i></span>
                    Tickets
                    <?php
                    // Show legend only for admin users
                    if ($_SESSION["user_role"] === "admin") :
                    ?>
                <div class="gap-2 text-sm mb-2 flex items-center">
                    <span class="text-lg">Legend:</span>
                    <span class="px-1 bg-green-300 rounded-full">Assigned to me</span>
                    <span class="px-1 bg-blue-200 rounded-full">Created by me</span>
                </div>
            <?php endif; ?>
            </p>
            </header>
            <div class="card-content">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th>Department</th>
                            <?php if ($fileName != "admin-tickets-i-handle.php"): ?>
                                <th>Handling</th>
                            <?php endif; ?>
                            <th>Priority</th>
                            <th>Files</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($data as $ticket) :
                            $assignedToMe = $_SESSION["user_id"] === $ticket["handled_by"] ? "px-1 bg-green-300 rounded-full" : "";
                            $myTicket = $_SESSION["user_id"] === $ticket["created_by"] ? "px-1 bg-orange-300 rounded-full" : "";
                        ?>

                            <tr>
                                <td data-label="ID">
                                    <span class="<?= $assignedToMe . " " . $myTicket ?>"><?= $ticket['id']; ?></span>
                                </td>
                                <td data-label="Title">
                                    <!--
                                    Link formation is left in the view for performance reasons,
                                    to avoid adding an extra foreach in the controller
                                    -->
                                    <a href="/ticketing-system/<?= $panel === "admin" ? "admin/view-ticket" :
                                                                    "user/user-view-ticket" ?>.php?ticket=<?= $ticket["id"] ?>">
                                        <?php
                                        echo strlen($ticket['title']) > 25 ? substr($ticket['title'], 0, 22) . "..." : $ticket['title'];
                                        ?>
                                    </a>
                                </td>

                                <td data-label="Created">
                                    <small class="text-gray-500" title="<?= $ticket['created_date']; ?>"><?= $ticket['created_date']; ?></small>
                                </td>

                                <?php
                                // Set ticket status name color
                                if (isset(TICKET_STATUSES[$ticket['status_name']]) && $ticket['status_name'] !== "closed") {
                                    $statusValue = $ticket['status_name'];
                                    $statusStyle = "style='" . TICKET_STATUSES[$ticket['status_name']] . "'";
                                }

                                if ($ticket['status_name'] === "closed") {
                                    $statusValue = date("Y/m/d", strtotime($ticket['closed_date']));
                                    $statusStyle = $statusStyle = "style='color:green; font-style: italic;'";
                                }
                                ?>
                                <td data-label="Status" <?= $statusStyle ?? ""; ?>><?= $statusValue ?></td>

                                <td data-label="Department"><?= $ticket['department_name']; ?></td>

                                <?php
                                if ($fileName != "admin-tickets-i-handle.php"):
                                    // Set style for unassigned tickets
                                    $handlingStyle = $ticket['handled_by'] ? "style='color:green;'" : "style='color:coral; font-style: italic;'"
                                ?>

                                    <td <?= $handlingStyle ?> data-label="Handling"><?= $ticket['handled_by'] ? $ticket['admin_name'] . " " . $ticket['admin_surname'] : "Unassigned"; ?></td>
                                <?php endif; ?>

                                <?php
                                // Set style for the highest priority level
                                $priorityStyle = HIGHEST_PRIORITY === $ticket['priority_name'] ? "style='color:coral; font-style: italic;'" : "";
                                ?>
                                <td <?= $priorityStyle ?> data-label="Priority"><?= $ticket['priority_name']; ?></td>

                                <?php
                                if ($ticket['attachment_id']) {
                                    $ticketIds = explode(',', $ticket['attachment_id']);
                                    $attachments = count($ticketIds);
                                }
                                ?>

                                <td data-label="Att">
                                    <?php
                                    echo $attachments ?? "None";
                                    unset($attachments);
                                    ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>
                <!-- Pagination -->
                <?php require_once ROOT . 'views' . DS . 'partials' . DS . '_table_pagination.php'; ?>
            </div>
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