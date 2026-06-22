<?php
/** @var yii\web\View $this */

$this->title = 'Dashboard';

// Dashboard specific scripts
$this->registerJsFile('@web/js/isurf-api.js?v=1.3', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://unpkg.com/mqtt/dist/mqtt.min.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('@web/js/dashboard.js?v=' . time(), ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<script>
    window.appBaseUrl = '<?= Yii::$app->request->baseUrl ?>';
    window.isGuestUser = <?= Yii::$app->user->isGuest ? 'true' : 'false' ?>;
    window.apiUrls = {
        latestReadings: '<?= \yii\helpers\Url::to(['site/latest-readings']) ?>',
        getHistory: '<?= \yii\helpers\Url::to(['site/get-history']) ?>',
        getLogs: '<?= \yii\helpers\Url::to(['site/get-logs']) ?>'
    };
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

        <!-- TDS Card -->
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div style="padding: 8px; background-color: #EFF6FF; border-radius: 50%; color: #3B82F6;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <span class="ds-badge ds-badge-success" style="border-radius: 12px;" id="status-tds">--</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-bottom: 4px;">
                <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="metric-tds">--</span>
                <span class="text-body font-bold" style="color: var(--gray-900);">ppm</span>
            </div>
            <p class="text-body" style="color: var(--gray-500); margin-bottom: 8px;">TDS Air</p>
        </div>

        <!-- pH Card -->
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div style="padding: 8px; background-color: #FEF9C3; border-radius: 50%; color: #EAB308;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <span class="ds-badge ds-badge-success" style="border-radius: 12px;" id="status-ph">--</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-bottom: 4px;">
                <span class="text-h2" style="font-weight: 700; color: var(--gray-900);" id="metric-ph">--</span>
                <span class="text-body font-bold" style="color: var(--gray-900);"></span>
            </div>
            <p class="text-body" style="color: var(--gray-500); margin-bottom: 8px;">pH Air</p>
        </div>
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

        <!-- pH/TDS Chart -->
        <div class="glass-card" style="display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                <div>
                    <h3 class="text-h3" style="font-weight: 600; color: var(--gray-900);">Grafik Kualitas Air</h3>
                    <p class="text-caption text-gray-500">Pergerakan sensor TDS dan pH</p>
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
<div class="glass-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
        <div>
            <h3 class="text-h3" style="font-weight: 700;">Kendali Aktuator</h3>
            <p class="text-caption" style="color: var(--gray-500);">Pompa Penyiraman Utama (ID: PMP-01)</p>
        </div>
        <div style="display: flex; gap: var(--space-2); align-items: center;">
            <select id="connMode" class="ds-input" style="padding: 4px 8px; font-size: 13px; font-weight: 600;" onchange="changeConnMode()">
                <option value="mqtt">Mode: MQTT</option>
                <option value="local">Mode: HTTP (Lokal DB)</option>
            </select>
            <div id="actuator-status-badge" class="ds-badge ds-badge-info">Status: --</div>
        </div>
    </div>
    <div style="display: flex; gap: var(--space-3); align-items: center; padding: var(--space-4); background-color: var(--gray-50); border-radius: var(--radius-sm); border: 1px solid var(--gray-200);">
        <span class="text-body font-medium" style="color: var(--gray-700);">Kendali Manual:</span>
        <button id="btn-on" onclick="handleTurnOn()" class="ds-btn-primary" style="background-color: var(--primary-600);">Paksa Nyala (MQTT)</button>
        <button id="btn-off" onclick="handleTurnOff()" class="ds-btn-primary" style="background-color: var(--gray-600);">Paksa Mati (MQTT)</button>
    </div>
</div>
<?php endif; ?>

<script>
// --- KONFIGURASI MQTT HIVEMQ ---
const mqttBrokerUrl = 'wss://40ce76f98591453e962925f524ea06fa.s1.eu.hivemq.cloud:8884/mqtt';
const mqttOptions = {
    username: 'isurf', // <-- GANTI INI
    password: 'testIsurf123', // <-- GANTI INI
    clientId: 'Yii2WebApp_' + Math.random().toString(16).substr(2, 8)
};

console.log("Mencoba koneksi ke HiveMQ...");
const mqttClient = mqtt.connect(mqttBrokerUrl, mqttOptions);

mqttClient.on('connect', function () {
    console.log('Terhubung ke HiveMQ Broker!');
    document.getElementById('status-temp').innerText = 'Online';
    document.getElementById('status-temp').className = 'ds-badge ds-badge-success';
    
    // Subscribe ke data sensor dari Wokwi
    mqttClient.subscribe('isurf/device/sensor');
});

mqttClient.on('error', function (err) {
    console.error('MQTT Connection Error:', err);
    document.getElementById('status-temp').innerText = 'Error (Cek Username/Pass)';
    document.getElementById('status-temp').className = 'ds-badge ds-badge-error';
});

mqttClient.on('message', function (topic, message) {
    if (topic === 'isurf/device/sensor') {
        try {
            const data = JSON.parse(message.toString());
            if(data.temperature) document.getElementById('metric-temp').innerText = data.temperature.toFixed(1);
            if(data.humidity) document.getElementById('metric-humidity').innerText = data.humidity.toFixed(1);
            if(data.tds) document.getElementById('metric-tds').innerText = Math.round(data.tds);
            if(data.ph) document.getElementById('metric-ph').innerText = data.ph.toFixed(1);
        } catch(e) {
            console.error("Gagal parse pesan MQTT", e);
        }
    }
});

function changeConnMode() {
    const mode = document.getElementById('connMode').value;
    const btnOn = document.getElementById('btn-on');
    const btnOff = document.getElementById('btn-off');
    if(mode === 'mqtt') {
        btnOn.innerText = 'Paksa Nyala (MQTT)';
        btnOff.innerText = 'Paksa Mati (MQTT)';
        document.getElementById('status-temp').innerText = 'Online (MQTT)';
        document.getElementById('status-temp').className = 'ds-badge ds-badge-success';
    } else {
        btnOn.innerText = 'Paksa Nyala (Lokal)';
        btnOff.innerText = 'Paksa Mati (Lokal)';
        document.getElementById('status-temp').innerText = 'Online (Lokal)';
        document.getElementById('status-temp').className = 'ds-badge ds-badge-info';
    }
}

function handleTurnOn() {
    const mode = document.getElementById('connMode').value;
    if(mode === 'mqtt') {
        mqttClient.publish('isurf/device/control', 'ON');
        alert('Perintah ON dikirim ke Wokwi via MQTT!');
    } else {
        if(typeof ISURF_API !== 'undefined') {
            ISURF_API.forceActuator('PMP-01', 'ON').then(()=> { if(typeof updateActuatorUI === 'function') updateActuatorUI(); });
            alert('Perintah ON dikirim via HTTP Lokal');
        }
    }
}

function handleTurnOff() {
    const mode = document.getElementById('connMode').value;
    if(mode === 'mqtt') {
        mqttClient.publish('isurf/device/control', 'OFF');
        alert('Perintah OFF dikirim ke Wokwi via MQTT!');
    } else {
        if(typeof ISURF_API !== 'undefined') {
            ISURF_API.forceActuator('PMP-01', 'OFF').then(()=> { if(typeof updateActuatorUI === 'function') updateActuatorUI(); });
            alert('Perintah OFF dikirim via HTTP Lokal');
        }
    }
}
</script>
