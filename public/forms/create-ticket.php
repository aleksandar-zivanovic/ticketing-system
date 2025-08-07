<?php
session_start();
require_once '../../helpers/functions.php';
require_once '../../classes/Department.php';
require_once '../../classes/Ticket.php';

unset(
    $_SESSION["error_priority"],
    $_SESSION["error_page"],
    $_SESSION["error_department"],
    $_SESSION["error_title"],
    $_SESSION["error_description"],
    $_SESSION["error_user_id"],
    $_SESSION["error_ticket_id"],
    $_SESSION["limit"],
    $_SESSION["info"],
    $_SESSION["success"],
    $_SESSION["fail"]
);
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
    <?php require_once '../../partials/_ticket-form.php'; ?>
</body>

</html>