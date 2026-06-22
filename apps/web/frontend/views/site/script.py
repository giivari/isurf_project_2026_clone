import re

with open(r"c:\Users\givar\KULIAH\capstone\ilkom-isurf-project\apps\web\frontend\views\site\areas.php", "r", encoding="utf-8") as f:
    lines = f.readlines()

modals = "".join(lines[122:399]) # Modal Add Sensor down to RuleThreshContent
js = "".join(lines[488:857]) # JS logic for sensors/actuators/rules

# In js, we need to replace currentManageAreaId with areaId
# Actually, the JS in areas.php sets currentManageAreaId when the modal opens.
# In area-details.php, areaId is already defined when an area is selected!
# So we can just set currentManageAreaId = areaId; currentRuleAreaId = areaId;

with open(r"c:\Users\givar\KULIAH\capstone\ilkom-isurf-project\apps\web\frontend\views\site\area-details.php", "r", encoding="utf-8") as f:
    content = f.read()

# Add isurf-api.js include
content = content.replace("<script>", "<?php\n$this->registerJsFile('@web/js/isurf-api.js?v=' . time(), ['depends' => [\\yii\\web\\JqueryAsset::class]]);\n?>\n<script>")

# Inject modals before script
content = content.replace("\n<script>", f"\n{modals}\n<script>")

# Inject JS into script
content = content.replace("document.addEventListener('DOMContentLoaded', async () => {", f"let currentManageAreaId = null;\nlet currentRuleAreaId = null;\n{js}\n\ndocument.addEventListener('DOMContentLoaded', async () => {{")

# Find areaSelect.addEventListener('change', async (e) => {
content = content.replace("const areaId = e.target.value;", "const areaId = e.target.value;\n        currentManageAreaId = areaId;\n        currentRuleAreaId = areaId;")

# Also, the user wants round corners: "sudut kotaknya itu perbaiki"
content = content.replace('class="glass-panel p-6"', 'class="glass-panel p-6" style="border-radius: 16px;"')
content = content.replace('class="glass-panel p-6 mb-6"', 'class="glass-panel p-6 mb-6" style="border-radius: 16px;"')
content = content.replace('class="glass-panel p-6 mb-6 border-l-4"', 'class="glass-panel p-6 mb-6 border-l-4" style="border-radius: 16px;"')

# Now add buttons to the UI
# sensors
btn_sensor = '<div class="flex items-center gap-2 mb-4 justify-between">\n<div class="flex items-center gap-2">\n<div class="p-2 bg-blue-100 text-blue-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg></div><h3 class="text-lg font-bold">Daftar Sensor</h3>\n</div>\n<button class="ds-btn-outline text-xs py-1 px-2" onclick="openAddSensorModal()">+ Tambah</button>\n</div>'
content = content.replace('<div class="flex items-center gap-2 mb-4">\n                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">\n                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>\n                    </div>\n                    <h3 class="text-lg font-bold">Daftar Sensor</h3>\n                </div>', btn_sensor)

# Add edit/delete buttons to the table render logic
content = content.replace("sensorsTbody.innerHTML += `<tr class=\"border-b border-gray-100\"><td class=\"py-3\"><b>${s.id}</b><br><span class=\"text-xs text-gray-500\">${s.name}</span></td><td class=\"py-3\">${s.data_type}</td><td class=\"py-3\">${min} — ${max}</td></tr>`;", "sensorsTbody.innerHTML += `<tr class=\"border-b border-gray-100\"><td class=\"py-3\"><b>${s.id}</b><br><span class=\"text-xs text-gray-500\">${s.name}</span></td><td class=\"py-3\">${s.data_type}</td><td class=\"py-3\">${min} — ${max}</td><td class=\"py-3 text-right\"><button class=\"ds-btn-outline text-xs px-2 py-1 mr-1 text-blue-600 border-blue-200\" onclick='openEditSensorModal(${JSON.stringify(s)})'>Edit</button><button class=\"ds-btn-outline text-xs px-2 py-1 text-red-600 border-red-200\" onclick=\"deleteSensorData('${s.id}')\">Hapus</button></td></tr>`;")
# also add th to sensors
content = content.replace('<th class="pb-2 font-medium">Ambang Batas (Min-Max)</th>', '<th class="pb-2 font-medium">Ambang Batas (Min-Max)</th>\n<th class="pb-2 font-medium text-right">Aksi</th>')

