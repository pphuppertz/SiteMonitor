<!DOCTYPE html>
<html>
<head>
    <title>SiteMonitor</title>
    <!-- <link rel="stylesheet" href="styles.css"> -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
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
                    console.log("IPv4 data:", data.ipv4Data); // Log the IPv4 data for debugging
                    console.log("IPv6 data:", data.ipv6Data); // Log the IPv6 data for debugging
                    console.log("Error data:", data.ipErrorData); // Log the error data for debugging

                    // console.log(data);
                    // console.log(Array.isArray(data));
                    
                    const labels = data.ipv4Data.map(x => x.timestamp).map(t => t.replace(' ', 'T'));

                    const ipv4Times  = data.ipv4Data.map(x => x.responseTime);
                    const ipv6Times  = data.ipv6Data.map(x => x.responseTime);
                    const errorTimes = data.ipErrorData.map(x => x.responseTime);
                    const ipv4DailyStats = data.ipv4DailyStats;
                    const ipv6DailyStats = data.ipv6DailyStats;
                    const ctx = document.getElementById('responseChart').getContext('2d');
                    
                    console.log("IPv4 daily stats:", ipv4DailyStats); // Log the averages for debugging
                    console.log("IPv6 daily stats:", ipv6DailyStats); // Log the averages for debugging
                    console.log(ipv4DailyStats['2026-07-10']['medianTime']); // Log the averages for a specific date for debugging
                    console.log(ipv6DailyStats['2026-07-10']['medianTime']); // Log the averages for a specific date for debugging
                    console.log(ipv4DailyStats['2026-07-10']['averageTime']);   
                    console.log(ipv6DailyStats['2026-07-10']['averageTime']);

                    ctx.canvas.style.maxHeight = '80vh';

                    responseChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
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
                                    backgroundColor: 'rgba(255, 0, 0, 0.1)',
                                    tension: 0.2
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
                                    maxScale: 10,
                                    limits: {
                                        y: { min: 0 }
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
                });
            }
    </script>
</body>
</html>