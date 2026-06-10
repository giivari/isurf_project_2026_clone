<?php
use yii\helpers\Html;

$this->title = 'Device Management';
?>
<div class="devices-page">
    <div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-6);">
        <div>
            <h1 class="text-h2" style="margin-bottom: var(--space-2);">Device Management</h1>
            <p class="text-body text-gray-500">Kelola perangkat IoT, sensor, dan atur batas indikator (threshold) untuk otomatisasi lokal.</p>
        </div>
        <button class="ds-btn-primary" style="white-space: nowrap;" onclick="openAddDeviceModal()">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Device
        </button>
    </div>

    <div style="background: white; border-radius: var(--radius-lg); box-shadow: var(--elevation-1); overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
            <thead>
                <tr style="background: var(--gray-50); border-bottom: 1px solid var(--gray-200); text-align: left;">
                    <th style="padding: var(--space-4); color: var(--gray-500); font-size: 12px; font-weight: 600; text-transform: uppercase; white-space: nowrap;">Device Info</th>
                    <th style="padding: var(--space-4); color: var(--gray-500); font-size: 12px; font-weight: 600; text-transform: uppercase; white-space: nowrap;">Status & Heartbeat</th>
                    <th style="padding: var(--space-4); color: var(--gray-500); font-size: 12px; font-weight: 600; text-transform: uppercase; white-space: nowrap;">Sensors</th>
                    <th style="padding: var(--space-4); color: var(--gray-500); font-size: 12px; font-weight: 600; text-transform: uppercase; text-align: right; white-space: nowrap;">Aksi</th>
                </tr>
            </thead>
            <tbody id="devicesTableBody">
                <tr><td colspan="4" style="text-align: center; padding: var(--space-6); color: var(--gray-500);">Loading devices...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Threshold Configuration -->
<div id="thresholdModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: var(--space-4);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 600px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: var(--elevation-3);">
        <div style="padding: var(--space-5); border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
            <h3 class="text-h3" style="margin: 0;" id="modalTitle">Konfigurasi Indikator Sensor</h3>
            <button onclick="closeModal()" style="background: none; border: none; cursor: pointer; color: var(--gray-400);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div style="padding: var(--space-5); overflow-y: auto;" id="modalBody">
            <!-- Sensor list will be injected here -->
        </div>
        <div style="padding: var(--space-5); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: var(--space-3);">
            <button class="ds-btn-secondary" onclick="closeModal()">Batal</button>
            <button class="ds-btn-primary" onclick="saveThresholds()" id="saveBtn">Simpan Konfigurasi</button>
        </div>
    </div>
</div>

<!-- Modal Add Device -->
<div id="addDeviceModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: var(--space-4);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 500px; display: flex; flex-direction: column; box-shadow: var(--elevation-3);">
        <div style="padding: var(--space-5); border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
            <h3 class="text-h3" style="margin: 0;">Tambah Perangkat Baru</h3>
            <button onclick="closeAddDeviceModal()" style="background: none; border: none; cursor: pointer; color: var(--gray-400);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div style="padding: var(--space-5);">
            <div style="margin-bottom: 16px;">
                <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Nama Perangkat</label>
                <input type="text" id="newDevName" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" placeholder="cth: Nursery Monitor C">
            </div>
            <div style="margin-bottom: 16px;">
                <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Kode Perangkat (Device Code)</label>
                <input type="text" id="newDevCode" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" placeholder="cth: ESP32_NUR_03">
                <small class="text-gray-500">Kode ini harus dimasukkan ke dalam config.h alat fisik.</small>
            </div>
            <div style="margin-bottom: 16px;">
                <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Tipe</label>
                <select id="newDevType" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                    <option value="esp32_monitor">ESP32 Monitor (Hanya Sensor)</option>
                    <option value="esp32_irrigation">ESP32 Controller (Sensor & Relay)</option>
                </select>
            </div>
            <div style="margin-bottom: 16px;">
                <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Lokasi</label>
                <input type="text" id="newDevLocation" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" placeholder="cth: Greenhouse C">
            </div>
        </div>
        <div style="padding: var(--space-5); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: var(--space-3);">
            <button class="ds-btn-secondary" onclick="closeAddDeviceModal()">Batal</button>
            <button class="ds-btn-primary" onclick="submitNewDevice()" id="submitDevBtn">Simpan Perangkat</button>
        </div>
    </div>
</div>

<script src="<?= Yii::getAlias('@web') ?>/js/isurf-api.js"></script>
<script>
let currentDevice = null;

