<div class="p-4">
    <?php
    if (count($data["searchResults"]) === 0) :
    ?>
        <p class="italic text-gray-600">No results found.</p>
    <?php
    else :
    ?>
        <div class="border border-gray-300 py-2 bg-white">
            <div class="card-content">
                <table>
                    <thead>
                        <tr>
                            <?php
                            if ($data["table"] === "tickets") :
                            ?>

                                <th>ID</th>
                                <th>Title</th>
                                <th>Body</th>
                                <th>Created</th>
                                <th>Status</th>
                                <th>Department</th>
                                <th>Priority</th>
                            <?php
                            else :
                            ?>
                                <th>ID</th>
                                <th>Name & Surname</th>
                                <th>Email</th>
                                <th class="text-center">Role</th>
                                <th>Phone</th>
                                <th>Department</th>
                                <th>Tickets</th>
                            <?php
                            endif;

                            foreach ($data["searchResults"] as $result) :
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                                if ($data["table"] === "tickets") {
                                    // Set style for ticket ID based on user relation to the ticket
                                    if ($_SESSION["user_id"] === $result["handled_by"]) {
                                        $style = "bg-green-100";
                                    } elseif ($_SESSION["user_id"] === $result["created_by"]) {
                                        $style = "bg-blue-100";
                                    } else {
                                        $style = "";
                                    }

                        ?>

                            <tr>
                                <td data-label="ID">
                                    <span class="px-1 p-1 <?= $style ?> border-2 border-solid border-black rounded-full"><?= $result['id']; ?></span>
                                </td>
                                <td data-label="Title">
                                    <!--
                                    Link formation is left in the view for performance reasons,
                                    to avoid adding an extra foreach in the controller
                                    -->
                                    <a href="/ticketing-system/admin/view-ticket.php?ticket=<?= $result["id"] ?>">
                                        <?php
                                        echo strlen($result['title']) > 25 ? substr($result['title'], 0, 22) . "..." : $result['title'];
                                        ?>
                                    </a>
                                </td>

                                <td data-label="Body" title="<?= $result['body']; ?>">
                                    <a href="/ticketing-system/admin/view-ticket.php?ticket=<?= $result["id"] ?>">
                                        <?php
                                        echo strlen($result['body']) > 50 ? substr($result['body'], 0, 47) . "..." : $result['body'];
                                        ?>
                                    </a>
                                </td>

                                <td data-label="Created">
                                    <small class="text-gray-500" title="<?= $result['created_date']; ?>"><?= $result['created_date']; ?></small>
                                </td>

                                <?php
                                    // Set ticket status name color
                                    if (isset(TICKET_STATUSES[$result['status_name']]) && $result['status_name'] !== "closed") {
                                        $statusValue = $result['status_name'];
                                        $statusStyle = "style='" . TICKET_STATUSES[$result['status_name']] . "'";
                                    }

                                    if ($result['status_name'] === "closed") {
                                        $statusValue = date("Y/m/d", strtotime($result['closed_date']));
                                        $statusStyle = $statusStyle = "style='color:green; font-style: italic;'";
                                    }
                                ?>
                                <td data-label="Status" <?= $statusStyle ?? ""; ?>><?= $statusValue ?></td>

                                <td data-label="Department"><?= $result['department_name']; ?></td>

                                <?php
                                    // Set style for the highest priority level
                                    $priorityStyle = HIGHEST_PRIORITY === $result['priority_name'] ? "style='color:coral; font-style: italic;'" : "";
                                ?>
                                <td <?= $priorityStyle ?> data-label="Priority"><?= $result['priority_name']; ?></td>

                                <td data-label="Att" class="text-center">
                                    <?php
                                    echo $attachments ?? "None";
                                    unset($attachments);
                                    ?>
                                </td>
                            </tr>

                        <?php
                                }

                                if ($data["table"] === "users") :

                                    if ($result["role_id"] === 3) {           // Admin
                                        $style = "bg-green-100";
                                    } elseif ($result["role_id"] === 2) {     // Moderator
                                        $style = "bg-blue-100";
                                    } else {
                                        $style = "";                          // Regular user
                                    }
                        ?>
                            <tr>
                                <td data-label="ID">
                                    <span class="px-1 p-1 <?= $style ?> border-2 border-solid border-black rounded-full"><?= $result['id']; ?></span>
                                </td>

                                <td data-label="Name & Surname">
                                    <span class="px-1 p-1">
                                        <a href="/ticketing-system/profile.php?user=<?= $result['id'] ?>" target="_blank" class="hover:underline hover:text-blue-600">
                                            <?= $result['name'] . " " . $result['surname']; ?>
                                        </a>
                                    </span>
                                </td>

                                <td data-label=" Email">
                                    <small class="text-gray-500" title="<?= $result['email']; ?>"><?= $result['email']; ?></small>
                                </td>

                                <td data-label="Role" class="text-center">
                                    <span class="px-2 p-1 <?= $style ?> border-2 border-solid border-black rounded-full"><?= $result["role_name"]; ?></span>
                                </td>

                                <td data-label="Phone"><?= $result['phone'] ?></td>

                                <td data-label="Department"><?= $result["department_name"]; ?></td>

                                <td data-label="Tickets">
                                        <a href="/ticketing-system/admin/user-tickets-list?user=<?= $result['id'] ?>" target="_blank" class="button small blue">View tickets</a>
                                </td>
                            </tr>

                        <?php
                                endif;
                        ?>
            </div>
        <?php
                            endforeach;
        ?>
        </tbody>
        </table>
        </div>
    <?php
    endif;
    ?>
</div>