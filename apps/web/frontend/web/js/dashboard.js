// dashboard.js

let tempChartInstance = null;
let waterChartInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. Fetch and Update Metrics ---
    async function updateDashboardMetrics() {
        try {
            const data = await ISURF_API.getLatestReadings();
            
            // Map latest reading per data_type globally
            const latestByType = {};
            if (data && data.length > 0) {
            for (const item of data) {
                latestByType[item.data_type] = item;
            }
        }

        const mappings = {
            'Suhu Udara': { val: 'metric-temp', status: 'status-temp' },
            'Kelembaban Udara': { val: 'metric-humidity', status: 'status-humidity' }
        };

        for (const [sensorName, uiIds] of Object.entries(mappings)) {
            if (latestByType[sensorName]) {
                const reading = latestByType[sensorName];
                const valEl = document.getElementById(uiIds.val);
                if (valEl) valEl.textContent = reading.avg_value.toFixed(1);

                if (uiIds.status) {
                    const statEl = document.getElementById(uiIds.status);
                    const maxThresh = reading.max_threshold !== null ? reading.max_threshold : Infinity;
                    const minThresh = reading.min_threshold !== null ? reading.min_threshold : -Infinity;
                    
                    if(reading.avg_value > maxThresh || reading.avg_value < minThresh) {
                        statEl.textContent = 'Peringatan';
                        statEl.className = 'ds-badge ds-badge-warning';
                    } else {
                        statEl.textContent = 'Normal';
                        statEl.className = 'ds-badge ds-badge-success';
                    }
                }
            } else {
                const valEl = document.getElementById(uiIds.val);
                if (valEl) valEl.textContent = '--';
                if (uiIds.status) {
                    const statEl = document.getElementById(uiIds.status);
                    if(statEl) {
                        statEl.textContent = 'Offline';
                        statEl.className = 'ds-badge ds-badge-danger';
                    }
                }
            }
        }
        
        // Fetch Water Usage
        const waterData = await ISURF_API.getWaterUsage();
        const waterValEl = document.getElementById('metric-water');
        if (waterValEl) waterValEl.textContent = waterData.remaining;
        const waterStatEl = document.getElementById('status-water');
        if (waterStatEl) {
            waterStatEl.textContent = 'Cukup';
            waterStatEl.className = 'ds-badge ds-badge-success';
            if (waterData.remaining < 200) {
                waterStatEl.textContent = 'Kritis';
                waterStatEl.className = 'ds-badge ds-badge-danger';
            }
        }

        // Admin Only: Fetch Pending Requests
        if (!window.isGuestUser) {
            try {
                const requests = await ISURF_API.getDataRequests();
                const pendingCount = requests.filter(r => r.status && r.status.toLowerCase() === 'pending').length;
                
                const reqValEl = document.getElementById('metric-requests');
                if (reqValEl) reqValEl.textContent = pendingCount;
                
                const reqStatEl = document.getElementById('status-requests');
                if (reqStatEl) {
                    if (pendingCount > 0) {
                        reqStatEl.textContent = 'Menunggu';
                        reqStatEl.className = 'ds-badge ds-badge-warning';
                    } else {
                        reqStatEl.textContent = 'Selesai';
                        reqStatEl.className = 'ds-badge ds-badge-success';
                    }
                }
            } catch (e) {
                console.error(e);
            }
        }
        
        } catch (error) {
            console.error("Gagal terhubung ke API Server:", error);
            document.querySelectorAll('[id^="status-"]').forEach(el => {
                el.textContent = 'Server Offline';
                el.className = 'ds-badge ds-badge-danger';
            });
            document.querySelectorAll('[id^="metric-"]').forEach(el => {
                if(el) el.textContent = 'Err';
            });
        }
    }

    // --- 2. Build Charts ---
    async function loadTempChart(hours) {
        const tempCtx = document.getElementById('tempChart');
        if (!tempCtx) return;
        
        const areaId = 1; // Default to area 1 for now
        const tempData = await ISURF_API.getHistory(areaId, 'Suhu Udara', hours);
        const labels = tempData.map(d => ISURF_API.formatTimestamp(d.timestamp).split(' ')[0]);
        const vals = tempData.map(d => d.avg_value);

        if (tempChartInstance) {
            tempChartInstance.data.labels = labels;
            tempChartInstance.data.datasets[0].data = vals;
            tempChartInstance.update();
        } else {
            tempChartInstance = new Chart(tempCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Suhu Udara (°C)',
                            data: vals,
                            borderColor: '#F87171',
                            backgroundColor: 'rgba(248, 113, 113, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(255, 255, 255, 0.05)' } },
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } }
                    }
                }
            });
        }
    }

    async function loadWaterChart(hours) {
        const waterCtx = document.getElementById('waterChart');
        if (!waterCtx) return;
        
        const waterData = await ISURF_API.getWaterUsage(hours);
        const labels = waterData.history.map(d => ISURF_API.formatTimestamp(d.timestamp).split(' ')[0]);
        const vals = waterData.history.map(d => d.value);

        if (waterChartInstance) {
            waterChartInstance.data.labels = labels;
            waterChartInstance.data.datasets[0].data = vals;
            waterChartInstance.update();
        } else {
            waterChartInstance = new Chart(waterCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Penggunaan Air (L)',
                            data: vals,
                            backgroundColor: '#60A5FA',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(255, 255, 255, 0.05)' } },
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 12 } }
                    }
                }
            });
        }
    }

    // Initial load
    updateDashboardMetrics();
    loadTempChart(24);
    loadWaterChart(24);

    // Event Listeners for Filters
    const timeFilterTemp = document.getElementById('time-filter-temp');
    if (timeFilterTemp) {
        timeFilterTemp.addEventListener('change', (e) => {
            const val = e.target.value;
            const hours = val === '7d' ? 168 : (val === '30d' ? 720 : 24);
            loadTempChart(hours);
        });
    }

    const timeFilterWater = document.getElementById('time-filter-water');
    if (timeFilterWater) {
        timeFilterWater.addEventListener('change', (e) => {
            const val = e.target.value;
            const hours = val === '7d' ? 168 : (val === '30d' ? 720 : 24);
            loadWaterChart(hours);
        });
    }

    setInterval(updateDashboardMetrics, 30000);
});
