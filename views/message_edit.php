<?php $page = "Edit message"; ?>
<!DOCTYPE html>
<html lang="en" class="">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page ?></title>

    <!-- Tailwind is included -->
    <link rel="stylesheet" href="/ticketing-system/public/css/admin-one-main.css">

</head>

<body>

    <div id="app">

        <?php
        require_once ROOT . 'helpers' . DS . 'view_helpers.php';
        
        // import header navigation bar
        include_once ROOT . 'views' . DS . 'partials' . DS . '_navigation_bar.php';

        // import side menu
        include_once ROOT . 'views' . DS . 'partials' . DS . '_side_menu.php';

        // import breadcrumbs
        include_once ROOT . 'views' . DS . 'partials' . DS . '_navigation_breadcrumbs.php';
        ?>

        <section class="section main-section">
            <div class="card mb-6">
                <div class="card-content">
                    <form id="editMessageForm" method="POST" action="/ticketing-system/message_action.php" enctype="multipart/form-data">

                        <div class="field">
                            <label class="label">Message</label>
                            <div class="control">
                                <textarea class="textarea" name="body"><?= $currentMessage['body'] ?></textarea>
                            </div>
                        </div>

                        <input id="message_id" type="hidden" name="message_id" value="<?= $currentMessage["id"] ?>">
                        <input id="ticket_id" type="hidden" name="ticketId" value="<?= $currentMessage['ticket'] ?>">
                        <input id="image_ids" type="hidden" name="image_ids" value="">
                        <hr>

                        <!-- Add attachments -->
                        <div class="control">
                            <?php renderingInputField("Add attachment(s):", "error_images[]", "file", ""); ?>
                        </div>
                        <hr>

                        <!-- Manage old attachments -->
                        <div class="flex flex-col md:flex-row">
                            <?php
                            // Adds attachment files
                            if ($currentMessage['file']) :
                                $attachmentsFiles = explode(',', $currentMessage['file']);
                                $attachmentsIds = explode(',', $currentMessage['attachment_id']);
                                $attachments = array_combine($attachmentsIds, $attachmentsFiles);

                                foreach ($attachments as $key => $attachment) :
                            ?>
                                    <div id="attachment-<?= $key ?>" class="flex flex-col items-center" onclick="manageAttachments(<?= $key ?>)">
                                        <img id="image-<?= $key ?>" width='150' src='/ticketing-system/public/img/ticket_images/<?= $attachment ?>' title="<?= $attachment ?>" />
                                        <button id="delete-btn-<?= $key ?>" type="button" class="button red">
                                            Delete image
                                        </button>
                                    </div>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </div>
                        <hr>

                        <div class="field grouped">
                            <div class="control">
                                <input type="text" name="edit_message" value="edit" hidden>
                                <input type="submit" class="button green" value="Submit">
                            </div>
                            <div class="control">
                                <button type="reset" class="button red">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </section>

        <?php
        // Import footer
        include_once ROOT . 'views' . DS . 'partials' . DS . '_footer.php';
        ?>

    </div>

    <!-- JavaScript file -->
    <script src="/ticketing-system/public/js/main.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Clear old attachment IDs from sessionStorage
            sessionStorage.removeItem("attachmentIds");

            let form = document.getElementById('editMessageForm');
            let idsInput = document.getElementById('image_ids');

            form.addEventListener("submit", function(event) {
                event.preventDefault();

                let storedIds = sessionStorage.getItem("attachmentIds");
                if (storedIds) {
                    idsInput.value = storedIds;
                }

                form.submit();
            });
        });
    </script>

</body>

</html>