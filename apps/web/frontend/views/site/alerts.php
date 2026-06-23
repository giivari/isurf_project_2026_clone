<?php
/** @var yii\web\View $this */
$this->title = 'Alerts & Logs';
$this->registerJsFile('@web/js/isurf-api.js?v=1.3', ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<div style="display: flex; flex-direction: column; gap: var(--space-5);">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 class="text-h2" style="font-weight: 700; color: var(--gray-900);">Critical Alerts & Logs</h1>
            <p class="text-body" style="color: var(--gray-500);">Log peringatan Offline perangkat dan Anomali sensor</p>
        </div>
        <button id="btn-mark-read" class="ds-btn-outline" style="font-weight: 500;">Tandai Semua Dibaca</button>
    </div>

    <!-- Alerts List -->
    <div id="alerts-container" style="background: white; border-radius: var(--radius-md); box-shadow: var(--elevation-1); display: flex; flex-direction: column; border: 1px solid var(--gray-200); overflow: hidden;">
        <div style="padding: 40px; text-align: center;">
            <p class="text-body" style="color: var(--gray-500); font-weight: 500;">Memuat data alert...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    async function loadAlerts() {
        const alerts = await ISURF_API.getAlerts();
        const container = document.getElementById('alerts-container');
        
        if (!alerts || alerts.length === 0) {
            container.innerHTML = `
                <div style="padding: 40px; text-align: center;">
                    <svg class="w-8 h-8" style="margin: 0 auto 16px auto; color: var(--gray-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-body" style="color: var(--gray-500); font-weight: 500;">Tidak ada log kritikal baru.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = '';
        alerts.forEach(alert => {
            const isCritical = alert.alert_type.toLowerCase() === 'critical';
            const color = isCritical ? 'var(--danger)' : 'var(--warning)';
            const bg = isCritical ? 'var(--danger-light)' : 'var(--warning-light)';
            
            const div = document.createElement('div');
            div.style.padding = '16px 24px';
            div.style.borderBottom = '1px solid var(--gray-200)';
            div.style.display = 'flex';
            div.style.gap = '16px';
            div.style.alignItems = 'flex-start';
            div.style.background = alert.is_read ? 'white' : 'var(--primary-50)';
            
            div.innerHTML = `
                <div style="padding: 8px; border-radius: 8px; background: ${bg}; color: ${color};">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <h4 class="text-body font-bold" style="color: var(--gray-900);">${alert.sensor_id} - ${alert.alert_type}</h4>
                        <span class="text-caption" style="color: var(--gray-500);">${ISURF_API.formatTimestamp(alert.created_at)}</span>
                    </div>
                    <p class="text-body" style="color: var(--gray-600); margin-bottom: 8px;">${alert.message}</p>
                    <div style="display: flex; gap: 8px;">
                        <span class="ds-badge ds-badge-gray">Value: ${alert.value.toFixed(1)}</span>
                        ${alert.threshold_exceeded ? `<span class="ds-badge ds-badge-danger">Exceeded: ${alert.threshold_exceeded.toFixed(1)}</span>` : ''}
                    </div>
                </div>
            `;
            container.appendChild(div);
        });
    }

    document.getElementById('btn-mark-read').addEventListener('click', async () => {
        await ISURF_API.markAllAlertsRead();
        loadAlerts();
    });

    loadAlerts();
});
</script>
