<?php
// Set $page and $data varaiables
$page = "Ticket: " . $theTicket['title'];
if (!empty($split)) {
  $page = "Split " . $page;
}

include_once ROOT . 'views' . DS . 'partials' . DS . '_head.php';
?>

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