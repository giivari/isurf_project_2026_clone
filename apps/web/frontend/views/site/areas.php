<?php
use yii\helpers\Html;

$this->title = 'Manajemen Area & Perangkat';
?>
<div class="areas-page">
    <div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-6);">
        <div>
            <h1 class="text-h2" style="margin-bottom: var(--space-2);">Kelola Area & Perangkat</h1>
            <p class="text-body text-gray-500">Buat Area (Greenhouse/Bedengan) dan tautkan perangkat sensor & aktuator di dalamnya.</p>
        </div>
        <button class="ds-btn-primary" style="white-space: nowrap;" onclick="openAddAreaModal()">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Area
        </button>
    </div>

    <div style="background: white; border-radius: var(--radius-lg); box-shadow: var(--elevation-1); overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
            <thead>
                <tr style="background: var(--gray-50); border-bottom: 1px solid var(--gray-200); text-align: left;">
                    <th style="padding: var(--space-4); color: var(--gray-500); font-size: 12px; font-weight: 600; text-transform: uppercase;">Nama Area</th>
                    <th style="padding: var(--space-4); color: var(--gray-500); font-size: 12px; font-weight: 600; text-transform: uppercase;">Tanaman</th>
                    <th style="padding: var(--space-4); color: var(--gray-500); font-size: 12px; font-weight: 600; text-transform: uppercase; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody id="areasTableBody">
                <tr><td colspan="3" style="text-align: center; padding: var(--space-6); color: var(--gray-500);">Loading areas...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Area -->
<div id="addAreaModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: var(--space-4);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 500px; display: flex; flex-direction: column; box-shadow: var(--elevation-3);">
        <div style="padding: var(--space-5); border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
            <h3 class="text-h3" style="margin: 0;">Tambah Area Baru</h3>
            <button onclick="closeAddAreaModal()" style="background: none; border: none; cursor: pointer; color: var(--gray-400);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div style="padding: var(--space-5);">
            <div style="margin-bottom: 16px;">
                <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Nama Area *</label>
                <input type="text" id="newAreaName" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" placeholder="cth: Area A - Selatan">
            </div>
            <div style="margin-bottom: 16px;">
                <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Jenis Tanaman</label>
                <input type="text" id="newAreaPlant" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" placeholder="cth: Tomat, Selada (Opsional)">
            </div>
            <div style="margin-bottom: 16px;">
                <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Deskripsi</label>
                <textarea id="newAreaDesc" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" rows="2" placeholder="Deskripsi area..."></textarea>
            </div>
        </div>
        <div style="padding: var(--space-5); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: var(--space-3);">
            <button class="ds-btn-outline" onclick="closeAddAreaModal()">Batal</button>
            <button class="ds-btn-primary" id="submitAreaBtn" onclick="submitNewArea()">Simpan Area</button>
        </div>
    </div>
</div>

