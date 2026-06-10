<?php
/** @var yii\web\View $this */
$this->title = 'Irrigation Control';
?>
<div style="display: flex; flex-direction: column; gap: var(--space-5);">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="text-h2">Irrigation Control</h1>
        <span class="ds-badge ds-badge-warning" id="status-badge">Loading...</span>
    </div>

    <!-- Device Selector -->
    <div style="background: white; padding: var(--space-4); border-radius: var(--radius-md); box-shadow: var(--elevation-1); display: flex; align-items: center; gap: var(--space-3);">
        <label class="text-body font-bold">Select Controller:</label>
        <select id="deviceSelect" onchange="switchDevice()" style="padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px; min-width: 200px;">
            <option value="">Loading...</option>
        </select>
    </div>

    <div style="display: grid; grid-template-columns: 1fr; gap: var(--space-5); @media(min-width: 1024px){ grid-template-columns: 1fr 2fr; }">
        <!-- Control Panel -->
        <div style="background: white; padding: var(--space-5); border-radius: var(--radius-md); box-shadow: var(--elevation-1);">
            <h3 class="text-h3" style="margin-bottom: var(--space-4);">Manual Override</h3>
            
            <div style="display: flex; justify-content: center; padding: var(--space-6) 0;">
                <button id="big-pump-btn" onclick="togglePump()" style="width: 150px; height: 150px; border-radius: 50%; background-color: var(--gray-100); border: 8px solid var(--gray-200); color: var(--gray-500); font-weight: 700; font-size: 24px; cursor: pointer; transition: all 0.3s; box-shadow: inset 0 4px 6px rgba(0,0,0,0.1);">
                    OFF
                </button>
            </div>
            <p class="text-body text-gray-500 text-center mt-4">Tap to manually toggle the main water pump. This overrides the automatic schedule for 30 minutes.</p>
        </div>

        <!-- Schedules -->
        <div style="background: white; padding: var(--space-5); border-radius: var(--radius-md); box-shadow: var(--elevation-1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
                <h3 class="text-h3">Active Schedules</h3>
                <button class="ds-btn-primary" onclick="openAddModal()" style="padding: var(--space-1) var(--space-3); font-size: 12px;">+ Add</button>
            </div>
            
            <div id="schedule-list" style="display: flex; flex-direction: column; gap: var(--space-3);">
                <p class="text-gray-500">Loading schedules...</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Schedule Modal -->
<div id="addScheduleModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: var(--space-4);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 500px; display: flex; flex-direction: column; box-shadow: var(--elevation-3);">
        <div style="padding: var(--space-5); border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
            <h3 class="text-h3" style="margin: 0;">Add New Schedule</h3>
            <button onclick="closeAddModal()" style="background: none; border: none; cursor: pointer; color: var(--gray-400);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div style="padding: var(--space-5);">
            <div style="margin-bottom: 16px;">
                <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Schedule Name</label>
                <input type="text" id="schName" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" placeholder="e.g. Morning Watering">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Start Time</label>
                    <input type="time" id="schTime" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                </div>
                <div>
                    <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Duration (Minutes)</label>
                    <input type="number" id="schDuration" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" value="15">
                </div>
            </div>
            <div style="margin-bottom: 16px;">
                <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Days of Week</label>
                <input type="text" id="schDays" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" placeholder="e.g. Everyday OR Mon, Wed, Fri">
            </div>
        </div>
        <div style="padding: var(--space-5); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: var(--space-3);">
            <button class="ds-btn-secondary" onclick="closeAddModal()">Cancel</button>
            <button class="ds-btn-primary" onclick="submitSchedule()" id="submitSchBtn">Save Schedule</button>
        </div>
    </div>
</div>

