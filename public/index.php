<!DOCTYPE html>
<html>
<head>
    <title>SiteMonitor</title>
    <!-- <link rel="stylesheet" href="styles.css"> -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>SiteMonitor Dashboard</h1>
    <select id="siteSelector">
        <option value="">Select a site</option>
    </select>
    <canvas id="responseChart"></canvas>
    
    <script>
        fetch('api/sites.php')
            .then(r => r.json())
            .then(sites => {
                console.log("Fetched sites:", sites);   // <-- Debug here
                const sel = document.getElementById('siteSelector');
                console.log("Selector element:", sel);  // <-- Debug here
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
            fetch("api/data.php?site=" + encodeURIComponent(site))
                .then(r => r.json())
                .then(data => {
                    const labels = data.ipv4Data.map(x => x.timestamp);

                    const ipv4Times  = data.ipv4Data.map(x => x.responseTime);
                    const ipv6Times  = data.ipv6Data.map(x => x.responseTime);
                    const errorTimes = data.ipErrorData.map(x => x.responseTime);

                    const ctx = document.getElementById('responseChart').getContext('2d');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'IPv4',
                                    data: ipv4Times,
                                    borderColor: 'blue',
                                    backgroundColor: 'rgba(0, 0, 255, 0.1)',
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
                        }
                    });     
             });
        }


    </script>
</body>
</html>