<?php
/** @var yii\web\View $this */
$this->title = 'Analytic Monitoring';

$this->registerJsFile('@web/js/isurf-api.js?v=1.1', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<div class="analytics-page" style="display: flex; flex-direction: column; gap: var(--space-6);">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div style="display: flex; flex-direction: column; gap: var(--space-2);">
            <h1 class="text-h2" style="font-weight: 700; color: var(--gray-900);">Analytic Monitoring (Per Area)</h1>
            <p class="text-body" style="color: var(--gray-500);">Perbandingan agregat dan performa tiap area tanam</p>
        </div>
        <select id="analytics-time-filter" class="ds-input" style="width: auto; font-size: 14px;">
            <option value="7d">7 Hari Terakhir</option>
            <option value="30d">30 Hari Terakhir</option>
            <option value="all">Keseluruhan</option>
        </select>
    </div>

    <!-- Area Tabs Container -->
    <div id="area-tabs" style="display: flex; gap: var(--space-2); border-bottom: 2px solid var(--gray-200); padding-bottom: 8px; overflow-x: auto;">
        <!-- Tabs will be injected here -->
    </div>

    <!-- Area Content Container -->
    <div id="area-content" style="display: flex; flex-direction: column; gap: var(--space-6);">
        <div style="text-align: center; padding: 40px; color: var(--gray-500);">
            Loading data area...
        </div>
    </div>
</div>

<script>
let currentAreaId = null;
let phChart = null;
let waterChart = null;
let tdsChart = null;

document.addEventListener('DOMContentLoaded', async function() {
    
    async function loadAreas() {
        const areas = await ISURF_API.getAreas();
        const tabsContainer = document.getElementById('area-tabs');
        
        if (!areas || areas.length === 0) {
            tabsContainer.innerHTML = '<span style="color: var(--gray-400);">Belum ada area terdaftar</span>';
            document.getElementById('area-content').innerHTML = '<div class="glass-card" style="text-align:center; padding:40px;">Data Kosong</div>';
            return;
        }

        tabsContainer.innerHTML = '';
        areas.forEach((area, index) => {
            const btn = document.createElement('button');
            btn.className = `area-tab-btn ${index === 0 ? 'active' : ''}`;
            btn.style.padding = '8px 16px';
            btn.style.border = 'none';
            btn.style.background = index === 0 ? 'var(--primary-100)' : 'transparent';
            btn.style.color = index === 0 ? 'var(--primary-700)' : 'var(--gray-600)';
            btn.style.fontWeight = '600';
            btn.style.borderRadius = '8px';
            btn.style.cursor = 'pointer';
            btn.textContent = area.name;
            btn.onclick = () => {
                document.querySelectorAll('.area-tab-btn').forEach(b => {
                    b.style.background = 'transparent';
                    b.style.color = 'var(--gray-600)';
                });
                btn.style.background = 'var(--primary-100)';
                btn.style.color = 'var(--primary-700)';
                renderAreaContent(area.id, area.name);
            };
            tabsContainer.appendChild(btn);
        });

        // Load first area by default
        renderAreaContent(areas[0].id, areas[0].name);
    }

    async function renderAreaContent(areaId, areaName) {
        currentAreaId = areaId;
        const content = document.getElementById('area-content');
        
        // Render skeletons/structure
        content.innerHTML = `
            <div class="ds-grid ds-grid-cols-1 md-grid-cols-4">
                <div class="glass-card">
                    <h4 class="text-caption" style="color: var(--gray-500); margin-bottom: 8px;">Rata-rata pH Air</h4>
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                        <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="avg-ph">--</span>
                    </div>
                </div>
                <div class="glass-card">
                    <h4 class="text-caption" style="color: var(--gray-500); margin-bottom: 8px;">Rata-rata TDS</h4>
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                        <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="avg-tds">--</span>
                        <span class="text-body font-bold" style="color: var(--gray-900);">ppm</span>
                    </div>
                </div>
                <div class="glass-card">
                    <h4 class="text-caption" style="color: var(--gray-500); margin-bottom: 8px;">Total Penggunaan Air</h4>
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                        <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="total-water">--</span>
                        <span class="text-body font-bold" style="color: var(--gray-900);">L</span>
                    </div>
                </div>
                <div class="glass-card">
                    <h4 class="text-caption" style="color: var(--gray-500); margin-bottom: 8px;">Anomali Data</h4>
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                        <span class="text-h2" style="font-weight: 700; color: var(--danger);" id="total-anomalies">0</span>
                        <span class="text-body font-bold" style="color: var(--gray-900);">Kejadian</span>
                    </div>
                </div>
            </div>

            <div class="ds-grid ds-grid-cols-1 lg-grid-cols-3">
                <div class="glass-card">
                    <h3 class="text-h3" style="font-weight: 600; margin-bottom: var(--space-4);">Trend Kondisi pH</h3>
                    <div style="position: relative; height: 260px; width: 100%;">
                        <canvas id="analyticsPhChart"></canvas>
                    </div>
                </div>
                <div class="glass-card">
                    <h3 class="text-h3" style="font-weight: 600; margin-bottom: var(--space-4);">Distribusi Penggunaan Air</h3>
                    <div style="position: relative; height: 260px; width: 100%;">
                        <canvas id="analyticsWaterChart"></canvas>
                    </div>
                </div>
                <div class="glass-card">
                    <h3 class="text-h3" style="font-weight: 600; margin-bottom: var(--space-4);">Trend Nutrisi (TDS)</h3>
                    <div style="position: relative; height: 260px; width: 100%;">
                        <canvas id="analyticsTdsChart"></canvas>
                    </div>
                </div>
            </div>
        `;

        loadAreaCharts(areaId);
    }

    async function loadAreaCharts(areaId) {
        // GH1 uses 'pH Air', GH2 uses 'pH Tanah'
        const phType = areaId === 1 ? 'pH Air' : (areaId === 2 ? 'pH Tanah' : 'pH Air');
        const phData = await ISURF_API.getHistory(areaId, phType, 24*7);
        const tdsData = await ISURF_API.getHistory(areaId, 'TDS', 24*7);
        const waterData = await ISURF_API.getWaterUsage(24*7); // Mocked globally per area for now
        
        // Update Aggregates
        if(phData && phData.length > 0) {
            const sum = phData.reduce((acc, curr) => acc + curr.avg_value, 0);
            document.getElementById('avg-ph').textContent = (sum / phData.length).toFixed(2);
        } else {
            document.getElementById('avg-ph').textContent = '--';
        }

        if(tdsData && tdsData.length > 0) {
            const sumTds = tdsData.reduce((acc, curr) => acc + curr.avg_value, 0);
            document.getElementById('avg-tds').textContent = (sumTds / tdsData.length).toFixed(0);
        } else {
            document.getElementById('avg-tds').textContent = '--';
        }

        document.getElementById('total-water').textContent = waterData.total_discharged.toFixed(1);

        // Render pH Chart
        const phCtx = document.getElementById('analyticsPhChart');
        if (phChart) phChart.destroy();
        phChart = new Chart(phCtx, {
            type: 'line',
            data: {
                labels: phData.map(d => ISURF_API.formatTimestamp(d.timestamp).split(' ')[0]),
                datasets: [{
                    label: 'pH Level',
                    data: phData.map(d => d.avg_value),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // Render Water Chart
        const wCtx = document.getElementById('analyticsWaterChart');
        if (waterChart) waterChart.destroy();
        waterChart = new Chart(wCtx, {
            type: 'bar',
            data: {
                labels: waterData.history.map(d => ISURF_API.formatTimestamp(d.timestamp).split(' ')[0]),
                datasets: [{
                    label: 'Water Usage (L)',
                    data: waterData.history.map(d => d.value),
                    backgroundColor: '#3B82F6',
                    borderRadius: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // Render TDS Chart
        const tdsCtx = document.getElementById('analyticsTdsChart');
        if (tdsChart) tdsChart.destroy();
        tdsChart = new Chart(tdsCtx, {
            type: 'line',
            data: {
                labels: tdsData.map(d => ISURF_API.formatTimestamp(d.timestamp).split(' ')[0]),
                datasets: [{
                    label: 'TDS (ppm)',
                    data: tdsData.map(d => d.avg_value),
                    borderColor: '#A855F7',
                    backgroundColor: 'rgba(168, 85, 247, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }

    loadAreas();
});
</script>
