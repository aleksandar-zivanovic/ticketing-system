<?php
require_once '../../helpers/functions.php';

// Checks if a visitor is logged in.
requireLogin();

// Gets file name
$fileName = basename($_SERVER['SCRIPT_NAME']); 

// Sets an appropriate action file for requiring
if ($fileName === "user-ticket-listing.php") { 
  require_once '../actions/user-ticket-listing-action.php';
  requireLogin();
} elseif ($fileName === "admin-ticket-listing.php") {
  require_once '../actions/admin-ticket-listing-action.php';
} elseif ($fileName === "admin-tickets-i-handle.php") {
  // TODO: dodati ovaj uslov u gornji elseif sa znakom ili (||), tako da, ako se jedan od dva uslova ostvare, onda se isputni require_once '../actions/admin-ticket-listing-action.php';
  require_once '../actions/admin-ticket-listing-action.php';
}
?>

<!DOCTYPE html>
<html lang="en" class="">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $page ?></title>

  <!-- Tailwind is included -->
  <link rel="stylesheet" href="../css/tailwind-output.css">
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

  // import session messages
  include_once '../../partials/_session-messages.php';

  // import table
  require_once '../../partials/_ticket-listing-table.php';

  // import footer
  include_once '../../partials/_footer.php'; 
  ?>

</div>

</body>
</html>