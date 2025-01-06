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
<!-- Admin One JavaScript -->
<script type="text/javascript" src="../js/admin-one-main.js"></script>
<?php 
endif;

if (str_ends_with($currentPage, "index.php") || str_ends_with($currentPage, "/")): 
?>
<script type="text/javascript" src="../js/Chart.min.js"></script>
<script type="text/javascript" src="../js/admin-one-chart.js"></script>
<?php endif; ?>

<!-- Material Design Icons (MDI) - Local copy v4.9.95 -->
<!-- Downloaded from: https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css -->
<link rel="stylesheet" href="../css/materialdesignicons.min.css">