<!-- Modal Manage Devices in Area -->
<div id="manageDevicesModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: flex-start; justify-content: center; padding: var(--space-6); overflow-y: auto;">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 800px; display: flex; flex-direction: column; box-shadow: var(--elevation-3); margin-top: 40px; margin-bottom: 40px;">
        <div style="padding: var(--space-5); border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; border-radius: var(--radius-lg) var(--radius-lg) 0 0; z-index: 10;">
            <div>
                <h3 class="text-h3" style="margin: 0; color: var(--gray-900);" id="manageDevicesTitle">Perangkat di Area</h3>
                <p class="text-caption" style="color: var(--gray-500); margin: 4px 0 0 0;">Tambahkan sensor atau aktuator fisik ke area ini</p>
            </div>
            <button onclick="closeManageDevicesModal()" style="background: none; border: none; cursor: pointer; color: var(--gray-400);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div style="padding: var(--space-5);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
                <h4 style="margin: 0; font-size: 16px; font-weight: 600;">Daftar Sensor</h4>
                <button class="ds-btn-outline" style="padding: 6px 12px; font-size: 13px;" onclick="openAddSensorModal()">+ Tambah Sensor</button>
            </div>
            <div style="border: 1px solid var(--gray-200); border-radius: var(--radius-md); overflow: hidden; margin-bottom: var(--space-6);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: var(--gray-50);">
                        <tr>
                            <th style="padding: var(--space-3); text-align: left; font-size: 12px; color: var(--gray-500);">ID & Nama</th>
                            <th style="padding: var(--space-3); text-align: left; font-size: 12px; color: var(--gray-500);">Tipe Data</th>
                            <th style="padding: var(--space-3); text-align: left; font-size: 12px; color: var(--gray-500);">Threshold (Min/Max)</th>
                            <th style="padding: var(--space-3); text-align: center; font-size: 12px; color: var(--gray-500);">Status</th>
                            <th style="padding: var(--space-3); text-align: right; font-size: 12px; color: var(--gray-500);">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="areaSensorsBody">
                        <tr><td colspan="4" style="text-align: center; padding: var(--space-4); color: var(--gray-500);">Loading...</td></tr>
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
                <h4 style="margin: 0; font-size: 16px; font-weight: 600;">Daftar Aktuator</h4>
                <button class="ds-btn-outline" style="padding: 6px 12px; font-size: 13px;" onclick="openAddActuatorModal()">+ Tambah Aktuator</button>
            </div>
            <div style="border: 1px solid var(--gray-200); border-radius: var(--radius-md); overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: var(--gray-50);">
                        <tr>
                            <th style="padding: var(--space-3); text-align: left; font-size: 12px; color: var(--gray-500);">ID & Nama</th>
                            <th style="padding: var(--space-3); text-align: left; font-size: 12px; color: var(--gray-500);">Debit Air (L/s)</th>
                            <th style="padding: var(--space-3); text-align: center; font-size: 12px; color: var(--gray-500);">Status Valve</th>
                            <th style="padding: var(--space-3); text-align: center; font-size: 12px; color: var(--gray-500);">Otomatisasi</th>
                            <th style="padding: var(--space-3); text-align: right; font-size: 12px; color: var(--gray-500);">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="areaActuatorsBody">
                        <tr><td colspan="4" style="text-align: center; padding: var(--space-4); color: var(--gray-500);">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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
</div>