<script src="<?= Yii::getAlias('@web') ?>/js/isurf-api.js"></script>
<script>
    let isPumpOn = false;
    let selectedDeviceId = null;
    const pumpBtn = document.getElementById('big-pump-btn');
    const badge = document.getElementById('status-badge');

    async function init() {
        // Load devices into select
        const devices = await iSurfAPI.getDevices();
        const select = document.getElementById('deviceSelect');
        select.innerHTML = '';
        
        let hasIrrigation = false;
        devices.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = `${d.name} (${d.type})`;
            select.appendChild(opt);
            if(!hasIrrigation && (d.type === 'esp32_irrigation' || d.type === 'irrigation')) {
                opt.selected = true;
                hasIrrigation = true;
            }
        });
        
        if (devices.length > 0 && !hasIrrigation) {
            select.selectedIndex = 0;
        }

        switchDevice();
    }

    async function switchDevice() {
        selectedDeviceId = document.getElementById('deviceSelect').value;
        if (!selectedDeviceId) return;
        await refreshStatus();
        await refreshSchedules();
    }

    async function refreshStatus() {
        try {
            const res = await fetch(`${iSurfAPI.baseUrl}/irrigation/status?device_id=${selectedDeviceId}`);
            if(res.ok) {
                const data = await res.json();
                isPumpOn = data.main_valve === 'ON';
                updatePumpUI();
                
                if (data.status === 'Manual Override') {
                    badge.textContent = `Manual Override until ${iSurfAPI.formatTimeWithTZ(data.until)}`;
                    badge.className = 'ds-badge ds-badge-warning';
                } else {
                    badge.textContent = 'Auto Mode';
                    badge.className = 'ds-badge ds-badge-success';
                }
            }
        } catch (e) {
            console.error(e);
        }
    }

    function updatePumpUI() {
        if(isPumpOn){
            pumpBtn.style.backgroundColor = 'var(--primary-100)';
            pumpBtn.style.borderColor = 'var(--primary-500)';
            pumpBtn.style.color = 'var(--primary-700)';
            pumpBtn.textContent = 'ON';
        } else {
            pumpBtn.style.backgroundColor = 'var(--gray-100)';
            pumpBtn.style.borderColor = 'var(--gray-200)';
            pumpBtn.style.color = 'var(--gray-500)';
            pumpBtn.textContent = 'OFF';
        }
    }

    async function togglePump() {
        if (!selectedDeviceId) return alert("Select a device first");
        pumpBtn.disabled = true;
        
        const newAction = isPumpOn ? 'OFF' : 'ON';
        try {
            await iSurfAPI.triggerManualPump(selectedDeviceId, newAction, 30);
            await refreshStatus();
        } catch(e) {
            alert('Failed to toggle pump');
        } finally {
            pumpBtn.disabled = false;
        }
    }

    async function refreshSchedules() {
        const list = document.getElementById('schedule-list');
        list.innerHTML = '<p class="text-gray-500">Loading schedules...</p>';
        
        const schedules = await iSurfAPI.getSchedules(selectedDeviceId);
        
        if(schedules.length === 0) {
            list.innerHTML = '<p class="text-gray-500">No active schedules. Click + Add to create one.</p>';
            return;
        }
        
        let html = '';
        schedules.forEach(sch => {
            html += `
            <div style="border: 1px solid var(--gray-200); padding: var(--space-3) var(--space-4); border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p class="text-body-lg font-bold" style="margin:0">${sch.name}</p>
                    <p class="text-caption text-gray-500" style="margin:0">${sch.days_of_week} • ${sch.start_time} • ${sch.duration_minutes} mins</p>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button onclick="delSchedule(${sch.id})" style="background: none; border: none; color: var(--danger); cursor: pointer; padding: 4px;">Delete</button>
                    <div style="width: 40px; height: 24px; border-radius: 12px; background-color: ${sch.is_active ? 'var(--primary-500)' : 'var(--gray-300)'}; position: relative;">
                        <span style="position: absolute; right: ${sch.is_active ? '2px' : 'auto'}; left: ${sch.is_active ? 'auto' : '2px'}; top: 2px; width: 20px; height: 20px; background: white; border-radius: 50%;"></span>
                    </div>
                </div>
            </div>`;
        });
        list.innerHTML = html;
    }

    function openAddModal() {
        if (!selectedDeviceId) return alert("Select a device first");
        document.getElementById('schName').value = '';
        document.getElementById('schTime').value = '06:00';
        document.getElementById('schDays').value = 'Everyday';
        document.getElementById('addScheduleModal').style.display = 'flex';
    }

    function closeAddModal() {
        document.getElementById('addScheduleModal').style.display = 'none';
    }

    async function submitSchedule() {
        const btn = document.getElementById('submitSchBtn');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        
        const payload = {
            device_id: parseInt(selectedDeviceId),
            name: document.getElementById('schName').value,
            start_time: document.getElementById('schTime').value + ':00',
            duration_minutes: parseInt(document.getElementById('schDuration').value),
            days_of_week: document.getElementById('schDays').value,
            is_active: true
        };
        
        try {
            await iSurfAPI.addSchedule(payload);
            closeAddModal();
            refreshSchedules();
        } catch(e) {
            alert('Failed to add schedule: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save Schedule';
        }
    }

    async function delSchedule(id) {
        if(confirm("Delete this schedule?")) {
            await iSurfAPI.deleteSchedule(id);
            refreshSchedules();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        init();
        setInterval(refreshStatus, 15000); // Polling status
    });
</script>
