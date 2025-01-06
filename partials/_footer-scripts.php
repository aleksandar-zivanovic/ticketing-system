</div>

<?php $currentPage = $_SERVER['REQUEST_URI']; 

if (!str_contains($currentPage, "forms.php")): ?>
<!-- Admin One JavaScript -->
<script type="text/javascript" src="../js/admin-one-main.js"></script>
<?php 
endif;

if (str_contains($currentPage, "index.php")): 
?>
<script type="text/javascript" src="../js/Chart.min.js"></script>
<script type="text/javascript" src="../js/admin-one-chart.js"></script>
<?php endif; ?>

<!-- Material Design Icons (MDI) - Local copy v4.9.95 -->
<!-- Downloaded from: https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css -->
<link rel="stylesheet" href="../css/materialdesignicons.min.css">