<div class="card mb-6">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-finance"></i></span>
            <?= $title ?>
        </p>
    </header>
    <div class="card-content h-96">
        <div class="chart-area h-full">
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

    const dashStyles_<?= $chartId ?> = [
        [],             // solid line
        [5, 5],         // dashed line
        [1, 4],         // dotted line
        [10, 2, 2, 2],  // mixed pattern
        [3, 3, 1, 3]    // variation
    ];

    const borderWidths_<?= $chartId ?> = [2, 3, 4, 5, 6];

    new Chart(document.getElementById('<?= $chartId ?>'), {
        type: "<?= $type ?>",
        data: {
            labels: data_<?= $chartId ?>.labels,
            datasets: data_<?= $chartId ?>.datasets.map((ds, i) => ({
                ...ds,
                pointRadius: 6,
                pointHoverRadius: 12,
                borderWidth: borderWidths_<?= $chartId ?>[i % borderWidths_<?= $chartId ?>.length],
                borderDash: dashStyles_<?= $chartId ?>[i % dashStyles_<?= $chartId ?>.length],
                borderCapStyle: 'round'
            })),
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>