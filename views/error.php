<?php
$panel = "error";
$page  = "Error Page";
require_once ROOT . 'views' . DS . 'partials' . DS . '_head.php';
?>

<body>

    <div id="app">

        <?php
        // import header navigation bar
        include_once ROOT . 'views' . DS . 'partials' . DS . '_navigation_bar.php';

        // import side menu bar
        include_once ROOT . 'views' . DS . 'partials' . DS . '_side_menu.php';

        // import breadcrumbs
        include_once ROOT . 'views' . DS . 'partials' . DS . '_navigation_breadcrumbs.php';

        // import session messages
        include_once ROOT . 'views' . DS . 'partials' . DS . '_session_messages.php';
        ?>

        <section class="section main-section">
            <p class="text-xl text-center">If the error persists, please contact support.</p>
        </section>

        <?php
        // import footer
        include_once ROOT . 'views' . DS . 'partials' . DS . '_footer.php';
        ?>

</body>

</html>