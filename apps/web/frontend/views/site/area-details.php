<?php

use yii\helpers\Html;

$this->title = 'Detail & Aturan Area';
?>
<div class="site-area-details">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-h2" style="font-weight: 700; color: var(--gray-900);">Informasi Detail Perangkat & Aturan</h1>
            <p class="text-gray-500 text-sm mt-1">Pilih area untuk melihat daftar sensor, aktuator, jadwal, dan aturan kondisional.</p>
        </div>
        <div>
            <button id="btnDownloadCsv" class="ds-btn ds-btn-primary flex items-center gap-2 shadow-sm" style="display: none;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download Laporan (CSV)
            </button>
        </div>
    </div>



    <div id="loadingIndicator" class="text-center py-10" style="display: none;">
        <svg class="animate-spin h-8 w-8 text-primary-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-3 text-gray-500">Mengambil data perangkat dan aturan...</p>
    </div>

    <div id="detailsContent" style="display: none;">
        
        <!-- Area Summary -->
        <div class="glass-panel p-6 mb-6 border-l-4" style="border-radius: 16px;" style="border-left-color: var(--primary-500);">
            <h2 id="summaryName" class="text-xl font-bold" style="color: var(--gray-900);">Nama Area</h2>
            <p id="summaryPlant" class="text-sm font-medium mt-1 text-primary-600">Tanaman: -</p>
            <p id="summaryDesc" class="text-sm text-gray-600 mt-2">Deskripsi: -</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Sensors -->
            <div class="glass-panel p-6" style="border-radius: 16px;">
                <div class="flex items-center gap-2 mb-4 justify-between">
<div class="flex items-center gap-2">
<div class="p-2 bg-blue-100 text-blue-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg></div><h3 class="text-lg font-bold">Daftar Sensor</h3>
</div>
<button class="ds-btn-outline text-xs py-1 px-2" onclick="openAddSensorModal()">+ Tambah</button>
</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase border-b border-gray-200">
                                <th class="pb-2 font-medium">ID / Nama</th>
                                <th class="pb-2 font-medium">Tipe Data</th>
                                <th class="pb-2 font-medium">Ambang Batas (Min-Max)</th>
<th class="pb-2 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="sensorsTbody" class="text-sm">
                            <!-- Filled by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Actuators -->
            <div class="glass-panel p-6" style="border-radius: 16px;">
                <div class="flex items-center gap-2 mb-4 justify-between">
<div class="flex items-center gap-2">
<div class="p-2 bg-orange-100 text-orange-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></div><h3 class="text-lg font-bold">Daftar Aktuator</h3>
</div>
<button class="ds-btn-outline text-xs py-1 px-2" onclick="openAddActuatorModal()">+ Tambah</button>
</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase border-b border-gray-200">
                                <th class="pb-2 font-medium">ID / Nama</th>
                                <th class="pb-2 font-medium">Status Valve</th>
                                <th class="pb-2 font-medium">Mode Otomatis</th>
<th class="pb-2 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="actuatorsTbody" class="text-sm">
                            <!-- Filled by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Condition Rules -->
            <div class="glass-panel p-6" style="border-radius: 16px;">
                <div class="flex items-center gap-2 mb-4 justify-between">
