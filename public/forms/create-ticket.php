<?php
session_start();
require_once '../../helpers/functions.php';
require_once '../../classes/Department.php';
require_once '../../classes/Ticket.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creating a ticket</title>
    <link rel="stylesheet" href="../css/form.css">
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/tailwind-output.css">
</head>

<body>
    <div class="max-w-4xl mx-auto font-[sans-serif] p-6">
        <div class="text-center mb-16">
            <a href="javascript:void(0)"><img src="https://readymadeui.com/readymadeui.svg" alt="logo" class='w-52 inline-block' />
            </a>
            <h4 class="text-gray-800 text-base font-semibold mt-6">Creating a ticket</h4>
        </div>

        <?php
            if (!isset($_GET['source']) || strlen($_GET['source']) < 5) {
                header('Location: /ticketing-system/');
            }

            // cleaning soruce error page URL
            $sourceUrl = trim(htmlspecialchars(filter_input(INPUT_GET, "source", FILTER_SANITIZE_URL)));

        ?>

        <form action="../actions/process_creating_ticket.php" method="POST" enctype="multipart/form-data">
            <div class="grid sm:grid-cols-1 gap-8">

            <?php
                // choose department
                $department = new Department();
                $data = $department->getAllDepartments();
                renderingSelectOption("Choose department", "department", $data);

                // choose priority
                $ticket = new Ticket();
                $data = $ticket->getAllPriorities();
                renderingSelectOption("Choose priority", "priority", $data);

                // error page url
                renderingInputField(null, "error_page", "hidden", null, $sourceUrl);

                // title
                renderingInputField("Title:", "error_title", "text", "Enter title");

                // error description
                renderingTextArea("Describe the issue:", "error_description");

                // error image
                renderingInputField("Insert error images:", "error_images[]", "file", "");
                ?>
            </div>

            <div class="!mt-12">
            <?php 
            // submit button
            renderingSubmitButton("user_action", "Create Ticket");
            ?>
          </div>
        </form>
    </div>
</body>

</html>