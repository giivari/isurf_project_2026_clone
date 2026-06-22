<?php
/** @var yii\web\View $this */

$this->title = 'Dashboard';

// Dashboard specific scripts
$this->registerJsFile('@web/js/isurf-api.js?v=1.1', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('@web/js/dashboard.js?v=' . time(), ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<script>
    window.appBaseUrl = '<?= yii\helpers\Url::to('@web') ?>';
    window.isGuestUser = <?= Yii::$app->user->isGuest ? 'true' : 'false' ?>;
</script>

<?php if (Yii::$app->user->isGuest): ?>
<!-- Guest Hero Section -->
<div style="position: relative; width: 100%; min-height: 80vh; display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-8); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--elevation-2);">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; overflow: hidden;" id="hero-slideshow">
        <div id="slide-track" style="display: flex; width: 100%; height: 100%; transition: transform 1s ease-in-out;">
            <?php for ($i = 1; $i <= 7; $i++): ?>
                <div style="flex: 0 0 100%; height: 100%; background-image: url('<?= yii\helpers\Url::to('@web/images/isurf_' . $i . '.jpg') ?>'); background-size: cover; background-position: center;"></div>
            <?php endfor; ?>
        </div>
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(13, 148, 136, 0.6) 0%, rgba(15, 23, 42, 0.7) 100%);"></div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentSlide = 0;
            const totalSlides = 7;
            const track = document.getElementById('slide-track');
            
            setInterval(() => {
                currentSlide = (currentSlide + 1) % totalSlides;
                track.style.transform = `translateX(-${currentSlide * 100}%)`;
            }, 3000); // Wait 3 seconds between slides

            const btnScroll = document.getElementById('btn-selengkapnya');
            if (btnScroll) {
                btnScroll.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById('dashboard-content').scrollIntoView({ behavior: 'smooth' });
                });
            }
        });
    </script>
    <div style="position: relative; z-index: 1; text-align: center; max-width: 800px; padding: var(--space-6);">
        <h1 class="text-display" style="color: #ffffff; margin-bottom: var(--space-4); font-weight: 800;">
            Selamat Datang di <span style="color: #EF4444; -webkit-text-stroke: 1.5px #ffffff;">i</span><span style="color: #22C55E; -webkit-text-stroke: 1.5px #ffffff;">Surf</span> Lab
        </h1>
        <p class="text-body" style="color: #ffffff !important; font-weight: 600; font-size: 1.25rem; margin-bottom: var(--space-6); line-height: 1.6; ">
            Sistem Pemantauan dan Kontrol Cerdas untuk Pertanian Perkotaan. Pantau kondisi lingkungan secara real-time dan tingkatkan efisiensi hasil panen.
        </p>
        <a id="btn-selengkapnya" href="#dashboard-content" style="display: inline-block; background-color: #06B6D4; color: #ffffff; font-weight: 700; padding: 12px 32px; border-radius: 8px; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 14px rgba(6, 182, 212, 0.4);">
            Selengkapnya
        </a>
    </div>
</div>
<?php endif; ?>

