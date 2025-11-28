<?php
// Set $page and $data varaiables
$page = "Ticket: " . $theTicket['title'];
if (!empty($split)) {
  $page = "Split " . $page;
}

?>

<!DOCTYPE html>
<html lang="en" class="">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= !empty($split) ? "Split $page" :  $page ?></title>

  <!-- Tailwind is included -->
  <link rel="stylesheet" href="/ticketing-system/public/css/admin-one-main.css">
  <link rel="stylesheet" href="/ticketing-system/public/css/font-awesome.min.css">
  <link rel="stylesheet" href="/ticketing-system/public/css/tailwind-output.css">
</head>

<body>

  <div id="app">

    <?php
    // import header navigation bar
    include_once ROOT . 'views' . DS . 'partials' . DS . '_navigation_bar.php';

    // import side menu
    include_once ROOT . 'views' . DS . 'partials' . DS . '_side_menu.php';

    // import breadcrumbs
    include_once ROOT . 'views' . DS . 'partials' . DS . '_navigation_breadcrumbs.php';

    // import session messages
    include_once ROOT . 'views' . DS . 'partials' . DS . '_session_messages.php';

    // import table
    require_once ROOT . 'views' . DS . 'partials' . DS . '_ticket.php';

    // import footer
    include_once ROOT . 'views' . DS . 'partials' . DS . '_footer.php';
    ?>

  </div>

</body>

</html>