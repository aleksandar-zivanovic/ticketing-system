<section class="is-hero-bar">
    <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
        <h1 class="title">Tickets listing</h1>
        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
            <form action="">
                <button 
                    type="submit" 
                    class="button <?php echo $orderBy === "oldest" ? "green" : "light";?>" 
                    name="order_by" 
                    value="oldest"
                >
                    <i class="fa fa-solid fa-arrow-up"></i>
                </button>

                <button 
                    type="submit" 
                    class="button 
                    <?php echo $orderBy === "newest" ? "green" : "light"; ?>" 
                    name="order_by" 
                    value="newest"
                >
                    <i class="fa fa-solid fa-arrow-down"></i>
                </button>

                <select name="sort" id="sort" onchange="this.form.submit()">
                    <option value="all" <?php echo addSelectedTag("sort", "newest"); ?>>All</option>
                    <optgroup label="status">
                        <?php
                        foreach ($statuses as $singleStatus) {
                            echo "<option value='{$singleStatus}' " . addSelectedTag("sort", $singleStatus) . ">" . ucfirst($singleStatus) . "</option>";
                        }
                        ?>
                    </optgroup>
                    <optgroup label="priority">
                        <?php
                        foreach ($priorities as $singlePriority) {
                            echo "<option value='{$singlePriority}' " . addSelectedTag("sort", $singlePriority) . ">" . ucfirst($singlePriority) . "</option>";
                        }
                        ?>
                    </optgroup>
                    <?php if ($departments) : ?>
                        <optgroup label="department">
                            <?php
                            foreach ($departments as $singleDepartment) {
                                echo "<option value='{$singleDepartment}' " . addSelectedTag("sort", $singleDepartment) . ">" . ucfirst($singleDepartment) . "</option>";
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
                </p>
                <a href="#" class="card-header-icon">
                    <span class="icon"><i class="mdi mdi-reload"></i></span>
                </a>
            </header>
            <div class="card-content">
                <table>
                    <thead>
                        <tr>
                            <th class="checkbox-cell">
                                <label class="checkbox">
                                    <input type="checkbox">
                                    <span class="check"></span>
                                </label>
                            </th>
                            <th>ID</th>
                            <th>Created</th>
                            <th>Closed</th>
                            <th>Department</th>
                            <th>Handling</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Files</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $ticket) : ?>

                            <tr>
                                <td class="checkbox-cell">
                                    <label class="checkbox">
                                        <input type="checkbox">
                                        <span class="check"></span>
                                    </label>
                                </td>
                                <td data-label="ID"><?= $ticket['id']; ?></td>
                                <td data-label="Created">
                                    <small class="text-gray-500" title="<?= $ticket['created_date']; ?>"><?= $ticket['created_date']; ?></small>
                                </td>
                                <td data-label="Closed">
                                    <small class="text-gray-500" title="<?= $ticket['closed_date']; ?>"><?= $ticket['closed_date'] ?? "Unsolved yet"; ?></small>
                                </td>
                                <td data-label="Department"><?= $ticket['department_name']; ?></td>
                                <td data-label="Handling"><?= $ticket['handled_by'] ? $ticket['admin_name'] . " " . $ticket['admin_surname'] : "Unassigned"; ?></td>
                                <td data-label="Priority"><?= $ticket['priority_name']; ?></td>
                                <td data-label="Status"><?= $ticket['status_name']; ?></td>

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
                                <td class="actions-cell">
                                    <div class="buttons right nowrap">
                                        <button class="button small green --jb-modal" data-target="sample-modal-2" type="button">
                                            <span class="icon"><i class="mdi mdi-eye"></i></span>
                                        </button>
                                        <button class="button small red --jb-modal" data-target="sample-modal" type="button">
                                            <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>
                <div class="table-pagination">
                    <div class="flex items-center justify-between">
                        <div class="buttons">
                            <button type="button" class="button active">1</button>
                            <button type="button" class="button">2</button>
                            <button type="button" class="button">3</button>
                        </div>
                        <small>Page 1 of 3</small>
                    </div>
                </div>
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