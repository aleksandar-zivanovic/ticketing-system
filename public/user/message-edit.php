<?php
session_start();
require_once '../../helpers/functions.php';

// Checks if a visitor is logged in.
requireLogin();

if (!isset($_GET['message']) || !is_numeric($_GET['message'])) {
    header("Location: ../");
    die();
}

require_once '../../classes/Message.php';
require_once '../../config/config.php';

$id = filter_input(INPUT_GET, "message", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

// Deny access if the $_GET['message'] is not a number or is less than 1
if ($id === false) die(header("Location: ../"));

$message = new Message();
$currentMessage = $message->getMessageWithAttachments($id);

// Allow access only to the message creator
if ($currentMessage["creator_id"] !== $_SESSION['user_id']) die(header("Location: ../index.php"));

$page = "Edit message";
$panel = $_SESSION['user_role'] === "admin" ? "admin" : "user";
?>

<!DOCTYPE html>
<html lang="en" class="">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page ?></title>

    <!-- Tailwind is included -->
    <link rel="stylesheet" href="../css/admin-one-main.css">

</head>

<body>

    <div id="app">

        <?php
        // import header navigation bar
        include_once '../../partials/_navigation-bar.php';

        // import side menu
        include_once '../../partials/_side-menu.php';

        // import breadcrumbs
        include_once '../../partials/_navigation-breadcrumbs.php';
        ?>

        <section class="section main-section">
            <div class="card mb-6">
                <div class="card-content">
                    <form id="editMessageForm" method="POST" action="../actions/message-edit-action.php" enctype="multipart/form-data">

                        <div class="field">
                            <label class="label">Message</label>
                            <div class="control">
                                <textarea class="textarea" name="body"><?= $currentMessage['body'] ?></textarea>
                            </div>
                        </div>

                        <input id="message_id" type="hidden" name="message_id" value="<?= $id ?>">
                        <input id="ticket_id" type="hidden" name="ticket_id" value="<?= $currentMessage['ticket'] ?>">
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
                                        <img id="image-<?= $key ?>" width='150' src='../img/ticket_images/<?= $attachment ?>' title="<?= $attachment ?>" />
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
                                <input type="submit" class="button green" value="Submit">
                            </div>
                            <div class="control">
                                <button type="reset" class="button red">
                                    Cancle
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </section>

        <?php
        // Import footer
        include_once '../../partials/_footer.php';
        ?>

    </div>

    <!-- JavaScript file -->
    <script src="../js/main.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
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