<?php
$this->registerJsFile('@web/js/isurf-api.js?v=' . time(), ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<script>
let currentManageAreaId = null;
let currentManageAreaName = null;

async function loadAreas() {
    const tbody = document.getElementById('areasTableBody');
    tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: var(--space-6); color: var(--gray-500);">Memuat data...</td></tr>';
    
    try {
        const areas = await ISURF_API.getAreas();
        if (areas.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: var(--space-6); color: var(--gray-500);">Belum ada area yang terdaftar.</td></tr>`;
            return;
        }
        
        tbody.innerHTML = '';
        areas.forEach(area => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid var(--gray-200)';
            
            tr.innerHTML = `
                <td style="padding: var(--space-4);">
                    <p style="font-weight: 600; color: var(--gray-900); margin: 0;">${area.name}</p>
                    <p style="font-size: 12px; color: var(--gray-500); margin: 0;">${area.description || '-'}</p>
                </td>
                <td style="padding: var(--space-4); color: var(--gray-700);">
                    ${area.plant || 'Belum ditentukan'}
                </td>
                <td style="padding: var(--space-4); text-align: right; white-space: nowrap;">
                    <a href="${window.location.origin}${window.location.pathname}?r=site/area-details&id=${area.id}" class="ds-btn-outline" style="padding: 6px 12px; font-size: 13px; text-decoration: none; display: inline-block;">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Detail Aturan Area
                    </a>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: var(--space-6); color: var(--red-500);">Gagal memuat data.</td></tr>`;
    }
}

function openAddAreaModal() {
    document.getElementById('newAreaName').value = '';
    document.getElementById('newAreaPlant').value = '';
    document.getElementById('newAreaDesc').value = '';
    document.getElementById('addAreaModal').style.display = 'flex';
}

function closeAddAreaModal() {
    document.getElementById('addAreaModal').style.display = 'none';
}

async function submitNewArea() {
    const name = document.getElementById('newAreaName').value;
    const plant = document.getElementById('newAreaPlant').value;
    const desc = document.getElementById('newAreaDesc').value;
    
    if(!name) {
        alert("Nama Area wajib diisi!");
        return;
    }

    const btn = document.getElementById('submitAreaBtn');
    btn.disabled = true;
    btn.innerHTML = 'Menyimpan...';

    try {
        await ISURF_API.addArea({ name: name, plant: plant, description: desc });
        closeAddAreaModal();
        alert("Area berhasil ditambahkan!");
        loadAreas();
    } catch (err) {
        alert("Gagal menambahkan: " + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Simpan Area';
    }
}

async function openManageDevicesModal(areaId, areaName) {
    currentManageAreaId = areaId;
    currentManageAreaName = areaName;
    document.getElementById('manageDevicesTitle').textContent = `Perangkat di ${areaName}`;
    document.getElementById('manageDevicesModal').style.display = 'flex';
    
    refreshDevicesList();
}

async function refreshDevicesList() {
    const sBody = document.getElementById('areaSensorsBody');
    const aBody = document.getElementById('areaActuatorsBody');
    sBody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 12px; color: gray;">Loading...</td></tr>';
    aBody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 12px; color: gray;">Loading...</td></tr>';
    
    const sensors = await ISURF_API.getSensors();
    const actuators = await ISURF_API.getActuators();
    
    const areaSensors = sensors.filter(s => s.area_id === currentManageAreaId);
    const areaActuators = actuators.filter(a => a.area_id === currentManageAreaId);
    
    if (areaSensors.length === 0) {
        sBody.innerHTML = `<tr><td colspan="4" style="text-align: center; padding: 12px; color: gray;">Tidak ada sensor di area ini.</td></tr>`;
    } else {
        sBody.innerHTML = '';
        areaSensors.forEach(s => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid var(--gray-200)';
            tr.innerHTML = `
                <td style="padding: 12px;"><b>${s.name}</b><br><small style="color: gray; font-family: monospace;">${s.id}</small></td>
                <td style="padding: 12px;">${s.data_type}</td>
                <td style="padding: 12px;">${s.min_threshold || '-'} / ${s.max_threshold || '-'}</td>
                <td style="padding: 12px; text-align: center;">${s.is_online ? '<span class="ds-badge ds-badge-success">Online</span>' : '<span class="ds-badge ds-badge-danger">Offline</span>'}</td>
                <td style="padding: 12px; text-align: right; white-space: nowrap;">
                    <button class="ds-btn-outline" style="padding: 4px 8px; font-size: 12px; color: var(--blue-600); border-color: var(--blue-200); margin-right: 4px;" onclick='openEditSensorModal(${JSON.stringify(s)})'>Edit</button>
                    <button class="ds-btn-outline" style="padding: 4px 8px; font-size: 12px; color: var(--red-600); border-color: var(--red-200);" onclick="deleteSensorData('${s.id}')">Hapus</button>
                </td>
            `;
            sBody.appendChild(tr);
        });
    }

    if (areaActuators.length === 0) {
        aBody.innerHTML = `<tr><td colspan="4" style="text-align: center; padding: 12px; color: gray;">Tidak ada aktuator di area ini.</td></tr>`;
    } else {
        aBody.innerHTML = '';
        areaActuators.forEach(a => {
            const isRunning = a.valve_status === 'ON';
            const isAuto = a.is_auto_enabled;
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid var(--gray-200)';
            tr.innerHTML = `
                <td style="padding: 12px;"><b>${a.name}</b><br><small style="color: gray; font-family: monospace;">${a.id}</small></td>
                <td style="padding: 12px;">${a.flow_rate_per_sec} L/s</td>
                <td style="padding: 12px; text-align: center;">${isRunning ? '<span class="ds-badge ds-badge-success">ON</span>' : '<span class="ds-badge ds-badge-warning">OFF</span>'}</td>
                <td style="padding: 12px; text-align: center;">
                    <label class="ds-switch" style="display:inline-block;">
                        <input type="checkbox" ${isAuto ? 'checked' : ''} onchange="toggleAreaActuator('${a.id}', this.checked)">
                        <span class="ds-slider"></span>
                    </label>
                </td>
                <td style="padding: 12px; text-align: right; white-space: nowrap;">
                    <button class="ds-btn-outline" style="padding: 4px 8px; font-size: 12px; color: var(--blue-600); border-color: var(--blue-200); margin-right: 4px;" onclick='openEditActuatorModal(${JSON.stringify(a)})'>Edit</button>
                    <button class="ds-btn-outline" style="padding: 4px 8px; font-size: 12px; color: var(--red-600); border-color: var(--red-200);" onclick="deleteActuatorData('${a.id}')">Hapus</button>
                </td>
            `;
            aBody.appendChild(tr);
        });
    }
}

window.toggleAreaActuator = async function(id, isChecked) {
    try {
        await ISURF_API.toggleActuatorAuto(id, isChecked);
        refreshDevicesList();
    } catch (e) {
        alert('Gagal mengatur otomatisasi aktuator');
        refreshDevicesList();
    }
};

function closeManageDevicesModal() {
    document.getElementById('manageDevicesModal').style.display = 'none';
}

function openAddSensorModal() {
    document.getElementById('addSensorModal').style.display = 'flex';
}
async function submitNewSensor() {
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
        refreshDevicesList();
    } catch(e) {
        alert(e.message);
    }
}

function openAddActuatorModal() {
    document.getElementById('addActuatorModal').style.display = 'flex';
}
async function submitNewActuator() {
    try {
        await ISURF_API.addActuator({
            id: document.getElementById('newActId').value,
            name: document.getElementById('newActName').value,
            flow_rate_per_sec: parseFloat(document.getElementById('newActFlow').value),
            area_id: currentManageAreaId
        });
        alert('Aktuator berhasil ditambahkan');
        document.getElementById('addActuatorModal').style.display = 'none';
        refreshDevicesList();
    } catch(e) {
        alert(e.message);
    }
}

// --- Sensor Edit/Delete ---
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
        alert('Sensor berhasil diperbarui');
        document.getElementById('editSensorModal').style.display = 'none';
        refreshDevicesList();
    } catch(e) {
        alert(e.message);
    }
};

