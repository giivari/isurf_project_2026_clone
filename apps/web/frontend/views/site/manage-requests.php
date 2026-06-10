<?php
use yii\helpers\Html;

$this->title = 'Manage Data Requests';
?>
<style>
    .manage-requests-table {
        width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;
    }
    .manage-requests-table th {
        padding: 16px; background: var(--gray-50); border-bottom: 1px solid var(--gray-200); 
        color: var(--gray-500); font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.05em;
    }
    .manage-requests-table td {
        padding: 16px; border-bottom: 1px solid var(--gray-100);
    }
    .manage-requests-table tbody tr {
        transition: background-color 0.2s;
    }
    .manage-requests-table tbody tr:hover {
        background-color: var(--gray-50);
    }
</style>

<div class="manage-requests-page">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-5);">
        <h1 class="text-h2" style="margin: 0;">Manage Data Requests</h1>
        <div class="ds-badge" style="background-color: #FEE2E2; color: var(--danger); font-size: 13px;">Admin Only</div>
    </div>

    <div style="background: white; border-radius: var(--radius-lg); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); border: 1px solid var(--gray-200); overflow: hidden;">
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

<!-- Modal Review -->
<div id="reviewModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 24px; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 class="text-h3" style="margin-bottom: 16px;">Review Pengajuan</h3>
        <input type="hidden" id="reviewRequestId">
        
        <div style="margin-bottom: 16px;">
            <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Status Keputusan</label>
            <select id="reviewStatus" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                <option value="approved">Setujui (Approved)</option>
                <option value="rejected">Tolak (Rejected)</option>
            </select>
        </div>
        
        <div style="margin-bottom: 24px;">
            <label class="text-caption font-medium" style="display: block; margin-bottom: 8px;">Catatan Admin (Opsional)</label>
            <textarea id="reviewNotes" rows="3" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" placeholder="Tulis catatan jika ditolak..."></textarea>
        </div>
        
        <div style="background: var(--gray-50); padding: 16px; border-radius: 4px; margin-bottom: 24px; border: 1px solid var(--gray-200);">
            <p class="text-caption font-medium mb-2" style="margin-bottom: 8px;">Kustomisasi Unduhan CSV</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div>
                    <label class="text-caption text-gray-600" style="display: block; margin-bottom: 4px;">Dari Tanggal</label>
                    <input type="date" id="customDateStart" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                </div>
                <div>
                    <label class="text-caption text-gray-600" style="display: block; margin-bottom: 4px;">Sampai Tanggal</label>
                    <input type="date" id="customDateEnd" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;">
                </div>
            </div>
            <div style="margin-bottom: 12px;">
                <label class="text-caption text-gray-600" style="display: block; margin-bottom: 4px;">Sensor (Pisahkan dengan koma)</label>
                <input type="text" id="customSensors" style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: 4px;" placeholder="cth: moisture, ph, tds, temperature, ultrasonic">
                <small class="text-gray-400">Gunakan 'all' untuk semua sensor</small>
            </div>
            <button onclick="downloadCustomCsv()" class="ds-btn" style="background: white; border: 1px solid var(--gray-300); width: 100%; justify-content: center;">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download CSV Kustom
            </button>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <button onclick="closeModal()" class="ds-btn" style="background: white; border: 1px solid var(--gray-300);">Batal</button>
            <button onclick="submitReview()" class="ds-btn-primary" id="saveReviewBtn">Simpan Keputusan</button>
        </div>
    </div>
</div>

<script>
// Get JWT from localStorage if login logic stores it there, 
// or for simplicity, we assume session is handled by Yii, but FastAPI needs a token.
// Since we used Yii2 session for auth, we might need a workaround for FastAPI admin check.
// For this MVP, let's assume we have a hardcoded way or bypass for demo if needed.
// Actually, FastAPI has get_current_user. If we pass cookies or it needs Bearer?
// Wait, in previous implementation, we stored jwt in localStorage after login.
const token = localStorage.getItem('jwt_token') || '';

