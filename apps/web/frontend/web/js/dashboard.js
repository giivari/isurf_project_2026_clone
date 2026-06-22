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
            'Kelembaban Udara': { val: 'metric-humidity', status: 'status-humidity' },
            'TDS Air': { val: 'metric-tds', status: 'status-tds' },
            'pH Air': { val: 'metric-ph', status: 'status-ph' }
        };

        for (const [sensorName, uiIds] of Object.entries(mappings)) {
            if (latestByType[sensorName]) {
                const reading = latestByType[sensorName];
                const valEl = document.getElementById(uiIds.val);
                if (valEl) {
                    if (sensorName === 'TDS Air') {
                        valEl.textContent = Math.round(reading.avg_value);
                    } else {
                        valEl.textContent = reading.avg_value.toFixed(1);
                    }
                }

                if (uiIds.status) {
                    const statEl = document.getElementById(uiIds.status);
                    let isWarning = false;
                    
                    if (sensorName === 'Suhu Udara' && (reading.avg_value > 30 || reading.avg_value < 15)) isWarning = true;
                    if (sensorName === 'Kelembaban Udara' && (reading.avg_value < 40 || reading.avg_value > 80)) isWarning = true;
                    if (sensorName === 'TDS Air' && (reading.avg_value > 500)) isWarning = true;
                    if (sensorName === 'pH Air' && (reading.avg_value < 5.5 || reading.avg_value > 8.5)) isWarning = true;

                    if (isWarning) {
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
        const labels = tempData.map(d => {
            const timeStr = ISURF_API.formatTimestamp(d.timestamp).split(' ')[0];
            if (hours <= 24) return timeStr;
            const dateStr = new Date(d.timestamp + 'Z').toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
            return `${dateStr} ${timeStr}`;
        });
        const vals = tempData.map(d => d.avg_value);

        if (tempChartInstance) {
            tempChartInstance.data.labels = labels;
            tempChartInstance.data.datasets[0].data = vals;
            tempChartInstance.update('none'); // Update without full animation flash
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
        
        const areaId = 1;
        const tdsData = await ISURF_API.getHistory(areaId, 'TDS Air', hours);
        const phData = await ISURF_API.getHistory(areaId, 'pH Air', hours);
        
        // Asumsi timestamps TDS dan pH sama karena digrouping berdasarkan query backend
        const labels = tdsData.map(d => {
            const timeStr = ISURF_API.formatTimestamp(d.timestamp).split(' ')[0];
            if (hours <= 24) return timeStr;
            const dateStr = new Date(d.timestamp + 'Z').toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
            return `${dateStr} ${timeStr}`;
        });
        
        const valsTds = tdsData.map(d => d.avg_value);
        const valsPh = phData.map(d => d.avg_value);

        if (waterChartInstance) {
            waterChartInstance.data.labels = labels;
            waterChartInstance.data.datasets[0].data = valsTds;
            waterChartInstance.data.datasets[1].data = valsPh;
            waterChartInstance.update('none');
        } else {
            waterChartInstance = new Chart(waterCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'TDS Air (ppm)',
                            data: valsTds,
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'pH Air',
                            data: valsPh,
                            borderColor: '#EAB308',
                            backgroundColor: 'rgba(234, 179, 8, 0.1)',
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        y: { 
                            type: 'linear', 
                            display: true, 
                            position: 'left',
                            title: { display: true, text: 'TDS (ppm)' }
                        },
                        y1: { 
                            type: 'linear', 
                            display: true, 
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'pH' }
                        },
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } }
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

    // Auto Update Every 5 Seconds for High Resolution
    function autoUpdateAll() {
        updateDashboardMetrics();
        
        const tempVal = timeFilterTemp ? timeFilterTemp.value : '24h';
        const tempHours = tempVal === '7d' ? 168 : (tempVal === '30d' ? 720 : 24);
        loadTempChart(tempHours);

        const waterVal = timeFilterWater ? timeFilterWater.value : '24h';
        const waterHours = waterVal === '7d' ? 168 : (waterVal === '30d' ? 720 : 24);
        loadWaterChart(waterHours);
    }

    setInterval(autoUpdateAll, 5000);
});
