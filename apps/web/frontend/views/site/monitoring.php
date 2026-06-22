<?php
/** @var yii\web\View $this */
$this->title = 'Data Realtime';

$this->registerJsFile('@web/js/isurf-api.js?v=' . time(), ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<script>
    window.appBaseUrl = '<?= yii\helpers\Url::to('@web') ?>';
    const jwtToken = localStorage.getItem('jwt_token') || '';
</script>

<style>
    .manage-requests-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
    .manage-requests-table th { padding: 16px; background: var(--gray-50); border-bottom: 1px solid var(--gray-200); color: var(--gray-500); font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.05em; }
    .manage-requests-table td { padding: 16px; border-bottom: 1px solid var(--gray-100); }
</style>

<div class="monitoring-page" style="display: flex; flex-direction: column; gap: var(--space-6);">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 class="text-h2" style="font-weight: 700; color: var(--gray-900);">Data Realtime</h1>
            <p class="text-body" style="color: var(--gray-500);">Log lengkap pembacaan sensor dan status aktuator.</p>
        </div>
        <?php if (Yii::$app->user->isGuest): ?>
        <a href="<?= yii\helpers\Url::to(['site/request-data']) ?>" class="ds-btn-outline" style="text-decoration: none;">Permohonan Unduh Data</a>
        <?php endif; ?>
    </div>

    <!-- Tabs Container -->
    <div style="display: flex; gap: var(--space-2); border-bottom: 2px solid var(--gray-200); padding-bottom: 8px; flex-wrap: wrap;">
        <button id="tab-logs-sensor-btn" onclick="switchTab('logs-sensor')" style="padding: 8px 16px; border: none; background: var(--primary-100); color: var(--primary-700); border-radius: 8px; font-weight: 600; cursor: pointer;">
            Log Sensor
        </button>
        <button id="tab-logs-actuator-btn" onclick="switchTab('logs-actuator')" style="padding: 8px 16px; border: none; background: transparent; color: var(--gray-600); border-radius: 8px; font-weight: 600; cursor: pointer;">
            Log Aktuator
        </button>
        <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'admin'): ?>
        <button id="tab-requests-btn" onclick="switchTab('requests')" style="padding: 8px 16px; border: none; background: transparent; color: var(--gray-600); border-radius: 8px; font-weight: 600; cursor: pointer;">
            Manage Request Data
        </button>
        <?php endif; ?>
    </div>

    <!-- Tab 1: Logs Sensor -->
    <div id="tab-logs-sensor">
        <div style="background: white; padding: var(--space-5); border-radius: var(--radius-md); box-shadow: var(--elevation-1);">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-4); flex-wrap: wrap; gap: var(--space-4);">
                <div style="display: flex; gap: var(--space-2); align-items: flex-end; flex-wrap: wrap;">
                    <div>
                        <label class="text-caption font-medium" style="display: block; margin-bottom: 4px; color: var(--gray-600);">Dari Tanggal/Waktu</label>
                        <input type="datetime-local" id="log-sensor-start" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm); font-size: 13px;">
                    </div>
                    <div>
                        <label class="text-caption font-medium" style="display: block; margin-bottom: 4px; color: var(--gray-600);">Sampai Tanggal/Waktu</label>
                        <input type="datetime-local" id="log-sensor-end" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm); font-size: 13px;">
                    </div>
                    <div>
                        <label class="text-caption font-medium" style="display: block; margin-bottom: 4px; color: var(--gray-600);">Pilih Area</label>
                        <select id="log-sensor-zone-filter" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm); font-size: 13px;">
                            <option value="all">Semua Area</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-caption font-medium" style="display: block; margin-bottom: 4px; color: var(--gray-600);">Pilih Parameter</label>
                        <select id="log-sensor-filter" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm); font-size: 13px;">
                            <option value="all">Semua Parameter</option>
                            <option value="Suhu Udara">Suhu Udara</option>
                            <option value="Kelembaban Udara">Kelembaban Udara</option>
                            <option value="pH">pH</option>
                            <option value="Kelembaban Tanah">Kelembaban Tanah</option>
                        </select>
                    </div>
                </div>
                <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'admin'): ?>
                <button class="ds-btn-primary" style="font-size: 13px; padding: 6px 16px;" onclick="customDownload()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export CSV Custom
                </button>
                <?php endif; ?>
            </div>

            <div style="overflow-x: auto;">
                <table class="manage-requests-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Tanaman</th>
                            <th>Tipe</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Avrg Reading</th>
                            <th>Anomalies</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="logsSensorTableBody">
                        <tr><td colspan="10" style="text-align:center; padding: 20px; color: var(--gray-500);">Loading logs...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 1b: Logs Aktuator -->
    <div id="tab-logs-actuator" style="display: none;">
        <div style="background: white; padding: var(--space-5); border-radius: var(--radius-md); box-shadow: var(--elevation-1);">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-4); flex-wrap: wrap; gap: var(--space-4);">
                <div style="display: flex; gap: var(--space-2); align-items: flex-end; flex-wrap: wrap;">
                    <div>
                        <label class="text-caption font-medium" style="display: block; margin-bottom: 4px; color: var(--gray-600);">Dari Tanggal/Waktu</label>
                        <input type="datetime-local" id="log-actuator-start" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm); font-size: 13px;">
                    </div>
                    <div>
                        <label class="text-caption font-medium" style="display: block; margin-bottom: 4px; color: var(--gray-600);">Sampai Tanggal/Waktu</label>
                        <input type="datetime-local" id="log-actuator-end" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm); font-size: 13px;">
                    </div>
                    <div>
                        <label class="text-caption font-medium" style="display: block; margin-bottom: 4px; color: var(--gray-600);">Pilih Area</label>
                        <select id="log-actuator-zone-filter" style="background: var(--gray-50); border: 1px solid var(--gray-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm); font-size: 13px;">
                            <option value="all">Semua Area</option>
                        </select>
                    </div>
                </div>
                <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'admin'): ?>
                <button class="ds-btn-primary" style="font-size: 13px; padding: 6px 16px;" onclick="customDownload()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export CSV Custom
                </button>
                <?php endif; ?>
            </div>

            <div style="overflow-x: auto;">
                <table class="manage-requests-table">
                    <thead>
                        <tr>
                            <th>Waktu / Timestamp</th>
                            <th>Nama Pompa</th>
                            <th>Air Dikeluarkan</th>
                            <th>Sisa Air Tangki</th>
                        </tr>
                    </thead>
                    <tbody id="logsActuatorTableBody">
                        <tr><td colspan="4" style="text-align:center; padding: 20px; color: var(--gray-500);">Loading logs...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 2: Manage Requests -->
    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'admin'): ?>
    <div id="tab-requests" style="display: none;">
        <div style="background: white; border-radius: var(--radius-lg); box-shadow: var(--elevation-1); border: 1px solid var(--gray-200); overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="manage-requests-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pemohon</th>
                            <th>Tipe Data</th>
                            <th>Alasan</th>
                            <th>Dokumen</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="requestsTableBody">
                        <tr><td colspan="7" style="padding: 48px; text-align: center; color: var(--gray-500);">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Review Request -->
    <div id="reviewModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 24px; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <h3 class="text-h3" style="margin-bottom: 16px;">Review Pengajuan</h3>
            <input type="hidden" id="reviewRequestId">
            
            <div style="margin-bottom: 16px;">
                <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Status Keputusan</label>
                <select id="reviewStatus" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                    <option value="REVIEW">Tandai Sedang Direview</option>
                    <option value="APPROVED">Setujui (Approved)</option>
                    <option value="REJECTED">Tolak (Rejected)</option>
                </select>
            </div>
            
            <div style="margin-bottom: 24px;">
                <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Catatan Admin (Opsional)</label>
                <textarea id="reviewNotes" rows="3" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" placeholder="Tulis catatan..."></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button onclick="closeReviewModal()" class="ds-btn" style="background: white; border: 1px solid var(--gray-300);">Batal</button>
                <button onclick="submitReview()" class="ds-btn-primary" id="saveReviewBtn">Simpan Keputusan</button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function switchTab(tabName) {
    const isGuest = <?= Yii::$app->user->isGuest ? 'true' : 'false' ?>;
    if (tabName === 'requests' && isGuest) return;

    document.getElementById('tab-logs-sensor').style.display = tabName === 'logs-sensor' ? 'block' : 'none';
    document.getElementById('tab-logs-actuator').style.display = tabName === 'logs-actuator' ? 'block' : 'none';
    const tabRequests = document.getElementById('tab-requests');
    if (tabRequests) tabRequests.style.display = tabName === 'requests' ? 'block' : 'none';

    document.getElementById('tab-logs-sensor-btn').style.background = tabName === 'logs-sensor' ? 'var(--primary-100)' : 'transparent';
    document.getElementById('tab-logs-sensor-btn').style.color = tabName === 'logs-sensor' ? 'var(--primary-700)' : 'var(--gray-600)';

    document.getElementById('tab-logs-actuator-btn').style.background = tabName === 'logs-actuator' ? 'var(--primary-100)' : 'transparent';
    document.getElementById('tab-logs-actuator-btn').style.color = tabName === 'logs-actuator' ? 'var(--primary-700)' : 'var(--gray-600)';
    
    const reqBtn = document.getElementById('tab-requests-btn');
    if (reqBtn) {
        reqBtn.style.background = tabName === 'requests' ? 'var(--primary-100)' : 'transparent';
        reqBtn.style.color = tabName === 'requests' ? 'var(--primary-700)' : 'var(--gray-600)';
    }
}

