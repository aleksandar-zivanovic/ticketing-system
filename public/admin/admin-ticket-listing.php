<?php
session_start();
require_once '../../helpers/functions.php';
require_once '../../classes/User.php';
require_once '../../classes/Ticket.php';
require_once '../../classes/Department.php';
require_once '../../classes/Priority.php';
require_once '../../classes/Status.php';

$page = fileName(__FILE__);
$data = true;

// Initialize allowed filter values for tickets
$status = new Status();
$statuses = $status->getAllStatusNames();

$priority = new Priority();
$priorities = $priority->getAllPriorityNames();

$department = new Department();
$departments = $department->getAllDepartmentNames();

// Set allowed values list for fetchAllTickets() method
$allowedValues = array_merge(
  ["statuses" => $statuses], 
  ["priorities" => $priorities], 
  ["departments" => $departments],
);

// Get sorting and ordering parameters
if (isset($_GET['order_by'])) {
  $orderBy = cleanString(filter_input(INPUT_GET, 'order_by', FILTER_DEFAULT));
  $_SESSION['order_by'] = $orderBy;
} elseif (!isset($_GET['order_by']) && isset($_SESSION['order_by'])) {
  $orderBy = $_SESSION['order_by'];
} else {
  $orderBy = "newest";
}

$sortBy = isset($_GET['sort']) ? filter_input(INPUT_GET, 'sort', FILTER_DEFAULT) : null;

// Call fetchAllTickets() method
$ticket = new Ticket();
$data = $ticket->fetchAllTickets(allowedValues: $allowedValues, orderBy: $orderBy, sortBy: $sortBy);
?>

<!DOCTYPE html>
<html lang="en" class="">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=$page ?></title>

  <!-- Tailwind is included -->
  <link rel="stylesheet" href="../css/admin-one-main.css">
  <link rel="stylesheet" href="../css/font-awesome.min.css">
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
  require_once '../../partials/_admin-table.php';

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