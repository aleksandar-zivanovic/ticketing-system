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

$status = new Status();
$statuses = $status->getAllStatusNames();

$priority = new Priority();
$priorities = $priority->getAllPriorityNames();

$department = new Department();
$departments = $department->getAllDepartmentNames();

// Set allowed values list for fetchAllTickets() method
$allowedValues = array_merge(
  ["date" => ["newest", "oldest"]], 
  ["statuses" => $statuses], 
  ["priorities" => $priorities], 
  ["departments" => $departments],
);

// Call fetchAllTickets() method
$ticket = new Ticket();
$data = $ticket->fetchAllTickets($allowedValues, isset($_GET['tickets']) ? filter_input(INPUT_GET, 'tickets', FILTER_DEFAULT) : "oldest");
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