let requestsData = [];
let logsData = []; // Mock logs or fetch from history
let allAreas = []; // Store areas globally

async function loadInitialData() {
    // Load Areas for Filter
    allAreas = await ISURF_API.getAreas();
    const areas = allAreas;
    const zoneFilterSens = document.getElementById('log-sensor-zone-filter');
    const zoneFilterAct = document.getElementById('log-actuator-zone-filter');
    if (areas && zoneFilterSens && zoneFilterAct) {
        areas.forEach(a => {
            const optSens = document.createElement('option');
            optSens.value = a.id;
            optSens.textContent = a.name;
            zoneFilterSens.appendChild(optSens);
            
            const optAct = document.createElement('option');
            optAct.value = a.id;
            optAct.textContent = a.name;
            zoneFilterAct.appendChild(optAct);
        });
    }

    // Load Logs
    loadLogs();

    // Load Requests (Admin)
    const isGuest = <?= Yii::$app->user->isGuest ? 'true' : 'false' ?>;
    if (!isGuest) {
        loadRequests();
    }
}

async function loadLogs() {
    const sBody = document.getElementById('logsSensorTableBody');
    const aBody = document.getElementById('logsActuatorTableBody');
    
    try {
        const res = await fetch('http://localhost:8000/api/readings/latest');
        if (res.ok) {
            const data = await res.json();
            if (data.length === 0) {
                sBody.innerHTML = '<tr><td colspan="10" style="text-align:center; padding: 20px; color: var(--gray-500);">Belum ada data sensor.</td></tr>';
            } else {
                let html = '';
                data.forEach(log => {
                    let isAnomaly = false;
                    let statusTxt = 'Normal';
                    if (log.min_threshold !== null && log.avg_value < log.min_threshold) {
                        isAnomaly = true; statusTxt = 'Kritis (Low)';
                    }
                    if (log.max_threshold !== null && log.avg_value > log.max_threshold) {
                        isAnomaly = true; statusTxt = 'Kritis (High)';
                    }
                    
                    let anomalyBadge = isAnomaly ? '<span class="ds-badge ds-badge-danger" style="background:#FEE2E2;color:#991B1B;padding:2px 8px;border-radius:4px;">Ya</span>' : '<span class="ds-badge ds-badge-success" style="background:#DCFCE7;color:#166534;padding:2px 8px;border-radius:4px;">Tidak</span>';
                    let statusBadge = isAnomaly ? `<span style="color:#DC2626;font-weight:600;">${statusTxt}</span>` : `<span style="color:#16A34A;font-weight:600;">${statusTxt}</span>`;
                    
                    let areaObj = allAreas.find(a => a.id === log.area_id);
                    let areaName = areaObj ? areaObj.name : '-';
                    let areaPlant = areaObj ? areaObj.plant : '-';
                    let minT = log.min_threshold !== null ? log.min_threshold : '-';
                    let maxT = log.max_threshold !== null ? log.max_threshold : '-';

                    html += `<tr>
                        <td>${log.date}</td>
                        <td>${log.time.split('.')[0]}</td>
                        <td>${areaName}</td>
                        <td>${areaPlant}</td>
                        <td>${log.data_type}</td>
                        <td>${minT}</td>
                        <td>${maxT}</td>
                        <td style="font-weight:600;">${parseFloat(log.avg_value).toFixed(2)}</td>
                        <td>${anomalyBadge}</td>
                        <td>${statusBadge}</td>
                    </tr>`;
                });
                sBody.innerHTML = html;
            }
        } else {
            sBody.innerHTML = '<tr><td colspan="10" style="text-align:center; padding: 20px; color: var(--gray-500);">Gagal memuat data dari API (Error response).</td></tr>';
        }
    } catch(err) {
        sBody.innerHTML = '<tr><td colspan="10" style="text-align:center; padding: 20px; color: var(--gray-500);">Gagal memuat data dari API. Pastikan FastAPI berjalan.</td></tr>';
    }

    aBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 20px; color: var(--gray-500);">Log aktuator belum tersedia (Endpoint belum diintegrasikan).</td></tr>';
}