async function loadDevices() {
    const tbody = document.getElementById('devicesTableBody');
    const devices = await iSurfAPI.getDevices();
    
    if (devices.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; padding: var(--space-6); color: var(--gray-500);">Belum ada perangkat terdaftar.</td></tr>`;
        return;
    }
    
    tbody.innerHTML = '';
    devices.forEach(device => {
        const tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid var(--gray-200)';
        
        let statusBadge = device.status === 'online' 
            ? `<span class="ds-badge ds-badge-success">Online</span>` 
            : `<span class="ds-badge ds-badge-danger">Offline</span>`;
            
        let hb = device.last_heartbeat ? iSurfAPI.formatDateTimeWithTZ(device.last_heartbeat) : 'Never';
        
        tr.innerHTML = `
            <td style="padding: var(--space-4);">
                <p style="font-weight: 600; color: var(--gray-900); margin: 0;">${device.name}</p>
                <p style="font-size: 13px; color: var(--gray-500); margin: 0; font-family: monospace;">${device.device_code}</p>
            </td>
            <td style="padding: var(--space-4);">
                <div style="margin-bottom: 4px;">${statusBadge}</div>
                <p style="font-size: 12px; color: var(--gray-500); margin: 0;">Last: ${hb}</p>
            </td>
            <td style="padding: var(--space-4); white-space: nowrap;">
                <span class="ds-badge ds-badge-info">${device.sensors ? device.sensors.length : 0} Sensors</span>
            </td>
            <td style="padding: var(--space-4); text-align: right; white-space: nowrap;">
                <button class="ds-btn-primary" style="padding: 6px 12px; font-size: 13px;" onclick="openModal(${device.id})">
                    Set Thresholds
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function openModal(deviceId) {
    document.getElementById('thresholdModal').style.display = 'flex';
    document.getElementById('modalBody').innerHTML = '<p class="text-center text-gray-500">Memuat sensor...</p>';
    
    // Fetch device details directly to get its sensors
    const device = await iSurfAPI.getDeviceSensors(deviceId);
    if(!device) {
        document.getElementById('modalBody').innerHTML = '<p class="text-center text-red-500">Gagal memuat perangkat.</p>';
        return;
    }
    
    currentDevice = device;
    document.getElementById('modalTitle').textContent = `Indikator: ${device.name}`;
    
    if(!device.sensors || device.sensors.length === 0) {
        document.getElementById('modalBody').innerHTML = '<p class="text-center text-gray-500">Perangkat ini belum memiliki sensor terdaftar.</p>';
        return;
    }
    
    let html = '<div style="display: flex; flex-direction: column; gap: var(--space-4);">';
    device.sensors.forEach(s => {
        let minVal = s.min_threshold !== null ? s.min_threshold : '';
        let maxVal = s.max_threshold !== null ? s.max_threshold : '';
        
        html += `
            <div style="background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: var(--space-4);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-3);">
                    <div>
                        <p style="font-weight: 600; color: var(--gray-900); margin: 0;">${s.name}</p>
                        <p style="font-size: 12px; color: var(--gray-500); margin: 0; text-transform: uppercase;">${s.sensor_type} (${s.unit})</p>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-3);">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 500; color: var(--gray-600); margin-bottom: 4px;">Min Threshold</label>
                        <input type="number" step="0.1" id="min_${s.id}" value="${minVal}" placeholder="Kosongkan jika tdk ada" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: var(--radius-sm);">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 500; color: var(--gray-600); margin-bottom: 4px;">Max Threshold</label>
                        <input type="number" step="0.1" id="max_${s.id}" value="${maxVal}" placeholder="Kosongkan jika tdk ada" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: var(--radius-sm);">
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    document.getElementById('modalBody').innerHTML = html;
}

function closeModal() {
    document.getElementById('thresholdModal').style.display = 'none';
    currentDevice = null;
}

async function saveThresholds() {
    if(!currentDevice) return;
    
    const btn = document.getElementById('saveBtn');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;
    
    let promises = [];
    
    currentDevice.sensors.forEach(s => {
        let minVal = document.getElementById(`min_${s.id}`).value;
        let maxVal = document.getElementById(`max_${s.id}`).value;
        
        promises.push(iSurfAPI.updateSensorThreshold(currentDevice.id, s.id, minVal, maxVal));
    });
    
    try {
        await Promise.all(promises);
        alert('Konfigurasi indikator berhasil disimpan! Arduino akan menarik pembaruan ini dalam siklus berikutnya.');
        closeModal();
        loadDevices(); // reload list
    } catch (err) {
        alert('Gagal menyimpan konfigurasi.');
    } finally {
        btn.textContent = 'Simpan Konfigurasi';
        btn.disabled = false;
    }
}

// Add Device Logic
function openAddDeviceModal() {
    document.getElementById('newDevName').value = '';
    document.getElementById('newDevCode').value = '';
    document.getElementById('newDevLocation').value = '';
    document.getElementById('addDeviceModal').style.display = 'flex';
}

function closeAddDeviceModal() {
    document.getElementById('addDeviceModal').style.display = 'none';
}

async function submitNewDevice() {
    const name = document.getElementById('newDevName').value;
    const code = document.getElementById('newDevCode').value;
    const type = document.getElementById('newDevType').value;
    const loc = document.getElementById('newDevLocation').value;

    if (!name || !code) {
        alert("Nama dan Kode Perangkat wajib diisi!");
        return;
    }

    const btn = document.getElementById('submitDevBtn');
    btn.disabled = true;
    btn.innerHTML = 'Menyimpan...';

    try {
        await iSurfAPI.addDevice({
            name: name,
            device_code: code,
            type: type,
            location: loc
        });
        
        closeAddDeviceModal();
        alert("Perangkat berhasil ditambahkan! Silakan pasang kode alat di config.h Arduino Anda.");
        loadDevices(); // Reload table
    } catch (err) {
        alert("Gagal menambahkan: " + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Simpan Perangkat';
    }
}

document.addEventListener('DOMContentLoaded', loadDevices);
</script>
