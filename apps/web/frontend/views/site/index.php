<?php
/** @var yii\web\View $this */

$this->title = 'Dashboard';


// Dashboard specific scripts
$this->registerJsFile('@web/js/isurf-api.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('@web/js/dashboard.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<!-- Minimalist Industrial Design Dashboard (AgriSmart Inspired) -->
<div style="display: flex; flex-direction: column; gap: var(--space-6);">
    
    <!-- Header -->
    <div style="display: flex; flex-direction: column; gap: var(--space-2);">
        <h1 class="text-h2" style="font-weight: 700; color: var(--gray-900);">iSurf Smart Greenhouse Dashboard</h1>
        <p class="text-body" style="color: var(--gray-500);">Real-time monitoring and control system</p>
    </div>

    <!-- Metric Cards (4 Columns) -->
    <div class="ds-grid ds-grid-cols-1 md-grid-cols-2 lg-grid-cols-4">
        
        <!-- TDS Card -->
        <div style="background: white; border-radius: var(--radius-md); box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: var(--space-5); border: 1px solid var(--gray-100);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div style="padding: 8px; background-color: #EFF6FF; border-radius: 50%; color: #3B82F6;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <span class="ds-badge ds-badge-success" style="border-radius: 12px;" id="status-tds">optimal</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-bottom: 4px;">
                <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="metric-tds">--</span>
                <span class="text-body font-bold" style="color: var(--gray-900);">ppm</span>
            </div>
            <p class="text-body" style="color: var(--gray-500); margin-bottom: 8px;">Water TDS Level</p>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                <span style="color: var(--gray-400);">Target: 400-600 ppm</span>
                <span style="color: #10B981; display: flex; align-items: center;"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg> 2.1%</span>
            </div>
        </div>

        <!-- pH Level Card -->
        <div style="background: white; border-radius: var(--radius-md); box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: var(--space-5); border: 1px solid var(--gray-100);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div style="padding: 8px; background-color: #FEF2F2; border-radius: 50%; color: #EF4444;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547"></path></svg>
                </div>
                <span class="ds-badge ds-badge-info" style="border-radius: 12px;" id="status-ph">normal</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-bottom: 4px;">
                <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="metric-ph">--</span>
                <span class="text-body font-bold" style="color: var(--gray-900);">pH</span>
            </div>
            <p class="text-body" style="color: var(--gray-500); margin-bottom: 8px;">Water Acidity</p>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                <span style="color: var(--gray-400);">Target: 5.5-6.5</span>
                <span style="color: #EF4444; display: flex; align-items: center;"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg> 1.2%</span>
            </div>
        </div>

        <!-- Temperature Card -->
        <div style="background: white; border-radius: var(--radius-md); box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: var(--space-5); border: 1px solid var(--gray-100);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div style="padding: 8px; background-color: #ECFEFF; border-radius: 50%; color: #06B6D4;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <span class="ds-badge ds-badge-info" style="border-radius: 12px;" id="status-temp">good</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-bottom: 4px;">
                <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="metric-temp">--</span>
                <span class="text-body font-bold" style="color: var(--gray-900);">°C</span>
            </div>
            <p class="text-body" style="color: var(--gray-500); margin-bottom: 8px;">Air Temperature</p>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                <span style="color: var(--gray-400);">Optimal: 15-35°C</span>
                <span style="color: var(--gray-400);">Avg: 25.1°C</span>
            </div>
        </div>

        <!-- Active Nodes Card -->
        <div style="background: white; border-radius: var(--radius-md); box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: var(--space-5); border: 1px solid var(--gray-100);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div style="padding: 8px; background-color: #FEF9C3; border-radius: 50%; color: #EAB308;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"></path></svg>
                </div>
                <span class="ds-badge ds-badge-warning" style="border-radius: 12px;" id="status-nodes">stable</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-bottom: 4px;">
                <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="metric-nodes">--</span>
                <span class="text-body font-bold" style="color: var(--gray-900);">Active</span>
            </div>
            <p class="text-body" style="color: var(--gray-500); margin-bottom: 8px;">Connected Nodes</p>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                <span style="color: var(--gray-400);">Total Registered: <span id="total-devices">--</span></span>
            </div>
        </div>
    </div>

    <!-- Charts Row (2 Columns) -->
    <div class="ds-grid ds-grid-cols-1 lg-grid-cols-2">
        
        <!-- Quality Trend Chart -->
        <div style="background: white; padding: var(--space-5); border-radius: var(--radius-md); box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--gray-100); display: flex; flex-direction: column;">
            <div style="margin-bottom: var(--space-4);">
                <h3 class="text-h3" style="font-weight: 600; color: var(--gray-900);">Water Quality Trend</h3>
                <p class="text-caption text-gray-500">Last 24 hours</p>
            </div>
            <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="qualityChart"></canvas>
            </div>
        </div>

        <!-- Temperature Trend Chart -->
        <div style="background: white; padding: var(--space-5); border-radius: var(--radius-md); box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--gray-100); display: flex; flex-direction: column;">
            <div style="margin-bottom: var(--space-4);">
                <h3 class="text-h3" style="font-weight: 600; color: var(--gray-900);">Temperature Trend</h3>
                <p class="text-caption text-gray-500">Last 24 hours</p>
            </div>
            <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="tempChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Controls & Alerts Row -->
    <div class="ds-grid ds-grid-cols-1 lg-grid-cols-2">
        
        <?php if (!Yii::$app->user->isGuest): ?>
        <!-- Irrigation Control -->
        <div style="background: white; padding: var(--space-5); border-radius: var(--radius-md); box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--gray-100);">
            <h3 class="text-h3" style="font-weight: 600; color: var(--gray-900); margin-bottom: var(--space-4);">Irrigation Control</h3>
            
            <div style="border: 1px solid var(--gray-100); border-radius: var(--radius-md); padding: var(--space-4); margin-bottom: var(--space-4); background-color: var(--gray-50);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path></svg>
                        <p class="text-body font-bold" style="color: var(--gray-900); margin: 0;">Main Pump Valve</p>
                    </div>
                    <button id="valve-btn" onclick="toggleMainPump()" style="background-color: var(--gray-700); color: white; border: none; border-radius: 4px; padding: 6px 16px; font-weight: 600; cursor: pointer;">Turn On</button>
                </div>
                <p class="text-caption text-gray-500" style="margin: 0;">Status: <span id="dash-pump-status" style="font-weight: 600;">Inactive</span></p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--gray-200); padding-bottom: 8px;">
                    <span class="text-body text-gray-500">Flow Rate</span>
                    <span class="text-body font-bold">45 L/min</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--gray-200); padding-bottom: 8px;">
                    <span class="text-body text-gray-500">Total Today</span>
                    <span class="text-body font-bold">1,240 L</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-body text-gray-500">Runtime</span>
                    <span class="text-body font-bold">2h 15m</span>
                </div>
            </div>
            
            <a href="<?= yii\helpers\Url::to(['site/irrigation']) ?>" style="display: block; width: 100%; text-align: center; padding: 10px; text-decoration: none; color: var(--gray-700); background-color: var(--gray-100); border-radius: 6px; font-weight: 600; transition: background-color 0.2s;">
                Schedule Irrigation
            </a>
        </div>
        <?php endif; ?>

        <!-- Recent Alerts -->
        <div style="background: white; padding: var(--space-5); border-radius: var(--radius-md); box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--gray-100);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
                <h3 class="text-h3" style="font-weight: 600; color: var(--gray-900);">Recent Alerts</h3>
                <a href="#" style="color: var(--primary-600); font-weight: 600; font-size: 13px; text-decoration: none;">View All</a>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                <!-- Alert 1 -->
                <div style="display: flex; gap: 16px; padding: 12px; border: 1px solid var(--gray-100); border-radius: 8px;">
                    <div style="background-color: #FFF7ED; color: #F97316; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <p class="text-body font-bold" style="color: var(--gray-900); margin: 0 0 4px 0;">TDS Level High</p>
                        <p class="text-caption text-gray-500" style="margin: 0 0 4px 0;">TDS level above 600ppm. Flush recommended.</p>
                        <p class="text-caption" style="color: var(--gray-400); margin: 0; font-size: 11px;">2 min ago</p>
                    </div>
                </div>

                <!-- Alert 2 -->
                <div style="display: flex; gap: 16px; padding: 12px; border: 1px solid var(--gray-100); border-radius: 8px;">
                    <div style="background-color: #EFF6FF; color: #3B82F6; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-body font-bold" style="color: var(--gray-900); margin: 0 0 4px 0;">Irrigation Completed</p>
                        <p class="text-caption text-gray-500" style="margin: 0 0 4px 0;">Morning irrigation cycle finished successfully.</p>
                        <p class="text-caption" style="color: var(--gray-400); margin: 0; font-size: 11px;">15 min ago</p>
                    </div>
                </div>

                <!-- Alert 3 -->
                <div style="display: flex; gap: 16px; padding: 12px; border: 1px solid var(--gray-100); border-radius: 8px;">
                    <div style="background-color: #ECFCCB; color: #65A30D; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-body font-bold" style="color: var(--gray-900); margin: 0 0 4px 0;">Optimal pH Achieved</p>
                        <p class="text-caption text-gray-500" style="margin: 0 0 4px 0;">Water pH is within target range.</p>
                        <p class="text-caption" style="color: var(--gray-400); margin: 0; font-size: 11px;">1 hour ago</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <!-- Device Health Row -->
    <div style="background: white; padding: var(--space-5); border-radius: var(--radius-md); box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--gray-100);">
        <h3 class="text-h3" style="font-weight: 600; color: var(--gray-900); margin-bottom: var(--space-4);">Device Health Monitor</h3>
        <div id="device-health-list" style="display: grid; grid-template-columns: 1fr; gap: 16px; @media(min-width: 1024px){ grid-template-columns: 1fr 1fr; }">
            <p class="text-gray-500">Loading devices...</p>
        </div>
    </div>
</div>

<style>
@media (min-width: 640px) {
    div[style*="grid-template-columns: repeat(1, 1fr)"] {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}
@media (min-width: 1024px) {
    div[style*="grid-template-columns: repeat(1, 1fr)"] {
        grid-template-columns: repeat(4, 1fr) !important;
    }
    div[style*="grid-template-columns: 1fr"] {
        grid-template-columns: 1fr 1fr !important;
    }
}
</style>
