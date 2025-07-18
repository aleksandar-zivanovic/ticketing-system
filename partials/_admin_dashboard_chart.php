<div class="card mb-6">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-finance"></i></span>
            <?= $title ?>
        </p>
    </header>
    <div class="card-content">
        <div class="chart-area">
            <div class="h-full">
                <div class="chartjs-size-monitor">
                    <div class="chartjs-size-monitor-expand">
                        <div></div>
                    </div>
                    <div class="chartjs-size-monitor-shrink">
                        <div></div>
                    </div>
                </div>
                <canvas id="<?= $chartId ?>" style="width:100%" class="chartjs-render-monitor block"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    const data_<?= $chartId ?> = <?php echo json_encode($data); ?>;

    new Chart(document.getElementById('<?= $chartId ?>'), {
        type: "<?= $type ?>",
        data: {
            labels: data_<?= $chartId ?>.labels,
            datasets: data_<?= $chartId ?>.datasets.map((ds, i) => ({
                ...ds,
            }))
        },
        options: {
            maintainAspectRatio: false
        }
    });
</script>