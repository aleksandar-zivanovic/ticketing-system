<section class="is-hero-bar">
    <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
        <h1 class="title">Users listing | <span class="text-gray-500 font-thin">Total: <?= $userCount ?></span></h1>
        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
            <form action="http://localhost/ticketing-system/admin/users-listing">
                <?php
                // Include order by buttons partial
                require_once ROOT . 'views' . DS . 'partials' . DS . '_table_order_by_buttons.php';
                ?>

                <select name="sort" id="sort" onchange="this.form.submit()">
                    <option value="all" <?php echo addSelectedTag("newest", "sort"); ?>>All</option>
                    <optgroup label="role">
                        <?php
                        foreach (USER_ROLES as $roleName => $roleId) {
                            echo "<option value='{$roleName}' " . addSelectedTag($roleName, "sort") . ">" . ucfirst($roleName) . "</option>";
                        }
                        ?>
                    </optgroup>
                    <optgroup label="department">
                        <?php
                        foreach ($departments as $singleDepartment) {
                            echo "<option value='{$singleDepartment}' " . addSelectedTag($singleDepartment, "sort") . ">" . ucfirst($singleDepartment) . "</option>";
                        }
                        ?>
                    </optgroup>
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
                    // Render table legend
                    renderTableLegend("Administrator", "Moderator", "Regular user");
                    ?>
                </p>
            </header>
            <div class="card-content">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name & Surname</th>
                            <th>Email</th>
                            <th class="text-center">Role</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Tickets</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($users as $user) :
                            // Set style for ticket ID based on user relation to the ticket
                            if ($user["role_id"] === 3) {           // Admin
                                $style = "bg-green-100";
                            } elseif ($user["role_id"] === 2) {     // Moderator
                                $style = "bg-blue-100";
                            } else {
                                $style = "";                        // Regular user
                            }
                        ?>
                            <tr>
                                <td data-label="ID">
                                    <span class="px-1 p-1 <?= $style ?> border-2 border-solid border-black rounded-full"><?= $user['id']; ?></span>
                                </td>

                                <td data-label="Name & Surname">
                                    <span class="px-1 p-1">
                                        <a href="/ticketing-system/profile.php?user=<?= $user['id'] ?>" target="_blank" class="hover:underline hover:text-blue-600">
                                            <?= $user['name'] . " " . $user['surname']; ?>
                                        </a>
                                    </span>
                                </td>

                                <td data-label=" Email">
                                    <small class="text-gray-500" title="<?= $user['email']; ?>"><?= $user['email']; ?></small>
                                </td>

                                <td data-label="Role" class="text-center">
                                    <span class="px-2 p-1 <?= $style ?> border-2 border-solid border-black rounded-full"><?= $user["role_name"]; ?></span>
                                </td>

                                <td data-label="Phone"><?= $user['phone'] ?></td>

                                <td data-label="Department"><?= $user["department_name"]; ?></td>

                                <td data-label="Tickets">
                                    <?php if ($user["tickets_count"] > 0) : ?>
                                        <a href="/ticketing-system/admin/user-tickets-list?user=<?= $user['id'] ?>" target="_blank" class="button small blue">View tickets</a>
                                    <?php else : ?>
                                        <i>No tickets</i>
                                    <?php endif; ?>
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