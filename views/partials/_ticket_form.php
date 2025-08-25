<div class="max-w-4xl mx-auto font-[sans-serif] p-6">
    <?php
    require_once ROOT . 'helpers' . DS . 'view_helpers.php';
    if (empty($split)) :
    ?>
        <div class="text-center mb-16">
            <a href="javascript:void(0)"><img src="https://readymadeui.com/readymadeui.svg" alt="logo" class='w-52 inline-block' />
            </a>
            <h4 class="text-gray-800 text-base font-semibold mt-6">Creating a ticket</h4>
        </div>
    <?php
        if (!isset($_GET['source']) || strlen($_GET['source']) < 5) {
            header('Location: /ticketing-system/public/index.php');
            die;
        }

        require_once '_session_messages.php';

        // cleaning soruce error page URL
        $sourceUrl = cleanString(filter_input(INPUT_GET, "source", FILTER_SANITIZE_URL));
    endif;

    $brackets = !empty($split) ? "[0]" : "";
    ?>

    <form action="/ticketing-system/<?= empty($split) ? "create_ticket_action.php" : "split_ticket_action.php" ?>" method="POST" enctype="multipart/form-data">
        <?= !empty($split) ? '<div id="tickets-container">' : ''; ?>
        <div class="grid sm:grid-cols-1 gap-8 <?= !empty($split) ? 'ticket-block' : ''; ?>">
            <?php
            // choose department
            renderingSelectOption("Choose department", "error_department" . $brackets, $departments);

            // choose priority
            renderingSelectOption("Choose priority", "error_priority" . $brackets, $priorities);

            // error page url
            renderingInputField(null, "error_page" . $brackets, "hidden", null, $sourceUrl);

            // title
            renderingInputField("Title:", "error_title" . $brackets, "text", "Enter title");

            // error description
            renderingTextArea("Describe the issue:", "error_description" . $brackets);

            // error image
            renderingInputField("Insert error images:", "error_images"  . $brackets . "[]", "file", "");

            if (!empty($split)) {
                // creator's ID
                renderingInputField(null, "error_user_id", "hidden", null, $theTicket["created_by"]);

                // ticket's ID
                renderingInputField(null, "error_ticket_id", "hidden", null, $theTicket["id"]);
            }
            ?>
        </div>

        <?php
        if (!empty($split)) :
            echo "</div>";
        ?>
            <div class="!mt-12">
                <!-- Add split ticket form button -->
                <?php
                renderingButton("add-ticket-btn", "Add create ticket form", "text-white", "bg-green-500", "hover:bg-green-600", "my-4", "mdi mdi-plus-thick", type:"button");
                ?>
            <?php endif; // ends if (!empty($split)) : block
            ?>


            <?php
            if (!empty($split)) {
                // Cancle split action button
                renderingButton("user_action", "Cancle split action", "text-white", "bg-red-500", "hover:bg-red-600", "mb-4");

                // Process splitting action button
                renderingButton("user_action", "Split Ticket");
            } else {
                // submit button
                renderingButton("user_action", "Create Ticket");
            }

            ?>
            </div>
    </form>
</div>

<?php if (!empty($split)) : ?>
    <script>
        let ticketIndex = 1;

        document.getElementById('add-ticket-btn').addEventListener('click', function() {
            const container = document.getElementById('tickets-container');
            const original = container.querySelector('.ticket-block');
            const clone = original.cloneNode(true);

            clone.querySelectorAll('select, input, textarea').forEach(el => {
                let name = el.getAttribute('name');
                // Preserve universal input values and skip unnamed inputs
                if (!name || el.name === "error_user_id" || el.name === "error_ticket_id") {
                    return;
                }

                if (name.match(/\[\d+\]/)) {
                    // Replace existing index with ticketIndex
                    let newName = name.replace(/\[\d+\]/g, `[${ticketIndex}]`);
                    el.setAttribute('name', newName);
                } else {
                    // Add [ticketIndex] before ], if there isn't an index
                    let pos = name.indexOf(']');
                    if (pos === -1) pos = name.length;
                    let newName = name.slice(0, pos) + `[${ticketIndex}]` + name.slice(pos);
                    el.setAttribute('name', newName);
                }

                el.value = '';
            });

            clone.querySelectorAll('[id]').forEach(el => {
                let oldId = el.id;
                let newId = oldId.replace(/\d+/, ticketIndex);
                el.id = newId;
            });

            clone.querySelectorAll('label[for]').forEach(label => {
                let oldFor = label.getAttribute('for');
                let newFor = oldFor.replace(/\d+/, ticketIndex);
                label.setAttribute('for', newFor);
            });

            container.appendChild(clone);
            ticketIndex++;
        });
    </script>

<?php endif; ?>