function customDownload() {
    window.open('http://localhost:8000/api/data-requests/custom-download?date_start=2020-01-01&date_end=2030-01-01', '_blank');
}

// Manage Requests Logic
async function loadRequests() {
    try {
        const reqs = await ISURF_API.getDataRequests();
        requestsData = reqs;
        renderRequestsTable(reqs);
    } catch(err) {
        console.error(err);
    }
}

function renderRequestsTable(data) {
    const tbody = document.getElementById('requestsTableBody');
    if(!tbody) return;

    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="padding: 32px; text-align: center; color: var(--gray-500);">Belum ada pengajuan.</td></tr>';
        return;
    }

    let html = '';
    data.forEach(req => {
        let statusBadge = '';
        if(req.status === 'PENDING') statusBadge = '<span class="ds-badge ds-badge-warning">Pending</span>';
        else if(req.status === 'REVIEW') statusBadge = '<span class="ds-badge ds-badge-info">In Review</span>';
        else if(req.status === 'APPROVED') statusBadge = '<span class="ds-badge ds-badge-success">Approved</span>';
        else statusBadge = '<span class="ds-badge ds-badge-danger">Rejected</span>';

        let docLink = req.document_path ? `<a href="http://localhost:8080${req.document_path}" target="_blank" style="color: var(--blue-500);">Lihat PDF</a>` : '-';

        html += `
        <tr>
            <td>${req.created_at.split('T')[0]}</td>
            <td>
                <p class="font-medium" style="margin: 0;">${req.full_name}</p>
                <p class="text-caption text-gray-500" style="margin: 0;">${req.nim_nip}</p>
            </td>
            <td>${req.data_type}</td>
            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;" title="${req.reason}">${req.reason}</td>
            <td>${docLink}</td>
            <td>${statusBadge}</td>
            <td style="text-align: right;">
                ${(req.status === 'PENDING' || req.status === 'REVIEW') ? `<button onclick="openReviewModal(${req.id})" class="ds-btn-primary" style="padding: 6px 12px; font-size: 13px;">Review</button>` : `<span class="text-caption text-gray-400">Selesai</span>`}
            </td>
        </tr>
        `;
    });
    tbody.innerHTML = html;
}

function openReviewModal(id) {
    const req = requestsData.find(r => r.id === id);
    if(!req) return;
    document.getElementById('reviewRequestId').value = id;
    document.getElementById('reviewModal').style.display = 'flex';
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}

async function submitReview() {
    const id = document.getElementById('reviewRequestId').value;
    const status = document.getElementById('reviewStatus').value;
    const notes = document.getElementById('reviewNotes').value;
    
    const btn = document.getElementById('saveReviewBtn');
    btn.disabled = true;

    try {
        const res = await fetch(`http://localhost:8000/api/data-requests/${id}/review`, {
            method: 'PUT',
            headers: { 
                'Authorization': 'Bearer ' + jwtToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: status, admin_notes: notes })
        });

        if(res.ok) {
            closeReviewModal();
            loadRequests();
        } else {
            alert('Gagal menyimpan review.');
        }
    } catch(err) {
        alert('Kesalahan jaringan.');
    } finally {
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', loadInitialData);
</script>