let requestsData = [];

async function loadRequests() {
    try {
        const res = await fetch('http://localhost:8000/api/data-requests/', {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        if(res.ok) {
            requestsData = await res.json();
            renderTable(requestsData);
        } else {
            document.getElementById('requestsTableBody').innerHTML = '<tr><td colspan="7" style="padding: 32px; text-align: center; color: var(--danger);">Gagal memuat data (Harus login admin).</td></tr>';
        }
    } catch(err) {
        console.error(err);
    }
}

function renderTable(data) {
    const tbody = document.getElementById('requestsTableBody');
    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="padding: 32px; text-align: center; color: var(--gray-500);">Belum ada pengajuan.</td></tr>';
        return;
    }

    let html = '';
    data.forEach(req => {
        let statusBadge = '';
        if(req.status === 'pending') statusBadge = '<span class="ds-badge ds-badge-warning">Pending</span>';
        else if(req.status === 'approved') statusBadge = '<span class="ds-badge ds-badge-success">Approved</span>';
        else statusBadge = '<span class="ds-badge ds-badge-danger">Rejected</span>';

        let docLink = req.document_path ? `<a href="http://localhost:8080${req.document_path}" target="_blank" style="color: var(--blue-500); text-decoration: underline;">Lihat PDF</a>` : '-';

        html += `
        <tr style="border-bottom: 1px solid var(--gray-100);">
            <td style="padding: 16px;">${iSurfAPI.formatDateTimeWithTZ(req.created_at)}</td>
            <td style="padding: 16px;">
                <p class="font-medium" style="margin: 0;">${req.full_name}</p>
                <p class="text-caption text-gray-500" style="margin: 0;">${req.nim_nip}</p>
                <a href="mailto:${req.email}" class="text-caption text-primary-500" style="text-decoration: none;">${req.email}</a>
            </td>
            <td style="padding: 16px; text-transform: capitalize;">${req.data_type}</td>
            <td style="padding: 16px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${req.reason}">${req.reason}</td>
            <td style="padding: 16px;">${docLink}</td>
            <td style="padding: 16px;">${statusBadge}</td>
            <td style="padding: 16px; text-align: right;">
                ${req.status === 'pending' ? `<button onclick="openModal(${req.id})" class="ds-btn-primary" style="padding: 6px 12px; font-size: 13px;">Review</button>` : `<span class="text-caption text-gray-400">Reviewed</span>`}
            </td>
        </tr>
        `;
    });
    tbody.innerHTML = html;
}

function openModal(id) {
    const req = requestsData.find(r => r.id === id);
    if(!req) return;
    
    document.getElementById('reviewRequestId').value = id;
    document.getElementById('customDateStart').value = req.date_start;
    document.getElementById('customDateEnd').value = req.date_end;
    document.getElementById('customSensors').value = req.requested_sensors ? req.requested_sensors.join(', ') : 'all';
    
    document.getElementById('reviewModal').style.display = 'flex';
}

function downloadCustomCsv() {
    const start = document.getElementById('customDateStart').value;
    const end = document.getElementById('customDateEnd').value;
    let sensorsStr = document.getElementById('customSensors').value;
    
    // Parse sensors into params
    let sensors = sensorsStr.split(',').map(s => s.trim()).filter(s => s);
    let params = new URLSearchParams();
    params.append('date_start', start);
    params.append('date_end', end);
    sensors.forEach(s => params.append('sensors', s));
    
    window.open(`http://localhost:8000/api/data-requests/custom-download?${params.toString()}`, '_blank');
}

function closeModal() {
    document.getElementById('reviewModal').style.display = 'none';
    document.getElementById('reviewNotes').value = '';
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
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: status, admin_notes: notes })
        });

        if(res.ok) {
            closeModal();
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

document.addEventListener('DOMContentLoaded', loadRequests);
</script>
