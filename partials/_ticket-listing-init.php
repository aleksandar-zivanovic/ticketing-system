<?php
// Gets file name
$fileName = basename($_SERVER['SCRIPT_NAME']); 

// Sets an appropriate if condition
if ($fileName === "user-ticket-listing.php") { 
    require_once '../actions/user-ticket-listing-action.php';

    if (!isset($_SESSION['user_email'])) {
        header("Location: /ticketing-system/public/forms/login.php");
        die;
    }

} elseif ($fileName === "admin-ticket-listing.php") {
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
  require_once '../../partials/_ticket-listing-table.php';

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