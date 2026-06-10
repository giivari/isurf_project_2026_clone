// dashboard.js

let qualityChartInstance = null;
let tempChartInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. Fetch and Update Metrics ---
    async function updateDashboardMetrics() {
        const data = await iSurfAPI.getLatestReadings();
        if (!data) return;

        const mappings = {
            'Water Quality (TDS)': { val: 'metric-tds', status: 'status-tds' },
            'Water pH Level': { val: 'metric-ph', status: 'status-ph' },
            'Air Temperature': { val: 'metric-temp', status: 'status-temp' }
        };

        for (const [sensorName, uiIds] of Object.entries(mappings)) {
            if (data[sensorName]) {
                const reading = data[sensorName];
                const valEl = document.getElementById(uiIds.val);
                if (valEl) valEl.textContent = reading.value;

                if (uiIds.status) {
                    const statEl = document.getElementById(uiIds.status);
                    if (sensorName.includes('pH')) {
                        if(reading.value < 5.5 || reading.value > 7.5) {
                            statEl.textContent = 'warning';
                            statEl.className = 'ds-badge ds-badge-warning';
                        } else {
                            statEl.textContent = 'optimal';
                            statEl.className = 'ds-badge ds-badge-success';
                        }
                    } else if (sensorName.includes('Temperature')) {
                        if(reading.value > 30) {
                            statEl.textContent = 'hot';
                            statEl.className = 'ds-badge ds-badge-danger';
                        } else {
                            statEl.textContent = 'good';
                            statEl.className = 'ds-badge ds-badge-info';
                        }
                    } else if (sensorName.includes('TDS')) {
                        if(reading.value > 800) {
                            statEl.textContent = 'high';
                            statEl.className = 'ds-badge ds-badge-warning';
                        } else {
                            statEl.textContent = 'optimal';
                            statEl.className = 'ds-badge ds-badge-success';
                        }
                    }
                }
            }
        }
        
        try {
            const devices = await iSurfAPI.getDevices();
            const onlineCount = devices.filter(d => d.status === 'online').length;
            const totalCount = devices.length;
            
            const nodesEl = document.getElementById('metric-nodes');
            if(nodesEl) nodesEl.textContent = onlineCount;
            
            const totalEl = document.getElementById('total-devices');
            if(totalEl) totalEl.textContent = totalCount;
            
            const lastUpdEl = document.getElementById('last-updated');
            if(lastUpdEl) lastUpdEl.textContent = iSurfAPI.formatTimeWithTZ(new Date());
        } catch (e) {
            console.error("Failed to fetch device stats", e);
        }
    }

    // --- 2. Chart.js Initialization ---
    function initCharts() {
        const blue500 = '#3B82F6';
        const green500 = '#10B981';
        const orange500 = '#F97316';
        
        const qCtx = document.getElementById('qualityChart');
        if (qCtx) {
            qualityChartInstance = new Chart(qCtx, {
                type: 'line',
                data: {
                    labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                    datasets: [
                        {
                            label: 'TDS (ppm)',
                            data: [420, 430, 425, 450, 480, 460, 440],
                            borderColor: blue500,
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'pH Level',
                            data: [6.5, 6.4, 6.6, 6.2, 6.1, 6.3, 6.5],
                            borderColor: green500,
                            backgroundColor: 'transparent',
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: { type: 'linear', display: true, position: 'left', grid: { color: '#f1f5f9' } },
                        y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        const tCtx = document.getElementById('tempChart');
        if (tCtx) {
            tempChartInstance = new Chart(tCtx, {
                type: 'line',
                data: {
                    labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                    datasets: [
                        {
                            label: 'Temperature (°C)',
                            data: [22, 21, 24, 28, 29, 26, 23],
                            borderColor: orange500,
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: { beginAtZero: false, grid: { color: '#f1f5f9' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    }

    // --- 3. Load Device Health ---
    async function loadDeviceHealth() {
        const listEl = document.getElementById('device-health-list');
        if(!listEl) return;
        
        try {
            const devices = await iSurfAPI.getDevices();
            listEl.innerHTML = '';
            
            if(devices.length === 0) {
                listEl.innerHTML = '<p class="text-gray-500">No devices registered.</p>';
                return;
            }
            
            devices.forEach(d => {
                const isOnline = d.status === 'online';
                const statusColor = isOnline ? '#10B981' : '#EF4444';
                const badgeClass = isOnline ? 'ds-badge-success' : 'ds-badge-danger';
                
                // Mock uptime and signal for visual realism matching reference
                const uptime = isOnline ? (95 + Math.random() * 4.9).toFixed(1) + '%' : 'N/A';
                const signal = isOnline ? Math.floor(70 + Math.random() * 30) + '%' : 'N/A';
                
                const card = document.createElement('div');
                card.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 12px; border: 1px solid var(--gray-100); border-radius: 8px; background-color: #FAFAFA;';
                card.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <svg class="w-5 h-5" style="color: ${statusColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                        <div>
                            <p class="text-body font-bold" style="color: var(--gray-900); margin: 0 0 4px 0;">${d.name}</p>
                            <p class="text-caption text-gray-500" style="margin: 0;">${d.device_code}</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 16px; align-items: center; text-align: center;">
                        <div style="display: none; @media(min-width: 640px){ display: block; }">
                            <p class="text-caption text-gray-500" style="margin: 0 0 4px 0;">Uptime</p>
                            <p class="text-body font-bold" style="color: var(--gray-900); margin: 0;">${uptime}</p>
                        </div>
                        <div style="display: none; @media(min-width: 640px){ display: block; }">
                            <p class="text-caption text-gray-500" style="margin: 0 0 4px 0;">Signal</p>
                            <p class="text-body font-bold" style="color: var(--gray-900); margin: 0;">${signal}</p>
                        </div>
                        <span class="ds-badge ${badgeClass}">${d.status}</span>
                    </div>
                `;
                listEl.appendChild(card);
            });
        } catch(e) {
            listEl.innerHTML = '<p class="text-red-500">Failed to load devices.</p>';
        }
    }

    // --- 4. Irrigation Control (Dashboard context) ---
    async function loadPumpStatus() {
        const devices = await iSurfAPI.getDevices();
        const irrigationDev = devices.find(d => d.type === 'esp32_irrigation');
        
        if(irrigationDev) {
            window.dashboardIrrigationDevId = irrigationDev.id;
            const res = await iSurfAPI.getPumpStatus(irrigationDev.id);
            updatePumpUI(res.pump_on);
        } else {
            document.getElementById('dash-pump-status').textContent = 'No Controller Found';
            document.getElementById('valve-btn').disabled = true;
            document.getElementById('valve-btn').style.opacity = '0.5';
        }
    }

    function updatePumpUI(isOn) {
        window.isDashPumpOn = isOn;
        const btn = document.getElementById('valve-btn');
        const status = document.getElementById('dash-pump-status');
        
        if (isOn) {
            btn.textContent = 'Turn Off';
            btn.style.backgroundColor = '#EF4444'; // Red for Off
            status.textContent = 'Active';
            status.style.color = '#10B981'; // Green text
        } else {
            btn.textContent = 'Turn On';
            btn.style.backgroundColor = 'var(--gray-700)';
            status.textContent = 'Inactive';
            status.style.color = 'var(--gray-500)';
        }
    }

    window.toggleMainPump = async function() {
        if(!window.dashboardIrrigationDevId) return;
        const newState = !window.isDashPumpOn;
        updatePumpUI(newState);
        
        const success = await iSurfAPI.triggerManualPump(window.dashboardIrrigationDevId, newState);
        if(!success) {
            alert('Failed to send command');
            updatePumpUI(!newState); // revert
        }
    };

    // Initial load and interval
    updateDashboardMetrics();
    initCharts();
    loadDeviceHealth();
    loadPumpStatus();
    
    setInterval(() => {
        updateDashboardMetrics();
        loadDeviceHealth();
        loadPumpStatus();
    }, 30000); // Update every 30s
});