window.deleteSensorData = async function(id) {
    if(!confirm('Yakin ingin menghapus sensor ini?')) return;
    try {
        await ISURF_API.deleteSensor(id);
        alert('Sensor dihapus');
        refreshDevicesList();
    } catch(e) {
        alert(e.message);
    }
};

// --- Actuator Edit/Delete ---
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
        alert('Aktuator berhasil diperbarui');
        document.getElementById('editActuatorModal').style.display = 'none';
        refreshDevicesList();
    } catch(e) {
        alert(e.message);
    }
};

window.deleteActuatorData = async function(id) {
    if(!confirm('Yakin ingin menghapus aktuator ini?')) return;
    try {
        await ISURF_API.deleteActuator(id);
        alert('Aktuator dihapus');
        refreshDevicesList();
    } catch(e) {
        alert(e.message);
    }
};

// --- Area Rules & Bulk Thresholds ---
let currentRuleAreaId = null;

window.openAreaRulesModal = function(areaId, areaName) {
    currentRuleAreaId = areaId;
    document.getElementById('areaRulesTitle').textContent = `Aturan Area: ${areaName}`;
    document.getElementById('areaRulesModal').style.display = 'flex';
    switchRuleTab('cond');
    refreshAreaRulesList();
};

window.switchRuleTab = function(tab) {
    document.getElementById('ruleCondContent').style.display = 'none';
    document.getElementById('ruleSchedContent').style.display = 'none';
    document.getElementById('ruleThreshContent').style.display = 'none';
    
    document.getElementById('tabRuleCond').style.borderBottomColor = 'transparent';
    document.getElementById('tabRuleCond').style.color = 'var(--gray-500)';
    document.getElementById('tabRuleSched').style.borderBottomColor = 'transparent';
    document.getElementById('tabRuleSched').style.color = 'var(--gray-500)';
    document.getElementById('tabRuleThresh').style.borderBottomColor = 'transparent';
    document.getElementById('tabRuleThresh').style.color = 'var(--gray-500)';

    if(tab === 'cond') {
        document.getElementById('ruleCondContent').style.display = 'block';
        document.getElementById('tabRuleCond').style.borderBottomColor = 'var(--green-600)';
        document.getElementById('tabRuleCond').style.color = 'var(--green-700)';
    } else if(tab === 'sched') {
        document.getElementById('ruleSchedContent').style.display = 'block';
        document.getElementById('tabRuleSched').style.borderBottomColor = 'var(--green-600)';
        document.getElementById('tabRuleSched').style.color = 'var(--green-700)';
    } else {
        document.getElementById('ruleThreshContent').style.display = 'block';
        document.getElementById('tabRuleThresh').style.borderBottomColor = 'var(--green-600)';
        document.getElementById('tabRuleThresh').style.color = 'var(--green-700)';
    }
};