<div id="dashboard-content" style="display: flex; flex-direction: column; gap: var(--space-6);">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div style="display: flex; flex-direction: column; gap: var(--space-2);">
            <h1 class="text-h2" style="font-weight: 700; color: var(--gray-900);">iSurf Smart Greenhouse Dashboard</h1>
            <p class="text-body" style="color: var(--gray-500);">Sistem Pemantauan Real-time dan Kontrol Aktuator</p>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="ds-grid ds-grid-cols-1 md-grid-cols-2 lg-grid-cols-4">
        
        <!-- Temperature Card -->
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div style="padding: 8px; background-color: #FEF2F2; border-radius: 50%; color: #EF4444;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <span class="ds-badge ds-badge-info" style="border-radius: 12px;" id="status-temp">--</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-bottom: 4px;">
                <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="metric-temp">--</span>
                <span class="text-body font-bold" style="color: var(--gray-900);">°C</span>
            </div>
            <p class="text-body" style="color: var(--gray-500); margin-bottom: 8px;">Suhu Udara</p>
        </div>

        <!-- Humidity Card -->
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div style="padding: 8px; background-color: #ECFEFF; border-radius: 50%; color: #06B6D4;">
                    <!-- Humidity icon -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                </div>
                <span class="ds-badge ds-badge-info" style="border-radius: 12px;" id="status-humidity">--</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-bottom: 4px;">
                <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="metric-humidity">--</span>
                <span class="text-body font-bold" style="color: var(--gray-900);">%</span>
            </div>
            <p class="text-body" style="color: var(--gray-500); margin-bottom: 8px;">Kelembaban Udara</p>
        </div>

        <!-- Water Usage Card -->
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div style="padding: 8px; background-color: #EFF6FF; border-radius: 50%; color: #3B82F6;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <span class="ds-badge ds-badge-success" style="border-radius: 12px;" id="status-water">--</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-bottom: 4px;">
                <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="metric-water">--</span>
                <span class="text-body font-bold" style="color: var(--gray-900);">L</span>
            </div>
            <p class="text-body" style="color: var(--gray-500); margin-bottom: 8px;">Volume Air Saat Ini</p>
        </div>

        <?php if (!Yii::$app->user->isGuest): ?>
        <!-- Data Request Pending Card -->
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div style="padding: 8px; background-color: #FEF9C3; border-radius: 50%; color: #EAB308;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <span class="ds-badge ds-badge-warning" style="border-radius: 12px;" id="status-requests">pending</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-bottom: 4px;">
                <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="metric-requests">0</span>
                <span class="text-body font-bold" style="color: var(--gray-900);">Req</span>
            </div>
            <p class="text-body" style="color: var(--gray-500); margin-bottom: 8px;">Request Data Baru</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Charts Row (2 Columns) -->
    <div class="ds-grid ds-grid-cols-1 lg-grid-cols-2">
        
        <!-- Temperature Trend Chart -->
        <div class="glass-card" style="display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div>
                    <h3 class="text-h3" style="font-weight: 600; color: var(--gray-900);">Grafik Suhu Rata-rata</h3>
                    <p class="text-caption text-gray-500">Pergerakan suhu udara</p>
                </div>
                <select id="time-filter-temp" class="ds-input" style="width: auto; padding: 4px 8px; font-size: 13px;">
                    <option value="24h">24 Jam Terakhir</option>
                    <option value="7d">7 Hari Terakhir</option>
                    <option value="30d">30 Hari Terakhir</option>
                </select>
            </div>
            <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="tempChart"></canvas>
            </div>
        </div>

        <!-- Water Usage Chart -->
        <div class="glass-card" style="display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div>
                    <h3 class="text-h3" style="font-weight: 600; color: var(--gray-900);">Grafik Penggunaan Air</h3>
                    <p class="text-caption text-gray-500">Akumulasi penggunaan air</p>
                </div>
                <select id="time-filter-water" class="ds-input" style="width: auto; padding: 4px 8px; font-size: 13px;">
                    <option value="24h">24 Jam Terakhir</option>
                    <option value="7d">7 Hari Terakhir</option>
                    <option value="30d">30 Hari Terakhir</option>
                </select>
            </div>
            <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="waterChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Actuator Control (Admin Only) -->
<?php if (!Yii::$app->user->isGuest): ?>
<div class="glass-card" style="margin-top: var(--space-8);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
        <div>
            <h3 class="text-h3" style="font-weight: 700;">Kendali Aktuator</h3>
            <p class="text-caption" style="color: var(--gray-500);">Pompa Penyiraman Utama (ID: PMP-01)</p>
        </div>
        <div id="actuator-status-badge" class="ds-badge ds-badge-info">Status: --</div>
    </div>
    <div style="display: flex; gap: var(--space-3); align-items: center; padding: var(--space-4); background-color: var(--gray-50); border-radius: var(--radius-sm); border: 1px solid var(--gray-200);">
        <span class="text-body font-medium" style="color: var(--gray-700);">Kendali Manual:</span>
        <button onclick="ISURF_API.forceActuator('PMP-01', 'ON').then(()=>updateActuatorUI())" class="ds-btn-primary" style="background-color: var(--primary-600);">Paksa Nyala</button>
        <button onclick="ISURF_API.forceActuator('PMP-01', 'OFF').then(()=>updateActuatorUI())" class="ds-btn-primary" style="background-color: var(--gray-600);">Paksa Mati</button>
    </div>
</div>
<?php endif; ?>
