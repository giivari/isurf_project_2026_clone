<script>
let currentManageAreaId = null;

document.addEventListener('DOMContentLoaded', async () => {
    const areaSelect = document.getElementById('areaSelect');
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
        areaSelect.dispatchEvent(new Event('change'));
    };

    // Load Areas
    try {
        const res = await fetch('https://api.digdaya.net/isurf/v1/areas/');
        if (res.ok) {
            const areas = await res.json();
            areaSelect.innerHTML = '<option value="">-- Pilih Area --</option>';
            areas.forEach(a => {
                let opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = a.name;
                areaSelect.appendChild(opt);
            });
            
            // Auto-select from URL
            const urlParams = new URLSearchParams(window.location.search);
            const areaIdFromUrl = urlParams.get('id');
            if (areaIdFromUrl) {
                areaSelect.value = areaIdFromUrl;
                areaSelect.dispatchEvent(new Event('change'));
            }
        }
    } catch (e) {
        areaSelect.innerHTML = '<option value="">Gagal memuat area</option>';
    }

    areaSelect.addEventListener('change', async (e) => {
        const areaId = e.target.value;
        currentManageAreaId = parseInt(areaId);
        
        if (!areaId) {
            detailsContent.style.display = 'none';
            btnDownloadCsv.style.display = 'none';
            return;
        }

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
    });

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
