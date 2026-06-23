<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Request Data Download';
?>
<div class="request-data-page">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: var(--space-5);">
            <h2 class="text-h3" style="margin-bottom: 8px;">Request Data Download</h2>
            <p class="text-body text-gray-500">Silakan isi formulir pengajuan untuk mendownload data sensor. Admin akan meninjau pengajuan Anda maksimal 2x24 jam.</p>
        </div>

        <div style="background: white; padding: var(--space-5); border-radius: var(--radius-lg); box-shadow: var(--elevation-2);">
            <form id="requestDataForm" enctype="multipart/form-data">
                
                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label class="text-caption font-medium text-gray-900" style="display: block; margin-bottom: 8px;">Nama Lengkap</label>
                    <input type="text" name="full_name" required style="width: 100%; padding: 10px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm);" placeholder="Masukkan nama lengkap Anda">
                </div>

                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label class="text-caption font-medium text-gray-900" style="display: block; margin-bottom: 8px;">Email</label>
                    <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm);" placeholder="Masukkan alamat email Anda">
                </div>

                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label class="text-caption font-medium text-gray-900" style="display: block; margin-bottom: 8px;">NIM / NIP</label>
                    <input type="text" name="nim_nip" required style="width: 100%; padding: 10px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm);" placeholder="Masukkan NIM atau NIP Anda">
                </div>

                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label class="text-caption font-medium text-gray-900" style="display: block; margin-bottom: 8px;">Area Tanam</label>
                    <select name="area" required style="width: 100%; padding: 10px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm); background: white;" id="requestAreaSelect">
                        <option value="all">Semua Area</option>
                        <!-- Area options will be loaded dynamically or hardcoded for now -->
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label class="text-caption font-medium text-gray-900" style="display: block; margin-bottom: 8px;">Kategori Log</label>
                    <select name="log_category" id="logCategorySelect" onchange="updateParamOptions()" required style="width: 100%; padding: 10px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm); background: white;">
                        <option value="sensor">Log Sensor</option>
                        <option value="actuator">Log Aktuator</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label class="text-caption font-medium text-gray-900" style="display: block; margin-bottom: 8px;">Parameter / Aktuator (Bisa lebih dari satu)</label>
                    <div style="display: flex; flex-direction: column; gap: 8px; border: 1px solid var(--gray-200); padding: 12px; border-radius: var(--radius-sm); background: var(--gray-50);">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="param_all" id="param_all" value="all" checked onchange="toggleSensors(this)"> Semua Parameter
                        </label>
                        <div style="height: 1px; background: var(--gray-200); margin: 4px 0;"></div>
                        
                        <div id="sensor-options" class="sensor-checkboxes" style="display: flex; flex-direction: column; gap: 8px; margin-left: 8px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="parameters[]" value="Suhu Udara" class="sensor-cb" checked disabled> Suhu Udara
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="parameters[]" value="Kelembaban Udara" class="sensor-cb" checked disabled> Kelembaban Udara
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="parameters[]" value="Kelembaban Tanah" class="sensor-cb" checked disabled> Kelembaban Tanah
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="parameters[]" value="pH Tanah" class="sensor-cb" checked disabled> pH Tanah
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="parameters[]" value="pH Air" class="sensor-cb" checked disabled> pH Air
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="parameters[]" value="Volume Air" class="sensor-cb" checked disabled> Volume Air
                            </label>
                        </div>

                        <div id="actuator-options" class="sensor-checkboxes" style="display: none; flex-direction: column; gap: 8px; margin-left: 8px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="parameters[]" value="Pompa Air" class="actuator-cb" checked disabled> Pompa Air
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label class="text-caption font-medium text-gray-900" style="display: block; margin-bottom: 8px;">Kolom Atribut Data (Kolom apa saja yang mau di-download)</label>
                    <div style="display: flex; flex-direction: column; gap: 8px; border: 1px solid var(--gray-200); padding: 12px; border-radius: var(--radius-sm); background: var(--gray-50);">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="attr_all" id="attr_all" value="all" checked onchange="toggleAttributes(this)"> Semua Atribut Kolom
                        </label>
                        <div style="height: 1px; background: var(--gray-200); margin: 4px 0;"></div>
                        
                        <div id="sensor-attr-options" style="display: flex; flex-wrap: wrap; gap: 12px; margin-left: 8px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" name="attributes[]" value="date" class="sensor-attr-cb" checked disabled> Date (Tanggal)</label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" name="attributes[]" value="time" class="sensor-attr-cb" checked disabled> Time (Waktu)</label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" name="attributes[]" value="sensor_name" class="sensor-attr-cb" checked disabled> Sensor Name (Nama Sensor)</label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" name="attributes[]" value="reading" class="sensor-attr-cb" checked disabled> Reading (Nilai Bacaan)</label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" name="attributes[]" value="anomalies" class="sensor-attr-cb" checked disabled> Anomalies (Status Anomali)</label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" name="attributes[]" value="status" class="sensor-attr-cb" checked disabled> Status (Normal/Kritis)</label>
                        </div>

                        <div id="actuator-attr-options" style="display: none; flex-wrap: wrap; gap: 12px; margin-left: 8px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" name="attributes[]" value="timestamp" class="actuator-attr-cb" checked disabled> Timestamp (Waktu)</label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" name="attributes[]" value="actuator_name" class="actuator-attr-cb" checked disabled> Actuator Name (Nama Pompa)</label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" name="attributes[]" value="water_discharged" class="actuator-attr-cb" checked disabled> Water Discharged (Air Dikeluarkan)</label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" name="attributes[]" value="water_remaining" class="actuator-attr-cb" checked disabled> Water Remaining (Sisa Air Tangki)</label>
                        </div>
                    </div>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: var(--space-4); margin-bottom: var(--space-4);">
                    <div class="form-group" style="flex: 1 1 150px;">
                        <label class="text-caption font-medium text-gray-900" style="display: block; margin-bottom: 8px;">Dari Waktu</label>
                        <input type="datetime-local" name="datetime_start" required style="width: 100%; padding: 10px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm);">
                    </div>
                    <div class="form-group" style="flex: 1 1 150px;">
                        <label class="text-caption font-medium text-gray-900" style="display: block; margin-bottom: 8px;">Sampai Waktu</label>
                        <input type="datetime-local" name="datetime_end" required style="width: 100%; padding: 10px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm);">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label class="text-caption font-medium text-gray-900" style="display: block; margin-bottom: 8px;">Alasan Pengajuan</label>
                    <textarea name="reason" required rows="4" style="width: 100%; padding: 10px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm); resize: vertical;" placeholder="Jelaskan untuk keperluan apa data ini akan digunakan (cth: Tugas Akhir, Penelitian, dll)"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: var(--space-6);">
                    <label class="text-caption font-medium text-gray-900" style="display: block; margin-bottom: 8px;">Surat Pengajuan (PDF Bertanda Tangan)</label>
                    <div id="dropzoneArea" style="border: 2px dashed var(--gray-300); padding: var(--space-5); text-align: center; border-radius: var(--radius-sm); background: var(--gray-50); cursor: pointer; transition: all 0.3s ease;" onclick="document.getElementById('docInput').click()">
                        <svg id="uploadIcon" class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <p class="text-caption text-gray-600" id="fileNameDisplay" style="margin-bottom: 0;">Klik untuk memilih file PDF (Max 5MB)</p>
                        <input type="file" id="docInput" name="document" accept="application/pdf" style="display: none;" onchange="handleFileUpload(this)">
                    </div>
                </div>

                <button type="submit" class="ds-btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 16px;" id="submitBtn">
                    Kirim Pengajuan
                </button>
            </form>

            <!-- Success State -->
            <div id="successState" style="display: none; text-align: center; padding: var(--space-4) 0;">
                <div style="width: 64px; height: 64px; background: #DCFCE7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                    <svg class="w-8 h-8" style="color: var(--primary-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-h3" style="margin-bottom: var(--space-2);">Pengajuan Berhasil Dikirim</h3>
                <p class="text-body text-gray-600 mb-4">Pengajuan Anda telah diterima oleh sistem. Kode Tiket Anda adalah: <strong id="trackingCodeDisplay" style="font-size: 18px; color: var(--primary-600);"></strong></p>
                <p class="text-caption text-gray-500 mb-4">Silakan catat kode ini untuk mengecek status permohonan Anda ke Admin.</p>
                <button onclick="location.reload()" class="ds-btn-primary" style="text-decoration: none;">Kembali ke Form</button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSensors(masterCheckbox) {
    const category = document.getElementById('logCategorySelect').value;
    const targetClass = category === 'sensor' ? '.sensor-cb' : '.actuator-cb';
    const checkboxes = document.querySelectorAll(targetClass);
    checkboxes.forEach(cb => {
        cb.disabled = masterCheckbox.checked;
        if(masterCheckbox.checked) cb.checked = true;
    });
}

function toggleAttributes(masterCheckbox) {
    const category = document.getElementById('logCategorySelect').value;
    const targetClass = category === 'sensor' ? '.sensor-attr-cb' : '.actuator-attr-cb';
    const checkboxes = document.querySelectorAll(targetClass);
    checkboxes.forEach(cb => {
        cb.disabled = masterCheckbox.checked;
        if(masterCheckbox.checked) cb.checked = true;
    });
}

function updateParamOptions() {
    const category = document.getElementById('logCategorySelect').value;
    const sensorOpts = document.getElementById('sensor-options');
    const actuatorOpts = document.getElementById('actuator-options');
    const paramAll = document.getElementById('param_all');
    
    const sensorAttrOpts = document.getElementById('sensor-attr-options');
    const actuatorAttrOpts = document.getElementById('actuator-attr-options');
    const attrAll = document.getElementById('attr_all');
    
    paramAll.checked = true;
    attrAll.checked = true;

    if (category === 'sensor') {
        sensorOpts.style.display = 'flex';
        actuatorOpts.style.display = 'none';
        document.querySelectorAll('.actuator-cb').forEach(cb => { cb.checked = false; cb.disabled = true; });
        document.querySelectorAll('.sensor-cb').forEach(cb => { cb.checked = true; cb.disabled = true; });
        
        sensorAttrOpts.style.display = 'flex';
        actuatorAttrOpts.style.display = 'none';
        document.querySelectorAll('.actuator-attr-cb').forEach(cb => { cb.checked = false; cb.disabled = true; });
        document.querySelectorAll('.sensor-attr-cb').forEach(cb => { cb.checked = true; cb.disabled = true; });
    } else {
        sensorOpts.style.display = 'none';
        actuatorOpts.style.display = 'flex';
        document.querySelectorAll('.sensor-cb').forEach(cb => { cb.checked = false; cb.disabled = true; });
        document.querySelectorAll('.actuator-cb').forEach(cb => { cb.checked = true; cb.disabled = true; });
        
        sensorAttrOpts.style.display = 'none';
        actuatorAttrOpts.style.display = 'flex';
        document.querySelectorAll('.sensor-attr-cb').forEach(cb => { cb.checked = false; cb.disabled = true; });
        document.querySelectorAll('.actuator-attr-cb').forEach(cb => { cb.checked = true; cb.disabled = true; });
    }
}

function handleFileUpload(input) {
    const display = document.getElementById('fileNameDisplay');
    const dropzone = document.getElementById('dropzoneArea');
    const icon = document.getElementById('uploadIcon');
    
    if (input.files && input.files[0]) {
        display.innerHTML = `<strong style="color: var(--green-600); font-size: 14px;">✓ File Terpilih:</strong><br>${input.files[0].name}`;
        dropzone.style.borderColor = 'var(--green-500)';
        dropzone.style.background = '#F0FDF4';
        icon.style.color = 'var(--green-500)';
    } else {
        display.innerHTML = 'Klik untuk memilih file PDF (Max 5MB)';
        dropzone.style.borderColor = 'var(--gray-300)';
        dropzone.style.background = 'var(--gray-50)';
        icon.style.color = 'var(--gray-400)';
    }
}

document.getElementById('requestDataForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // --- VALIDATION START ---
    const nimNip = document.querySelector('input[name="nim_nip"]').value;
    if (!/^\d+$/.test(nimNip) || nimNip.length < 8) {
        alert('NIM / NIP harus berupa angka dan minimal 8 digit.');
        return;
    }

    const dateStart = new Date(document.querySelector('input[name="datetime_start"]').value);
    const dateEnd = new Date(document.querySelector('input[name="datetime_end"]').value);
    if (dateStart >= dateEnd) {
        alert('Sampai Waktu harus lebih besar dari Dari Waktu.');
        return;
    }

    const fileInput = document.getElementById('docInput');
    if (fileInput.files.length === 0) {
        alert('Mohon unggah Surat Pengajuan (PDF Bertanda Tangan) terlebih dahulu.');
        return;
    }
    
    const file = fileInput.files[0];
    if (file.type !== 'application/pdf') {
        alert('File surat pengajuan harus berformat PDF.');
        return;
    }
    if (file.size > 5 * 1024 * 1024) { // 5MB
        alert('Ukuran file PDF maksimal 5MB.');
        return;
    }
    // --- VALIDATION END ---

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Mengirim...';

    const formData = new FormData(this);
    formData.delete('parameters[]'); // clean up native html array
    formData.delete('attributes[]');
    
    let selectedParams = [];
    if (formData.get('param_all') === 'all') {
        selectedParams = ['all'];
    } else {
        const category = document.getElementById('logCategorySelect').value;
        const targetClass = category === 'sensor' ? '.sensor-cb:checked:not(:disabled)' : '.actuator-cb:checked:not(:disabled)';
        const checkboxes = document.querySelectorAll(targetClass);
        checkboxes.forEach(cb => selectedParams.push(cb.value));
    }
    if (selectedParams.length === 0) {
        alert('Anda harus memilih minimal satu Parameter/Aktuator.');
        btn.disabled = false;
        btn.textContent = 'Kirim Pengajuan';
        return;
    }
    formData.set('requested_parameters', JSON.stringify(selectedParams));
    
    let selectedAttrs = [];
    if (formData.get('attr_all') === 'all') {
        selectedAttrs = ['all'];
    } else {
        const category = document.getElementById('logCategorySelect').value;
        const targetClass = category === 'sensor' ? '.sensor-attr-cb:checked:not(:disabled)' : '.actuator-attr-cb:checked:not(:disabled)';
        const checkboxes = document.querySelectorAll(targetClass);
        checkboxes.forEach(cb => selectedAttrs.push(cb.value));
    }
    if (selectedAttrs.length === 0) {
        alert('Anda harus memilih minimal satu Kolom Atribut Data.');
        btn.disabled = false;
        btn.textContent = 'Kirim Pengajuan';
        return;
    }
    
    // Map Frontend Names to Backend API Expected Names
    formData.set('date_start', formData.get('datetime_start').split('T')[0]);
    formData.set('date_end', formData.get('datetime_end').split('T')[0]);
    formData.set('data_type', formData.get('log_category'));
    formData.set('requested_sensors', JSON.stringify(selectedParams));

    // Remove incorrect names
    formData.delete('datetime_start');
    formData.delete('datetime_end');
    formData.delete('log_category');
    formData.delete('param_all');
    formData.delete('attr_all');
    formData.delete('area');
    formData.delete('requested_parameters');
    
    try {
        const response = await fetch('https://isurf.digdaya.net/isurf/v1/api/data-requests/', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            const result = await response.json();
            document.getElementById('requestDataForm').style.display = 'none';
            document.getElementById('successState').style.display = 'block';
            document.getElementById('trackingCodeDisplay').textContent = result.tracking_code;
        } else {
            const err = await response.json();
            let errorMsg = err.detail || 'Unknown error';
            if (Array.isArray(errorMsg)) {
                errorMsg = errorMsg.map(e => `${e.loc[e.loc.length - 1]}: ${e.msg}`).join('\n');
            }
            alert('Gagal mengirim pengajuan:\n' + errorMsg);
            btn.disabled = false;
            btn.textContent = 'Kirim Pengajuan';
        }
    } catch (err) {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.textContent = 'Kirim Pengajuan';
    }
});
</script>
