<?php
session_start();

// Gets file name
$fileName = basename($_SERVER['SCRIPT_NAME']); 

// Sets an appropriate if condition
if ($fileName === "view-ticket.php") { 
    $accessDenied = ($_SESSION['user_role'] != "admin");
} elseif ($fileName === "user-view-ticket.php") {
    $accessDenied = (!isset($_SESSION['user_role']));
}

if (
    !isset($_GET['ticket']) ||
    !is_numeric($_GET['ticket']) ||
    $_GET['ticket'] < 1 ||
    $accessDenied
) {
    header("Location:../index.php");
    die;
}

require_once '../../helpers/functions.php';
require_once '../../classes/Ticket.php';

// Sets the panel (admin or user)
$panel = isset($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], "/admin/") ? "admin" : "user";

$ticketID = filter_input(INPUT_GET, "ticket", FILTER_SANITIZE_NUMBER_INT);

// Call fetchAllTickets() method
$ticket = new Ticket();
$ticket = $ticket->fetchTicketDetails($ticketID);

// Prevents users who are not the ticket creator or and admin to access to the ticket
if ($_SESSION['user_role'] != "admin" && $_SESSION['user_id'] != $ticket["created_by"]) {
  header("Location:../index.php");
  die;
}

// Set $page and $data varaiables
$page = "Ticket: " . $ticket['title'];
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
  <link rel="stylesheet" href="../css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/tailwind-output.css">
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

  // import table
  require_once '../../partials/_admin-ticket.php';

  // import edit modal
  include_once '../../partials/_edit_modal.php';

  // import delete modal
  include_once '../../partials/_delete_modal.php';

  // import footer
  include_once '../../partials/_footer.php'; 
  ?>

</div>

</body>
</html>