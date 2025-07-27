<script>
document.addEventListener("DOMContentLoaded", function() {
    try {
        console.log("Chart initialization started");

        const selesaiData = [
            {{ implode(',', isset($chart_kinerja) && isset($chart_kinerja['selesai']) ? $chart_kinerja['selesai'] : [0,0,0,0]) }}
        ];
        const baruData = [
            {{ implode(',', isset($chart_kinerja) && isset($chart_kinerja['baru']) ? $chart_kinerja['baru'] : [0,0,0,0]) }}
        ];

        console.log("Chart data:", selesaiData, baruData);

        const ctx = document.getElementById('chart-kinerja');
        if (!ctx) {
            console.error("Canvas element not found!");
            return;
        }

        try {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                    datasets: [
                        {
                            label: 'Tugas Selesai',
                            data: selesaiData,
                            backgroundColor: '#206bc4',
                            borderColor: '#206bc4',
                            borderWidth: 1
                        },
                        {
                            label: 'Tugas Baru',
                            data: baruData,
                            backgroundColor: '#79a6dc',
                            borderColor: '#79a6dc',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0
                            }
                        }]
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return data.datasets[tooltipItem.datasetIndex].label + ': ' + tooltipItem.yLabel + ' tugas';
                            }
                        }
                    }
                }
            });
            console.log("Chart rendered successfully");
        } catch (chartError) {
            console.error("Error creating chart:", chartError);
            document.getElementById('chart-kinerja').innerHTML =
                '<p class="text-center text-muted pt-4">Tidak dapat menampilkan grafik. Silakan refresh halaman.</p>';
        }
    } catch (error) {
        console.error("Error in chart initialization:", error);
    }
});

function refreshDashboard() {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.style.position = 'fixed';
    loadingOverlay.style.top = '0';
    loadingOverlay.style.left = '0';
    loadingOverlay.style.width = '100%';
    loadingOverlay.style.height = '100%';
    loadingOverlay.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
    loadingOverlay.style.display = 'flex';
    loadingOverlay.style.justifyContent = 'center';
    loadingOverlay.style.alignItems = 'center';
    loadingOverlay.style.zIndex = '9999';
    loadingOverlay.innerHTML = '<div class="spinner-border text-primary" role="status"></div>';

    document.body.appendChild(loadingOverlay);

    setTimeout(function() {
        window.location.reload();
    }, 300);
}
</script>