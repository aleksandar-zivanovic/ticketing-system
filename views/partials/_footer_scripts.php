</div>

<?php
// Extracts the current URL path by removing query parameters (if present) for further use in determining required files 
$currentUrl = $_SERVER['REQUEST_URI'];
if (str_contains($currentUrl, "?")) {
    $position = strpos($currentUrl, "?");
    $remove = substr($currentUrl, $position);
    $currentPage = str_replace($remove, "", $currentUrl);
} else {
    $currentPage = $currentUrl;
}

if (!str_contains($currentPage, "forms.php")):
?>
    <!-- Admin One JavaonScript -->
    <script type="text/javascript" src="/ticketing-system/public/js/admin-one-main.js"></script>
<?php
endif;

if (str_ends_with($currentPage, "index.php") || str_ends_with($currentPage, "/")):
?>
    <script type="text/javascript" src="/ticketing-system/public/js/Chart.min.js"></script>
    <script type="text/javascript" src="/ticketing-system/public/js/admin-one-chart.js"></script>
<?php endif; ?>

<!-- Search JavaScript -->
<script type="text/javascript" src="/ticketing-system/public/js/search.js"></script>

<!-- Material Design Icons (MDI) - Local copy v7.4.47 -->
<!-- Downloaded from: https://github.com/Templarian/MaterialDesign-Webfont/releases/tag/v7.4.47 -->
<link rel="stylesheet" href="/ticketing-system/public/css/materialdesignicons.min.css">