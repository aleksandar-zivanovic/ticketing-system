<?php
if (empty($_SESSION["user_role"]) && !empty($_GET["source"]) && strlen(trim($_GET["source"])) > 11) {
    $_SESSION["redirect_after_login"] = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    redirectAndDie(BASE_URL . "login.php");
}

$sessionNames = ["error_page", "error_priority", "error_department", "error_title", "error_description"];

foreach ($sessionNames as $name) {
    if (isset($_SESSION[$name]) && is_array($_SESSION[$name])) {
        unset($_SESSION[$name]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creating a ticket</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/form.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/tailwind-output.css">
</head>

<body>
    <?php require_once ROOT . 'views' . DS . 'partials' . DS . '_ticket_form.php'; ?>
</body>

</html>