# Actuators
btn_act = '<div class="flex items-center gap-2 mb-4 justify-between">\n<div class="flex items-center gap-2">\n<div class="p-2 bg-orange-100 text-orange-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></div><h3 class="text-lg font-bold">Daftar Aktuator</h3>\n</div>\n<button class="ds-btn-outline text-xs py-1 px-2" onclick="openAddActuatorModal()">+ Tambah</button>\n</div>'
content = content.replace('<div class="flex items-center gap-2 mb-4">\n                    <div class="p-2 bg-orange-100 text-orange-600 rounded-lg">\n                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>\n                    </div>\n                    <h3 class="text-lg font-bold">Daftar Aktuator</h3>\n                </div>', btn_act)

content = content.replace("actuatorsTbody.innerHTML += `<tr class=\"border-b border-gray-100\"><td class=\"py-3\"><b>${a.id}</b><br><span class=\"text-xs text-gray-500\">${a.name}</span></td><td class=\"py-3\"><span class=\"px-2 py-1 rounded text-xs font-bold ${badgeClass}\">${a.valve_status}</span></td><td class=\"py-3\"><span class=\"px-2 py-1 rounded text-xs font-bold ${autoClass}\">${a.is_auto_enabled ? 'Aktif' : 'Nonaktif'}</span></td></tr>`;", "actuatorsTbody.innerHTML += `<tr class=\"border-b border-gray-100\"><td class=\"py-3\"><b>${a.id}</b><br><span class=\"text-xs text-gray-500\">${a.name}</span></td><td class=\"py-3\"><span class=\"px-2 py-1 rounded text-xs font-bold ${badgeClass}\">${a.valve_status}</span></td><td class=\"py-3\"><label class=\"ds-switch\" style=\"display:inline-block;\"><input type=\"checkbox\" ${a.is_auto_enabled ? 'checked' : ''} onchange=\"toggleAreaActuator('${a.id}', this.checked)\"><span class=\"ds-slider\"></span></label></td><td class=\"py-3 text-right\"><button class=\"ds-btn-outline text-xs px-2 py-1 mr-1 text-blue-600 border-blue-200\" onclick='openEditActuatorModal(${JSON.stringify(a)})'>Edit</button><button class=\"ds-btn-outline text-xs px-2 py-1 text-red-600 border-red-200\" onclick=\"deleteActuatorData('${a.id}')\">Hapus</button></td></tr>`;")
content = content.replace('<th class="pb-2 font-medium">Mode Otomatis</th>', '<th class="pb-2 font-medium">Mode Otomatis</th>\n<th class="pb-2 font-medium text-right">Aksi</th>')

# Conditions
btn_cond = '<div class="flex items-center gap-2 mb-4 justify-between">\n<div class="flex items-center gap-2">\n<div class="p-2 bg-purple-100 text-purple-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg></div><h3 class="text-lg font-bold">Aturan Kondisional</h3>\n</div>\n<button class="ds-btn-outline text-xs py-1 px-2" onclick="openAreaRulesModal(currentManageAreaId, document.getElementById(\'summaryName\').innerText)">Kelola Aturan</button>\n</div>'
content = content.replace('<div class="flex items-center gap-2 mb-4">\n                    <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">\n                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>\n                    </div>\n                    <h3 class="text-lg font-bold">Aturan Kondisional</h3>\n                </div>', btn_cond)

with open(r"c:\Users\givar\KULIAH\capstone\ilkom-isurf-project\apps\web\frontend\views\site\area-details.php", "w", encoding="utf-8") as f:
    f.write(content)
