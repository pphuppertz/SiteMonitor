<!DOCTYPE html>
<html>

<head>
    <title>SiteMonitor</title>
    <!-- <link rel="stylesheet" href="styles.css"> -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1"></script>
</head>

<body>
    <h1>SiteMonitor Dashboard</h1>
    <select id="siteSelector">
        <option value="">Select a site</option>
    </select>
    <canvas id="responseChart"></canvas>
    <button onclick="responseChart.resetZoom()">Reset Zoom</button>
    <script>
        Chart.register(window.ChartZoom);
    </script>

    <script>        
        let responseChart = null;
        fetch('api/sites.php')
            .then(r => r.json())
            .then(sites => {
                const sel = document.getElementById('siteSelector');
                sites.forEach(site => {
                    const opt = document.createElement('option');
                    opt.value = site;
                    opt.textContent = site;
                    sel.appendChild(opt);
                });
                sel.addEventListener('change', () => {
                    loadSiteData(sel.value);
                });
            });

        // Function to load data + draw chart
        function loadSiteData(site) {
            if (responseChart) {
                responseChart.destroy();
            }
            fetch("api/data.php?site=" + encodeURIComponent(site))
                .then(r => r.json())
                .then(data => {
                    const ipv4Times = data.ipv4Data.map(e => ({
                        x: Date.parse(e.timestamp),
                        y: e.responseTime
                    }));
                    const ipv6Times = data.ipv6Data.map(e => ({
                        x: Date.parse(e.timestamp),
                        y: e.responseTime
                    }));
                    const errorTimes = data.ipErrorData.map(e => ({
                        x: Date.parse(e.timestamp),
                        y: e.responseTime
                    }));
                    const ipv4DailyMedian = Object.entries(data.ipv4DailyStats).map(([date, s]) => ({
                        x: Date.parse(date + "T00:00:00"),
                        y: s.medianTime
                    }));
                    const ipv6DailyMedian = Object.entries(data.ipv6DailyStats).map(([date, s]) => ({
                        x: Date.parse(date + "T00:00:00"),
                        y: s.medianTime
                    }));

                    const ctx = document.getElementById('responseChart').getContext('2d');

                    ctx.canvas.style.maxHeight = '80vh';

                    responseChart = new Chart(ctx, {
                        type: 'scatter',
                        data: {
                            datasets: [{
                                    label: 'Daily IPv4Median',
                                    data: ipv4DailyMedian,
                                    borderColor: 'orange',
                                    backgroundColor: 'orange',
                                    pointRadius: 6,
                                    showLine: true,
                                    fill: false,
                                },
                                {
                                    label: 'Daily IPv6 Median',
                                    data: ipv6DailyMedian,
                                    borderColor: 'yellow',
                                    backgroundColor: 'yellow',
                                    pointRadius: 6,
                                    showLine: true,
                                    fill: false,
                                },
                                {
                                    label: 'IPv4',
                                    data: ipv4Times,
                                    borderColor: 'blue',
                                    backgroundColor: 'rgba(156, 156, 229, 0.1)',
                                    tension: 0.2
                                },
                                {
                                    label: 'IPv6',
                                    data: ipv6Times,
                                    borderColor: 'green',
                                    backgroundColor: 'rgba(0, 255, 0, 0.1)',
                                    tension: 0.2
                                },
                                {
                                    label: 'Errors',
                                    data: errorTimes,
                                    borderColor: 'red',
                                    backgroundColor: 'red',
                                    tension: 0.2,
                                    pointRadius: 6,
                                    showLine: false,
                                    fill: true,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    type: 'time',
                                    time: {
                                        tooltipFormat: 'yyyy-MM-dd HH:mm:ss',
                                        unit: 'hour',
                                        stepSize: 1,
                                    },
                                    ticks: {
                                        source: 'auto',
                                        callback: function(value) {
                                            const d = new Date(value);

                                            if (d.getHours() === 0 && d.getMinutes() === 0) {
                                                return d.toLocaleDateString('nl-NL');
                                            }

                                            return d.toLocaleTimeString([], {
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                hour12: false
                                            });
                                        }
                                    }
                                }
                            },
                            plugins: {
                                zoom: {
                                    maxScale: 100,
                                    limits: {
                                        y: {
                                            min: 0
                                        }
                                    },
                                    zoom: {
                                        wheel: {
                                            enabled: true
                                        },
                                        pinch: {
                                            enabled: true
                                        },
                                        mode: 'xy'
                                    },
                                    pan: {
                                        enabled: true,
                                        mode: 'xy'
                                    }
                                }
                            }
                        }
                    });
                    enablePan(responseChart);
                });
        }

        function enablePan(chart) {
            const canvas = chart.canvas;
            let isDragging = false;
            let lastX = 0;

            canvas.addEventListener('mousedown', (ev) => {
                isDragging = true;
                lastX = ev.clientX;
            });

            canvas.addEventListener('mousemove', (ev) => {
                if (!isDragging) return;

                const dx = ev.clientX - lastX;
                lastX = ev.clientX;
                const damping = 0.5;   // hydropneumatic suspension
                const smoothedDx = dx * damping;
                chart.pan({ x: -smoothedDx});                
            });

            canvas.addEventListener('mouseup', (ev) => {
                isDragging = false;                
            });

            canvas.addEventListener('mouseleave', (ev) => {
                isDragging = false;
            });
        }
    </script>
</body>
</html>