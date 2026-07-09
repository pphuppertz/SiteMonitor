
// Simple bootstrap for now
<!DOCTYPE html>
<html>
<head>
    <title>SiteMonitor</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>SiteMonitor Dashboard</h1>
    <canvas id="responseChart"></canvas>

    <script>
        fetch('api/data.php')
            .then(r => r.json())
            .then(data => {
                new Chart(document.getElementById('responseChart'), {
                    type: 'line',
                    data: {
                        labels: data.timestamps,
                        datasets: [{
                            label: 'Response Time (ms)',
                            data: data.responseTimes
                        }]
                    }
                });
            });
    </script>
</body>
</html>