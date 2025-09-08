<?php
require_once '../../helpers/functions.php';

$fileName = basename($_SERVER['SCRIPT_NAME']);

// Checks if a visitor is logged in.
if (str_contains($fileName, "admin")) {
  // Check if a visitor is logged in and is an admin.
  checkAuthorization("admin", "../");
  $page = "Administration ticket listing";
} else {
  // Check if a visitor is logged in.
  requireLogin();
  $page = "My tickets";
}

require_once __DIR__ . '/../public/actions/ticket_listing_action.php';
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