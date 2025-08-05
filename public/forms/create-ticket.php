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
    <?php require_once '../../partials/_ticket-form.php'; ?>
</body>

</html>