window.refreshAreaRulesList = async function() {
    const condBody = document.getElementById('areaCondBody');
    const schedBody = document.getElementById('areaSchedBody');
    
    condBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 12px; color: gray;">Loading...</td></tr>';
    schedBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 12px; color: gray;">Loading...</td></tr>';
    
    try {
        const conds = await ISURF_API.getAreaConditions(currentRuleAreaId);
        if(conds.length === 0) {
            condBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 12px; color: gray;">Belum ada aturan kondisi.</td></tr>';
        } else {
            condBody.innerHTML = '';
            conds.forEach(r => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid var(--gray-200)';
                tr.innerHTML = `
                    <td style="padding: 12px;">Jika <b>${r.data_type}</b> ${r.operator} <b>${r.value}</b></td>
                    <td style="padding: 12px; text-align: center;">${r.action}</td>
                    <td style="padding: 12px; text-align: right;">
                        <button class="ds-btn-outline" style="padding: 4px 8px; font-size: 12px; color: var(--red-600); border-color: var(--red-200);" onclick="deleteAreaCondition(${r.id})">Hapus</button>
                    </td>
                `;
                condBody.appendChild(tr);
            });
        }
        
        const scheds = await ISURF_API.getAreaSchedules(currentRuleAreaId);
        if(scheds.length === 0) {
            schedBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 12px; color: gray;">Belum ada jadwal rutin.</td></tr>';
        } else {
            schedBody.innerHTML = '';
            scheds.forEach(r => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid var(--gray-200)';
                tr.innerHTML = `
                    <td style="padding: 12px;">Pukul <b>${r.time}</b></td>
                    <td style="padding: 12px; text-align: center;">${r.action}</td>
                    <td style="padding: 12px; text-align: right;">
                        <button class="ds-btn-outline" style="padding: 4px 8px; font-size: 12px; color: var(--red-600); border-color: var(--red-200);" onclick="deleteAreaSchedule(${r.id})">Hapus</button>
                    </td>
                `;
                schedBody.appendChild(tr);
            });
        }
    } catch(e) {
        condBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 12px; color: red;">Gagal memuat aturan.</td></tr>';
        schedBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 12px; color: red;">Gagal memuat aturan.</td></tr>';
    }
};

window.submitNewAreaCondition = async function() {
    try {
        const data = {
            data_type: document.getElementById('newCondParam').value,
            operator: document.getElementById('newCondOp').value,
            value: parseFloat(document.getElementById('newCondVal').value),
            action: document.getElementById('newCondAction').value
        };
        if(isNaN(data.value)) {
            alert('Nilai harus berupa angka');
            return;
        }
        await ISURF_API.addAreaCondition(currentRuleAreaId, data);
        document.getElementById('newCondVal').value = '';
        refreshAreaRulesList();
    } catch(e) {
        alert(e.message);
    }
};

window.deleteAreaCondition = async function(id) {
    if(!confirm('Hapus kondisi ini?')) return;
    try {
        await ISURF_API.deleteAreaCondition(currentRuleAreaId, id);
        refreshAreaRulesList();
    } catch(e) {
        alert(e.message);
    }
};

window.submitNewAreaSchedule = async function() {
    try {
        const timeVal = document.getElementById('newSchedTime').value;
        const action = document.getElementById('newSchedAction').value;
        if(!timeVal) {
            alert('Isi waktu jadwal');
            return;
        }
        await ISURF_API.addAreaSchedule(currentRuleAreaId, { time: timeVal, action: action });
        document.getElementById('newSchedTime').value = '';
        refreshAreaRulesList();
    } catch(e) {
        alert(e.message);
    }
};

window.deleteAreaSchedule = async function(id) {
    if(!confirm('Hapus jadwal ini?')) return;
    try {
        await ISURF_API.deleteAreaSchedule(currentRuleAreaId, id);
        refreshAreaRulesList();
    } catch(e) {
        alert(e.message);
    }
};

window.submitBulkThreshold = async function() {
    try {
        const type = document.getElementById('bulkThreshType').value;
        const min = document.getElementById('bulkThreshMin').value;
        const max = document.getElementById('bulkThreshMax').value;
        if(!min || !max) {
            alert('Harap isi min dan max threshold');
            return;
        }
        const res = await ISURF_API.updateAreaThresholds(currentRuleAreaId, {
            data_type: type,
            min_threshold: parseFloat(min),
            max_threshold: parseFloat(max)
        });
        alert(res.message);
        document.getElementById('bulkThreshMin').value = '';
        document.getElementById('bulkThreshMax').value = '';
    } catch(e) {
        alert(e.message);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    loadAreas();
});
</script>
