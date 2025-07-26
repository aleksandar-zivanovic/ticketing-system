<div class="card mb-6">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-finance"></i></span>
            <?= $title ?>
        </p>
    </header>
    <div class="card-content <?php echo $type !== "pie" ? "h-96" : ""; ?>">
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
    const chartType_<?= $chartId ?> = "<?= $type ?>";
    // let dashStyles_<?= $chartId ?>;

    if (chartType_<?= $chartId ?> === "line") {
        var dashStyles_<?= $chartId ?> = [
            [], // solid line
            [5, 5], // dashed line
            [1, 4], // dotted line
            [10, 2, 2, 2], // mixed pattern
            [3, 3, 1, 3] // variation
        ];
    }

    const borderWidths_<?= $chartId ?> = [2, 3, 4, 5, 6];

    new Chart(document.getElementById('<?= $chartId ?>'), {
        type: chartType_<?= $chartId ?>,
        data: {
            labels: data_<?= $chartId ?>.labels,
            datasets: data_<?= $chartId ?>.datasets.map((ds, i) => ({
                ...ds,
                borderWidth: borderWidths_<?= $chartId ?>[i % borderWidths_<?= $chartId ?>.length],
                ...(chartType_<?= $chartId ?> === "line" && {
                    pointRadius: 8,
                    pointHoverRadius: 14,
                    borderDash: dashStyles_<?= $chartId ?>[i % dashStyles_<?= $chartId ?>.length],
                    borderCapStyle: 'round',
                }),
                ...(chartType_<?= $chartId ?> === "pie" && {
                    hoverOffset: 25,
                }),
            })),
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            ...(chartType_<?= $chartId ?> === "line" && {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }),
            ...(chartType_<?= $chartId ?> === "pie" && {
                layout: {
                    padding: 30,
                }
            }),
            plugins: {
                ...(chartType_<?= $chartId ?> === "bar" && {
                    legend: {
                        display: false,
                    }
                }),
                ...(chartType_<?= $chartId ?> !== "bar" && {
                    legend: {
                        display: true,
                        labels: {
                            color: "black",
                            font: {
                                size: 15,
                            },
                        },
                    },
                }),
                datalabels: {
                    color: (context) => context.active ? 'black' : 'white',
                    font: (context) => ({
                        size: context.active ? 22 : 14,
                        weight: 'bold',
                    }),
                    ...(chartType_<?= $chartId ?> === "pie" ? {
                        formatter: (value, context) => {
                            const label = context.chart.data.labels[context.dataIndex];
                            return `${label} \n ${value}`;
                        },
                    } : {
                        formatter: (value, context) => value
                    }),
                    ...(chartType_<?= $chartId ?> === "pie" && {
                        anchor: 'center',
                        align: 'center',
                        textAlign: 'center',
                    }),
                },


            },
        },
        plugins: [ChartDataLabels],
    });
</script>