<div class="flex items-center gap-2">
<div class="p-2 bg-purple-100 text-purple-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg></div><h3 class="text-lg font-bold">Aturan Kondisional</h3>
</div>
<button class="ds-btn-outline text-xs py-1 px-2" onclick="openAreaRulesModal(currentManageAreaId, document.getElementById('summaryName').innerText)">Kelola Aturan</button>
</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase border-b border-gray-200">
                                <th class="pb-2 font-medium">Jika (Kondisi)</th>
                                <th class="pb-2 font-medium">Maka (Aksi)</th>
                            </tr>
                        </thead>
                        <tbody id="conditionsTbody" class="text-sm">
                            <!-- Filled by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Schedule Rules -->
            <div class="glass-panel p-6" style="border-radius: 16px;">
                <div class="flex items-center gap-2 mb-4">
                    <div class="p-2 bg-green-100 text-green-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold">Aturan Jadwal Waktu</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase border-b border-gray-200">
                                <th class="pb-2 font-medium">Waktu (Jam)</th>
                                <th class="pb-2 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="schedulesTbody" class="text-sm">
                            <!-- Filled by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$this->registerJsFile('@web/js/isurf-api.js?v=' . time(), ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<!-- Modal Add Sensor -->
<div id="addSensorModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1010; align-items: center; justify-content: center; padding: var(--space-4);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 400px; display: flex; flex-direction: column;">
        <div style="padding: var(--space-4); border-bottom: 1px solid var(--gray-200);">
            <h3 class="text-h4" style="margin: 0;">Tambah Sensor Baru</h3>
        </div>
        <div style="padding: var(--space-4);">
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 13px; margin-bottom: 4px;">ID Sensor (MAC Address/Unik) *</label>
                <input type="text" id="newSensorId" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 13px; margin-bottom: 4px;">Nama Sensor *</label>
                <input type="text" id="newSensorName" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" placeholder="cth: DHT22 Suhu">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 13px; margin-bottom: 4px;">Tipe Data *</label>
                <select id="newSensorType" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                    <option value="Suhu Udara">Suhu Udara</option>
                    <option value="Kelembaban Udara">Kelembaban Udara</option>
                    <option value="Kelembaban Tanah">Kelembaban Tanah</option>
                    <option value="pH">pH</option>
                    <option value="TDS">TDS (Nutrisi)</option>
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                <div>
                    <label style="display: block; font-size: 13px; margin-bottom: 4px;">Min Threshold</label>
                    <input type="number" step="0.1" id="newSensorMin" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; font-size: 13px; margin-bottom: 4px;">Max Threshold</label>
                    <input type="number" step="0.1" id="newSensorMax" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                </div>
            </div>
        </div>
        <div style="padding: var(--space-4); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 8px;">
            <button class="ds-btn-outline" onclick="document.getElementById('addSensorModal').style.display='none'">Batal</button>
            <button class="ds-btn-primary" onclick="submitNewSensor()">Simpan</button>
        </div>
    </div>
</div>

<!-- Modal Add Actuator -->
<div id="addActuatorModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1010; align-items: center; justify-content: center; padding: var(--space-4);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 400px; display: flex; flex-direction: column;">
        <div style="padding: var(--space-4); border-bottom: 1px solid var(--gray-200);">
            <h3 class="text-h4" style="margin: 0;">Tambah Aktuator Baru</h3>
        </div>
        <div style="padding: var(--space-4);">
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 13px; margin-bottom: 4px;">ID Aktuator (Relay PIN/Unik) *</label>
                <input type="text" id="newActId" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 13px; margin-bottom: 4px;">Nama Aktuator *</label>
                <input type="text" id="newActName" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" placeholder="cth: Pompa Air A">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 13px; margin-bottom: 4px;">Debit Air (Liter / Detik) *</label>
                <input type="number" step="0.01" id="newActFlow" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" value="0.5">
            </div>
        </div>
        <div style="padding: var(--space-4); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 8px;">
            <button class="ds-btn-outline" onclick="document.getElementById('addActuatorModal').style.display='none'">Batal</button>
            <button class="ds-btn-primary" onclick="submitNewActuator()">Simpan</button>
        </div>
    </div>
</div>

<!-- Modal Edit Sensor -->
<div id="editSensorModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1010; align-items: center; justify-content: center; padding: var(--space-4);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 400px; display: flex; flex-direction: column;">
        <div style="padding: var(--space-4); border-bottom: 1px solid var(--gray-200);">
            <h3 class="text-h4" style="margin: 0;">Edit Sensor</h3>
        </div>
        <div style="padding: var(--space-4);">
            <input type="hidden" id="editSensorId">
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 13px; margin-bottom: 4px;">Nama Sensor *</label>
                <input type="text" id="editSensorName" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 13px; margin-bottom: 4px;">Tipe Data *</label>
                <select id="editSensorType" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                    <option value="Suhu Udara">Suhu Udara</option>
                    <option value="Kelembaban Udara">Kelembaban Udara</option>
                    <option value="Kelembaban Tanah">Kelembaban Tanah</option>
                    <option value="pH">pH</option>
                    <option value="TDS">TDS (Nutrisi)</option>
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                <div>
                    <label style="display: block; font-size: 13px; margin-bottom: 4px;">Min Threshold</label>
                    <input type="number" step="0.1" id="editSensorMin" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; font-size: 13px; margin-bottom: 4px;">Max Threshold</label>
                    <input type="number" step="0.1" id="editSensorMax" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                </div>
            </div>
        </div>
        <div style="padding: var(--space-4); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 8px;">
            <button class="ds-btn-outline" onclick="document.getElementById('editSensorModal').style.display='none'">Batal</button>
            <button class="ds-btn-primary" onclick="submitEditSensor()">Simpan</button>
        </div>
    </div>
</div>

<!-- Modal Edit Actuator -->
<div id="editActuatorModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1010; align-items: center; justify-content: center; padding: var(--space-4);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 400px; display: flex; flex-direction: column;">
        <div style="padding: var(--space-4); border-bottom: 1px solid var(--gray-200);">
            <h3 class="text-h4" style="margin: 0;">Edit Aktuator</h3>
        </div>
        <div style="padding: var(--space-4);">
            <input type="hidden" id="editActId">
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 13px; margin-bottom: 4px;">Nama Aktuator *</label>
                <input type="text" id="editActName" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 13px; margin-bottom: 4px;">Debit Air (Liter / Detik) *</label>
                <input type="number" step="0.01" id="editActFlow" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
            </div>
        </div>
        <div style="padding: var(--space-4); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 8px;">
            <button class="ds-btn-outline" onclick="document.getElementById('editActuatorModal').style.display='none'">Batal</button>
            <button class="ds-btn-primary" onclick="submitEditActuator()">Simpan</button>
        </div>
    </div>
</div>

<!-- Modal Area Rules -->
<div id="areaRulesModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1010; align-items: flex-start; justify-content: center; padding: var(--space-6); overflow-y: auto;">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 800px; display: flex; flex-direction: column; box-shadow: var(--elevation-3); margin-top: 40px; margin-bottom: 40px;">
        <div style="padding: var(--space-5); border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; border-radius: var(--radius-lg) var(--radius-lg) 0 0; z-index: 10;">
            <div>
                <h3 class="text-h3" style="margin: 0; color: var(--gray-900);" id="areaRulesTitle">Aturan Area</h3>
                <p class="text-caption" style="color: var(--gray-500); margin: 4px 0 0 0;">Atur jadwal dan kondisi otomasi untuk seluruh aktuator di area ini</p>
            </div>
            <button onclick="document.getElementById('areaRulesModal').style.display='none'" style="background: none; border: none; cursor: pointer; color: var(--gray-400);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div style="padding: var(--space-5);">
            <div style="display: flex; gap: 16px; border-bottom: 1px solid var(--gray-200); margin-bottom: var(--space-5);">
                <button id="tabRuleCond" class="text-body font-medium" style="padding: 8px 16px; border: none; background: none; border-bottom: 2px solid var(--green-600); color: var(--green-700); cursor: pointer;" onclick="switchRuleTab('cond')">Otomasi Kondisi</button>
                <button id="tabRuleSched" class="text-body font-medium" style="padding: 8px 16px; border: none; background: none; border-bottom: 2px solid transparent; color: var(--gray-500); cursor: pointer;" onclick="switchRuleTab('sched')">Jadwal Rutin</button>
                <button id="tabRuleThresh" class="text-body font-medium" style="padding: 8px 16px; border: none; background: none; border-bottom: 2px solid transparent; color: var(--gray-500); cursor: pointer;" onclick="switchRuleTab('thresh')">Threshold Massal Sensor</button>
            </div>

            <!-- Tab Kondisi -->
            <div id="ruleCondContent">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
                    <h4 style="margin: 0; font-size: 16px; font-weight: 600;">Daftar Aturan Kondisi</h4>
                </div>
                <div style="border: 1px solid var(--gray-200); border-radius: var(--radius-md); overflow: hidden; margin-bottom: var(--space-6);">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background: var(--gray-50);">
                            <tr>
                                <th style="padding: var(--space-3); text-align: left; font-size: 12px; color: var(--gray-500);">Kondisi Parameter</th>
                                <th style="padding: var(--space-3); text-align: center; font-size: 12px; color: var(--gray-500);">Aksi Pompa</th>
                                <th style="padding: var(--space-3); text-align: right; font-size: 12px; color: var(--gray-500);">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="areaCondBody">
                            <tr><td colspan="3" style="text-align: center; padding: var(--space-4); color: var(--gray-500);">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div style="background: var(--gray-50); padding: var(--space-4); border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
                    <h5 style="margin: 0 0 12px 0; font-size: 14px;">Tambah Kondisi Baru</h5>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-size: 13px; margin-bottom: 4px;">Parameter</label>
                            <select id="newCondParam" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                                <option value="Suhu Udara">Suhu Udara</option>
                                <option value="Kelembaban Udara">Kelembaban Udara</option>
                                <option value="Kelembaban Tanah">Kelembaban Tanah</option>
                                <option value="pH">pH</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; margin-bottom: 4px;">Operator</label>
                            <select id="newCondOp" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                                <option value="<">< (Kurang dari)</option>
                                <option value=">">> (Lebih dari)</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; margin-bottom: 4px;">Nilai</label>
                            <input type="number" step="0.1" id="newCondVal" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; margin-bottom: 4px;">Pompa</label>
                            <select id="newCondAction" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                                <option value="ON">Nyala (ON)</option>
                                <option value="OFF">Mati (OFF)</option>
                            </select>
                        </div>
                    </div>
                    <button class="ds-btn-primary" onclick="submitNewAreaCondition()">Tambah Kondisi</button>
                </div>
            </div>

            <!-- Tab Jadwal -->
            <div id="ruleSchedContent" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
                    <h4 style="margin: 0; font-size: 16px; font-weight: 600;">Daftar Jadwal Rutin</h4>
                </div>
                <div style="border: 1px solid var(--gray-200); border-radius: var(--radius-md); overflow: hidden; margin-bottom: var(--space-6);">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background: var(--gray-50);">
                            <tr>
                                <th style="padding: var(--space-3); text-align: left; font-size: 12px; color: var(--gray-500);">Waktu</th>
                                <th style="padding: var(--space-3); text-align: center; font-size: 12px; color: var(--gray-500);">Aksi Pompa</th>
                                <th style="padding: var(--space-3); text-align: right; font-size: 12px; color: var(--gray-500);">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="areaSchedBody">
                            <tr><td colspan="3" style="text-align: center; padding: var(--space-4); color: var(--gray-500);">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div style="background: var(--gray-50); padding: var(--space-4); border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
                    <h5 style="margin: 0 0 12px 0; font-size: 14px;">Tambah Jadwal Baru</h5>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-size: 13px; margin-bottom: 4px;">Waktu (Jam:Menit)</label>
                            <input type="time" id="newSchedTime" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; margin-bottom: 4px;">Pompa</label>
                            <select id="newSchedAction" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                                <option value="ON">Nyala (ON)</option>
                                <option value="OFF">Mati (OFF)</option>
                            </select>
                        </div>
                    </div>
                    <button class="ds-btn-primary" onclick="submitNewAreaSchedule()">Tambah Jadwal</button>
                </div>
            </div>

            <!-- Tab Threshold -->
            <div id="ruleThreshContent" style="display: none;">
                <p class="text-body text-gray-500" style="margin-top: 0;">Ubah nilai min/max threshold untuk semua sensor dengan tipe data yang sama di area ini.</p>
                <div style="background: var(--gray-50); padding: var(--space-4); border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; margin-bottom: 4px;">Pilih Tipe Data</label>
                        <select id="bulkThreshType" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                            <option value="Suhu Udara">Suhu Udara</option>
                            <option value="Kelembaban Udara">Kelembaban Udara</option>
                            <option value="Kelembaban Tanah">Kelembaban Tanah</option>
                            <option value="pH">pH</option>
                            <option value="TDS">TDS (Nutrisi)</option>
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-size: 13px; margin-bottom: 4px;">Min Threshold</label>
                            <input type="number" step="0.1" id="bulkThreshMin" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; margin-bottom: 4px;">Max Threshold</label>
                            <input type="number" step="0.1" id="bulkThreshMax" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                        </div>
                    </div>
                    <button class="ds-btn-primary" onclick="submitBulkThreshold()">Terapkan Threshold</button>
                </div>
            </div>
        </div>
    </div>

<script>
let currentManageAreaId = null;

document.addEventListener('DOMContentLoaded', () => {
    const loadingIndicator = document.getElementById('loadingIndicator');
    const detailsContent = document.getElementById('detailsContent');
    const btnDownloadCsv = document.getElementById('btnDownloadCsv');
    
    // UI Elements
    const summaryName = document.getElementById('summaryName');
    const summaryPlant = document.getElementById('summaryPlant');
    const summaryDesc = document.getElementById('summaryDesc');
    const sensorsTbody = document.getElementById('sensorsTbody');
    const actuatorsTbody = document.getElementById('actuatorsTbody');
    const conditionsTbody = document.getElementById('conditionsTbody');
    const schedulesTbody = document.getElementById('schedulesTbody');

    let currentData = {
        area: null,
        sensors: [],
        actuators: [],
        conditions: [],
        schedules: []
    };

    window.refreshCurrentArea = () => {
        if(currentManageAreaId) loadAreaDetails(currentManageAreaId);
    };

    async function loadAreaDetails(areaId) {
        currentManageAreaId = parseInt(areaId);
        
        detailsContent.style.display = 'none';
        loadingIndicator.style.display = 'block';
        btnDownloadCsv.style.display = 'none';

        try {
            // Fetch everything in parallel
            const [areaRes, sensorsRes, actuatorsRes, conditionsRes, schedulesRes] = await Promise.all([
                fetch(`https://api.digdaya.net/isurf/v1/areas/${areaId}`),
                fetch(`https://api.digdaya.net/isurf/v1/sensors/`),
                fetch(`https://api.digdaya.net/isurf/v1/actuators/`),
                fetch(`https://api.digdaya.net/isurf/v1/areas/${areaId}/conditions`),
                fetch(`https://api.digdaya.net/isurf/v1/areas/${areaId}/schedules`)
            ]);

            const area = await areaRes.json();
            const allSensors = await sensorsRes.json();
            const allActuators = await actuatorsRes.json();
            const conditions = await conditionsRes.json();
            const schedules = await schedulesRes.json();

            // Filter
            const sensors = allSensors.filter(s => s.area_id == areaId);
            const actuators = allActuators.filter(a => a.area_id == areaId);

            currentData = { area, sensors, actuators, conditions, schedules };

            // Render
            summaryName.textContent = area.name;
            summaryPlant.textContent = "Tanaman: " + (area.plant || "-");
            summaryDesc.textContent = "Deskripsi: " + (area.description || "-");

            // Render Sensors
            sensorsTbody.innerHTML = '';
            if (sensors.length === 0) sensorsTbody.innerHTML = '<tr><td colspan="4" class="py-3 text-center text-gray-500">Tidak ada sensor</td></tr>';
            sensors.forEach(s => {
                let min = s.min_threshold !== null ? s.min_threshold : '-';
                let max = s.max_threshold !== null ? s.max_threshold : '-';
                sensorsTbody.innerHTML += `<tr class="border-b border-gray-100"><td class="py-3"><b>${s.id}</b><br><span class="text-xs text-gray-500">${s.name}</span></td><td class="py-3">${s.data_type}</td><td class="py-3">${min} — ${max}</td><td class="py-3 text-right"><button class="ds-btn-outline text-xs px-2 py-1 mr-1 text-blue-600 border-blue-200" onclick='openEditSensorModal(${JSON.stringify(s).replace(/'/g, "&#39;")})'>Edit</button><button class="ds-btn-outline text-xs px-2 py-1 text-red-600 border-red-200" onclick="deleteSensorData('${s.id}')">Hapus</button></td></tr>`;
            });

            // Render Actuators
            actuatorsTbody.innerHTML = '';
            if (actuators.length === 0) actuatorsTbody.innerHTML = '<tr><td colspan="4" class="py-3 text-center text-gray-500">Tidak ada aktuator</td></tr>';
            actuators.forEach(a => {
                let badgeClass = a.valve_status === 'ON' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700';
                actuatorsTbody.innerHTML += `<tr class="border-b border-gray-100"><td class="py-3"><b>${a.id}</b><br><span class="text-xs text-gray-500">${a.name}</span></td><td class="py-3"><span class="px-2 py-1 rounded text-xs font-bold ${badgeClass}">${a.valve_status}</span></td><td class="py-3"><label class="ds-switch" style="display:inline-block;"><input type="checkbox" ${a.is_auto_enabled ? 'checked' : ''} onchange="toggleAreaActuator('${a.id}', this.checked)"><span class="ds-slider"></span></label></td><td class="py-3 text-right"><button class="ds-btn-outline text-xs px-2 py-1 mr-1 text-blue-600 border-blue-200" onclick='openEditActuatorModal(${JSON.stringify(a).replace(/'/g, "&#39;")})'>Edit</button><button class="ds-btn-outline text-xs px-2 py-1 text-red-600 border-red-200" onclick="deleteActuatorData('${a.id}')">Hapus</button></td></tr>`;
            });

            // Render Conditions
            conditionsTbody.innerHTML = '';
            if (conditions.length === 0) conditionsTbody.innerHTML = '<tr><td colspan="3" class="py-3 text-center text-gray-500">Tidak ada aturan</td></tr>';
            conditions.forEach(c => {
                conditionsTbody.innerHTML += `<tr class="border-b border-gray-100"><td class="py-3"><b>${c.data_type}</b> ${c.operator} ${c.value}</td><td class="py-3 text-primary-600 font-medium">${c.action}</td><td class="py-3 text-right"><button class="ds-btn-outline text-xs px-2 py-1 text-red-600 border-red-200" onclick="deleteAreaCondition(${c.id})">Hapus</button></td></tr>`;
            });

            // Render Schedules
            schedulesTbody.innerHTML = '';
            if (schedules.length === 0) schedulesTbody.innerHTML = '<tr><td colspan="3" class="py-3 text-center text-gray-500">Tidak ada jadwal</td></tr>';
            schedules.forEach(sc => {
                schedulesTbody.innerHTML += `<tr class="border-b border-gray-100"><td class="py-3 font-medium">${sc.time}</td><td class="py-3 text-primary-600 font-medium">${sc.action}</td><td class="py-3 text-right"><button class="ds-btn-outline text-xs px-2 py-1 text-red-600 border-red-200" onclick="deleteAreaSchedule(${sc.id})">Hapus</button></td></tr>`;
            });

            loadingIndicator.style.display = 'none';
            detailsContent.style.display = 'block';
            btnDownloadCsv.style.display = 'flex';

        } catch (e) {
            loadingIndicator.style.display = 'none';
            alert("Gagal memuat data dari API FastAPI.");
            console.error(e);
        }
    }

    // Initialize from URL
    const urlParams = new URLSearchParams(window.location.search);
    const areaIdFromUrl = urlParams.get('id');
    if (areaIdFromUrl) {
        loadAreaDetails(areaIdFromUrl);
    } else {
        loadingIndicator.style.display = 'none';
        detailsContent.innerHTML = '<div class="text-center py-10"><p class="text-gray-500">Area tidak ditemukan. Silakan kembali ke menu Kontrol Area.</p></div>';
        detailsContent.style.display = 'block';
    }

    // CSV Download Function
    btnDownloadCsv.addEventListener('click', () => {
        if (!currentData.area) return;
        let csv = [];
        const addRow = (rowArr) => { csv.push(rowArr.map(col => `"${String(col).replace(/"/g, '""')}"`).join(',')); };

        addRow(["LAPORAN DETAIL AREA"]);
        addRow(["Dicetak Pada", new Date().toLocaleString()]);
        addRow([]);
        addRow(["Nama Area", currentData.area.name]);
        addRow(["Tanaman", currentData.area.plant || "-"]);
        addRow(["Deskripsi", currentData.area.description || "-"]);
        addRow([]);

        addRow(["DAFTAR SENSOR"]);
        addRow(["ID Sensor", "Nama Sensor", "Tipe Data", "Ambang Min", "Ambang Max"]);
        currentData.sensors.forEach(s => { addRow([s.id, s.name, s.data_type, s.min_threshold ?? "-", s.max_threshold ?? "-"]); });
        if(currentData.sensors.length === 0) addRow(["(Kosong)"]);
        addRow([]);

        addRow(["DAFTAR AKTUATOR"]);
        addRow(["ID Aktuator", "Nama Aktuator", "Status Valve", "Mode Auto"]);
        currentData.actuators.forEach(a => { addRow([a.id, a.name, a.valve_status, a.is_auto_enabled ? "Aktif" : "Nonaktif"]); });
        if(currentData.actuators.length === 0) addRow(["(Kosong)"]);
        addRow([]);

        addRow(["ATURAN KONDISIONAL"]);
        addRow(["Tipe Data", "Operator", "Nilai", "Aksi"]);
        currentData.conditions.forEach(c => { addRow([c.data_type, c.operator, c.value, c.action]); });
        if(currentData.conditions.length === 0) addRow(["(Kosong)"]);
        addRow([]);

        addRow(["ATURAN JADWAL WAKTU"]);
        addRow(["Waktu", "Aksi"]);
        currentData.schedules.forEach(sc => { addRow([sc.time, sc.action]); });
        if(currentData.schedules.length === 0) addRow(["(Kosong)"]);
        
        let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
        let encodedUri = encodeURI(csvContent);
        let link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        let safeName = currentData.area.name.replace(/[^a-z0-9]/gi, '_').toLowerCase();
        link.setAttribute("download", `laporan_area_${safeName}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});

// DEVICE MANAGEMENT ACTIONS
window.toggleAreaActuator = async function(id, isChecked) {
    try {
        await ISURF_API.toggleActuatorAuto(id, isChecked);
        window.refreshCurrentArea();
    } catch (e) {
        alert('Gagal mengatur otomatisasi aktuator');
        window.refreshCurrentArea();
    }
};

window.openAddSensorModal = function() {
    document.getElementById('addSensorModal').style.display = 'flex';
};
window.submitNewSensor = async function() {
    try {
        await ISURF_API.addSensor({
            id: document.getElementById('newSensorId').value,
            name: document.getElementById('newSensorName').value,
            data_type: document.getElementById('newSensorType').value,
            min_threshold: document.getElementById('newSensorMin').value ? parseFloat(document.getElementById('newSensorMin').value) : null,
            max_threshold: document.getElementById('newSensorMax').value ? parseFloat(document.getElementById('newSensorMax').value) : null,
            area_id: currentManageAreaId
        });
        alert('Sensor berhasil ditambahkan');
        document.getElementById('addSensorModal').style.display = 'none';
        window.refreshCurrentArea();
    } catch(e) { alert(e.message); }
};

window.openAddActuatorModal = function() {
    document.getElementById('addActuatorModal').style.display = 'flex';
};
window.submitNewActuator = async function() {
    try {
        await ISURF_API.addActuator({
            id: document.getElementById('newActId').value,
            name: document.getElementById('newActName').value,
            flow_rate_per_sec: parseFloat(document.getElementById('newActFlow').value),
            area_id: currentManageAreaId
        });
        alert('Aktuator berhasil ditambahkan');
        document.getElementById('addActuatorModal').style.display = 'none';
        window.refreshCurrentArea();
    } catch(e) { alert(e.message); }
};

window.openEditSensorModal = function(sensor) {
    document.getElementById('editSensorId').value = sensor.id;
    document.getElementById('editSensorName').value = sensor.name;
    document.getElementById('editSensorType').value = sensor.data_type;
    document.getElementById('editSensorMin').value = sensor.min_threshold || '';
    document.getElementById('editSensorMax').value = sensor.max_threshold || '';
    document.getElementById('editSensorModal').style.display = 'flex';
};
window.submitEditSensor = async function() {
    try {
        const id = document.getElementById('editSensorId').value;
        await ISURF_API.updateSensor(id, {
            id: id,
            name: document.getElementById('editSensorName').value,
            data_type: document.getElementById('editSensorType').value,
            min_threshold: document.getElementById('editSensorMin').value ? parseFloat(document.getElementById('editSensorMin').value) : null,
            max_threshold: document.getElementById('editSensorMax').value ? parseFloat(document.getElementById('editSensorMax').value) : null,
            area_id: currentManageAreaId
        });
        alert('Sensor diperbarui');
        document.getElementById('editSensorModal').style.display = 'none';
        window.refreshCurrentArea();
    } catch(e) { alert(e.message); }
};
window.deleteSensorData = async function(id) {
    if(!confirm('Yakin ingin menghapus sensor ini?')) return;
    try {
        await ISURF_API.deleteSensor(id);
        alert('Sensor dihapus');
        window.refreshCurrentArea();
    } catch(e) { alert(e.message); }
};

window.openEditActuatorModal = function(actuator) {
    document.getElementById('editActId').value = actuator.id;
    document.getElementById('editActName').value = actuator.name;
    document.getElementById('editActFlow').value = actuator.flow_rate_per_sec;
    document.getElementById('editActuatorModal').style.display = 'flex';
};
window.submitEditActuator = async function() {
    try {
        const id = document.getElementById('editActId').value;
        await ISURF_API.updateActuator(id, {
            id: id,
            name: document.getElementById('editActName').value,
            flow_rate_per_sec: parseFloat(document.getElementById('editActFlow').value),
            area_id: currentManageAreaId
        });
        alert('Aktuator diperbarui');
        document.getElementById('editActuatorModal').style.display = 'none';
        window.refreshCurrentArea();
    } catch(e) { alert(e.message); }
};
window.deleteActuatorData = async function(id) {
    if(!confirm('Yakin ingin menghapus aktuator ini?')) return;
    try {
        await ISURF_API.deleteActuator(id);
        alert('Aktuator dihapus');
        window.refreshCurrentArea();
    } catch(e) { alert(e.message); }
};

// RULES MANAGEMENT
window.switchRuleTab = function(tabName) {
    document.getElementById('ruleCondContent').style.display = tabName === 'cond' ? 'block' : 'none';
    document.getElementById('ruleSchedContent').style.display = tabName === 'sched' ? 'block' : 'none';
    document.getElementById('ruleThreshContent').style.display = tabName === 'thresh' ? 'block' : 'none';
    document.getElementById('tabRuleCond').style.borderBottomColor = tabName === 'cond' ? 'var(--green-600)' : 'transparent';
    document.getElementById('tabRuleCond').style.color = tabName === 'cond' ? 'var(--green-700)' : 'var(--gray-500)';
    document.getElementById('tabRuleSched').style.borderBottomColor = tabName === 'sched' ? 'var(--green-600)' : 'transparent';
    document.getElementById('tabRuleSched').style.color = tabName === 'sched' ? 'var(--green-700)' : 'var(--gray-500)';
    document.getElementById('tabRuleThresh').style.borderBottomColor = tabName === 'thresh' ? 'var(--green-600)' : 'transparent';
    document.getElementById('tabRuleThresh').style.color = tabName === 'thresh' ? 'var(--green-700)' : 'var(--gray-500)';
};

window.openAreaRulesModal = function() {
    document.getElementById('areaRulesModal').style.display = 'flex';
    switchRuleTab('cond');
};

window.submitNewAreaCondition = async function() {
    try {
        await fetch(`https://api.digdaya.net/isurf/v1/areas/${currentManageAreaId}/conditions`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + ISURF_API.apiKey},
            body: JSON.stringify({
                data_type: document.getElementById('newCondParam').value,
                operator: document.getElementById('newCondOp').value,
                value: parseFloat(document.getElementById('newCondVal').value),
                action: document.getElementById('newCondAction').value
            })
        });
        alert('Kondisi ditambahkan');
        document.getElementById('areaRulesModal').style.display = 'none';
        window.refreshCurrentArea();
    } catch(e) { alert('Error: ' + e); }
};

window.deleteAreaCondition = async function(ruleId) {
    if(!confirm('Hapus kondisi ini?')) return;
    try {
        await fetch(`https://api.digdaya.net/isurf/v1/areas/${currentManageAreaId}/conditions/${ruleId}`, {
            method: 'DELETE', headers: {'Authorization': 'Bearer ' + ISURF_API.apiKey}
        });
        window.refreshCurrentArea();
    } catch(e) { alert('Error: ' + e); }
};

window.submitNewAreaSchedule = async function() {
    try {
        await fetch(`https://api.digdaya.net/isurf/v1/areas/${currentManageAreaId}/schedules`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + ISURF_API.apiKey},
            body: JSON.stringify({
                time: document.getElementById('newSchedTime').value + ':00',
                action: document.getElementById('newSchedAction').value
            })
        });
        alert('Jadwal ditambahkan');
        document.getElementById('areaRulesModal').style.display = 'none';
        window.refreshCurrentArea();
    } catch(e) { alert('Error: ' + e); }
};

window.deleteAreaSchedule = async function(ruleId) {
    if(!confirm('Hapus jadwal ini?')) return;
    try {
        await fetch(`https://api.digdaya.net/isurf/v1/areas/${currentManageAreaId}/schedules/${ruleId}`, {
            method: 'DELETE', headers: {'Authorization': 'Bearer ' + ISURF_API.apiKey}
        });
        window.refreshCurrentArea();
    } catch(e) { alert('Error: ' + e); }
};

window.submitBulkThreshold = async function() {
    try {
        await fetch(`https://api.digdaya.net/isurf/v1/areas/${currentManageAreaId}/sensors/thresholds`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + ISURF_API.apiKey},
            body: JSON.stringify({
                data_type: document.getElementById('bulkThreshType').value,
                min_threshold: parseFloat(document.getElementById('bulkThreshMin').value),
                max_threshold: parseFloat(document.getElementById('bulkThreshMax').value)
            })
        });
        alert('Threshold massal diterapkan');
        document.getElementById('areaRulesModal').style.display = 'none';
        window.refreshCurrentArea();
    } catch(e) { alert('Error: ' + e); }
};
</script>