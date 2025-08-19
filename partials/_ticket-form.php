<div class="max-w-4xl mx-auto font-[sans-serif] p-6">
    <?php
    if (empty($split)) :
    ?>
        <div class="text-center mb-16">
            <a href="javascript:void(0)"><img src="https://readymadeui.com/readymadeui.svg" alt="logo" class='w-52 inline-block' />
            </a>
            <h4 class="text-gray-800 text-base font-semibold mt-6">Creating a ticket</h4>
        </div>
    <?php
        if (!isset($_GET['source']) || strlen($_GET['source']) < 5) {
            header('Location: /ticketing-system/');
        }

        require_once '_session-messages.php';

        // cleaning soruce error page URL
        $sourceUrl = cleanString(filter_input(INPUT_GET, "source", FILTER_SANITIZE_URL));
    endif;

    $brackets = !empty($split) ? "[0]" : "";
    ?>

    <form action="../actions/process_create_split_ticket.php" method="POST" enctype="multipart/form-data">
        <?= !empty($split) ? '<div id="tickets-container">' : ''; ?>
        <div class="grid sm:grid-cols-1 gap-8 <?= !empty($split) ? 'ticket-block' : ''; ?>">
            <?php
            // choose department
            $department = new Department();
            $data = $department->getAllDepartments();
            renderingSelectOption("Choose department", "error_department" . $brackets, $data);

            // choose priority
            $ticket = new Ticket();
            $data = $ticket->getAllPriorities();
            renderingSelectOption("Choose priority", "error_priority" . $brackets, $data);

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
            <!-- Add split ticket form button -->
            <button type="button" id="add-ticket-btn" class="my-4 px-4 py-2 text-sm tracking-wider font-semibold rounded-md bg-green-500 hover:bg-green-600 text-white w-full focus:outline-none">
                <span class="mdi mdi-plus-thick"></span> Add create ticket form
            </button>
        <?php endif; // ends if (!empty($split)) : block
        ?>

        <div class="!mt-12">
            <?php
            if (!empty($split)) {
                // Cancle split action button
                echo '<button type="button" id="add-ticket-btn" class="my-4 px-4 py-2 text-sm tracking-wider font-semibold rounded-md bg-red-500 hover:bg-red-600 text-white w-full focus:outline-none">Cancle split action</button>';

                // Process splitting action button
                renderingSubmitButton("user_action", "Split Ticket");
            } else {
                // submit button
                renderingSubmitButton("user_action", "Create Ticket");
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