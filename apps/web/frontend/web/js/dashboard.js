// dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. Fetch and Update Metrics ---
    async function updateDashboardMetrics() {
        const data = await iSurfAPI.getLatestReadings();
        if (!data) return;

        // Map sensor names to UI elements (assuming sensor names match seed.sql)
        const mappings = {
            'Soil Moisture Sensor 1': { val: 'metric-moisture', status: 'status-moisture' },
            'Air Temperature': { val: 'metric-temp', status: 'status-temp' },
            'Water Tank Level': { val: 'metric-water', progress: 'progress-water' },
            'Water Quality (TDS)': { val: 'metric-tds', status: 'status-tds' }
        };

        for (const [sensorName, uiIds] of Object.entries(mappings)) {
            if (data[sensorName]) {
                const reading = data[sensorName];
                const valEl = document.getElementById(uiIds.val);
                if (valEl) valEl.textContent = reading.value;

                if (uiIds.progress) {
                    const progEl = document.getElementById(uiIds.progress);
                    if (progEl) progEl.style.width = Math.min(100, Math.max(0, reading.value)) + '%';
                }

                // Status logic based on DS tokens
                if (uiIds.status) {
                    const statEl = document.getElementById(uiIds.status);
                    if (sensorName.includes('Moisture') && reading.value < 45) {
                        statEl.textContent = 'Warning';
                        statEl.className = 'ds-badge ds-badge-warning';
                    } else if (sensorName.includes('Temperature') && reading.value > 30) {
                        statEl.textContent = 'Hot';
                        statEl.className = 'ds-badge ds-badge-danger';
                    } else if (sensorName.includes('TDS') && reading.value > 600) {
                        statEl.textContent = 'High';
                        statEl.className = 'ds-badge ds-badge-warning';
                    } else {
                        statEl.textContent = 'Normal';
                        statEl.className = 'ds-badge ds-badge-success';
                    }
                }
            }
        }
        
        try {
            const stats = await iSurfAPI.getDashboardStats();
            
            // Update basic counts
            document.getElementById('total-devices').textContent = stats.total_devices;
            document.getElementById('active-sensors').textContent = stats.active_sensors;
            document.getElementById('online-status').textContent = `${stats.online_devices} Online`;
            document.getElementById('last-updated').textContent = iSurfAPI.formatTimeWithTZ(new Date());
        } catch (e) {
            console.error("Failed to fetch dashboard stats", e);
        }
    }

    // --- 2. Chart.js Initialization ---
    // Colors based on Design System
    const blue500 = '#3B82F6';
    const orange500 = '#F97316';
    const ctx = document.getElementById('mainChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                datasets: [
                    {
                        label: 'Soil Moisture (%)',
                        data: [65, 62, 58, 50, 45, 75, 72],
                        borderColor: blue500,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Temperature (°C)',
                        data: [22, 21, 24, 28, 29, 26, 23],
                        borderColor: orange500,
                        backgroundColor: 'transparent',
                        tension: 0.4,
                        borderDash: [5, 5]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // --- 3. Toggle Valve ---
    const valveBtn = document.getElementById('valve-toggle');
    const valveKnob = document.getElementById('valve-knob');
    const valveText = document.getElementById('valve-status-text');
    let isValveOn = false;

    if (valveBtn) {
        valveBtn.addEventListener('click', () => {
            isValveOn = !isValveOn;
            if (isValveOn) {
                valveBtn.style.backgroundColor = 'var(--primary-500)';
                valveKnob.style.transform = 'translateX(28px)';
                valveText.textContent = 'Currently ON (Manual)';
                valveText.style.color = 'var(--primary-600)';
            } else {
                valveBtn.style.backgroundColor = 'var(--gray-200)';
                valveKnob.style.transform = 'translateX(4px)';
                valveText.textContent = 'Currently OFF';
                valveText.style.color = 'var(--gray-400)';
            }
        });
    }

    // Initial load and interval
    updateDashboardMetrics();
    setInterval(updateDashboardMetrics, 30000); // Update every 30s
});
