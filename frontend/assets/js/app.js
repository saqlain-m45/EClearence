/**
 * University E-Clearance System
 * Vanilla JS + Bootstrap 5 + MediQu Theme
 */

const API_URL = '../backend/api';
const app = document.getElementById('app');

const state = {
    user: null,
    token: null
};

// --- Utils ---
function showToast(message, type = 'success') {
    const toastEl = document.getElementById('liveToast');
    const toastBody = document.getElementById('toastMessage');

    if (!toastEl) return;
    toastBody.textContent = message;
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

async function apiCall(endpoint, method = 'GET', body = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin' // Ensure cookies are sent
    };
    if (body) options.body = JSON.stringify(body);

    try {
        const res = await fetch(`${API_URL}/${endpoint}`, options);
        if (!res.ok) {
            console.warn(`API responded with status ${res.status} for ${endpoint}`);
            // Still try to parse JSON error message if possible
            try {
                const errData = await res.json();
                return errData;
            } catch (e) {
                return { status: 'error', message: `Server error: ${res.status}` };
            }
        }
        return await res.json();
    } catch (err) {
        console.error('API Error:', err);
        return { status: 'error', message: 'Connection failed' };
    }
}

// --- Views ---

function renderLogin() {
    document.getElementById('sidebar-wrapper').style.display = 'none';
    document.querySelector('.navbar').style.display = 'none';

    app.innerHTML = `
        <div class="row justify-content-center mt-5 fade-in">
            <div class="col-md-5">
                <div class="card shadow-lg border-0 rounded-3 login-card">
                    <div class="card-header bg-white text-center py-4 border-0">
                        <i class="bi bi-mortarboard-fill display-4 text-primary mb-2"></i>
                        <h3 class="fw-bold text-primary">University Portal</h3>
                        <p class="text-muted mb-0">E-Clearance System Login</p>
                    </div>
                    <div class="card-body p-4">
                        <form id="loginForm">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" placeholder="name@example.com" required>
                                <label for="email">Email Address</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" placeholder="Password" required>
                                <label for="password">Password</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-lg shadow-sm mb-3">Sign In</button>
                            <div class="text-center">
                                <a href="#" class="text-decoration-none" onclick="renderRegister()">New Student? Register Here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const res = await apiCall('auth.php?action=login', 'POST', { email, password });
        if (res && res.status === 'success') {
            state.user = res.user;
            renderDashboard();
        } else {
            showToast(res ? res.message : 'Login failed', 'error');
        }
    });
}

function renderRegister() {
    app.innerHTML = `
        <div class="row justify-content-center fade-in py-4">
            <div class="col-md-8">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0">Student Registration</h4>
                    </div>
                    <div class="card-body p-4">
                        <form id="registerForm" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Full Name</label><input type="text" class="form-control" name="name" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Father's Name</label><input type="text" class="form-control" name="father_name" required></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label>CNIC</label><input type="text" class="form-control" name="cnic" required></div>
                                <div class="col-md-6 mb-3"><label>DOB</label><input type="date" class="form-control" name="dob" required></div>
                            </div>
                             <div class="row">
                                <div class="col-md-6 mb-3"><label>Reg No</label><input type="text" class="form-control" name="reg_no" required></div>
                                <div class="col-md-6 mb-3"><label>Discipline</label><input type="text" class="form-control" name="discipline" required></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label>Hostel Name (if any)</label><input type="text" class="form-control" name="hostel_name"></div>
                                <div class="col-md-6 mb-3"><label>Fee Slip ID</label><input type="text" class="form-control" name="fee_slip_id" required></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label>Semester</label><select class="form-select" name="semester"><option>8th</option></select></div>
                                <div class="col-md-6 mb-3"><label>Profile Pic</label><input type="file" class="form-control" name="profile_pic" required></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label>Email</label><input type="email" class="form-control" name="email" required></div>
                                <div class="col-md-6 mb-3"><label>Password</label><input type="password" class="form-control" name="password" required></div>
                            </div>
                            <div class="d-grid gap-2 mt-3">
                                <button type="submit" class="btn btn-success btn-lg">Register</button>
                                <button type="button" class="btn btn-link" onclick="renderLogin()">Back to Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const res = await fetch(`${API_URL}/register.php`, { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'success') { showToast('Registered!'); renderLogin(); }
            else { showToast(data.message, 'error'); }
        } catch (err) { showToast('Error', 'error'); }
    });
}

function renderDashboard() {
    if (!state.user) { renderLogin(); return; }

    document.getElementById('sidebar-wrapper').style.display = 'block';
    document.querySelector('.navbar').style.display = 'flex';
    document.getElementById('menu-toggle').style.display = 'block';
    document.getElementById('nav-username').textContent = state.user.name;

    if (state.user.role === 'student') renderStudentDashboard();
    else if (state.user.role === 'department') renderDepartmentDashboard();
    else if (state.user.role === 'admin') renderAdminDashboard();
}

function renderStatCard(title, value, icon, colorClass) {
    return `
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stat-icon ${colorClass}"><i class="bi ${icon}"></i></div>
                        <div class="stat-value">${value}</div>
                        <div class="stat-label">${title}</div>
                    </div>
                </div>
                <div class="mt-3" style="height:4px; background:#f0f0f0; border-radius:10px; overflow:hidden;">
                    <div style="width:70%; height:100%; background:currentColor; opacity:0.5"></div>
                </div>
            </div>
        </div>
    `;
}

// --- Student Dashboard ---
async function renderStudentDashboard() {
    document.getElementById('page-title').textContent = 'My Dashboard';

    const [profileRes, statusRes] = await Promise.all([
        apiCall(`student.php?action=profile&id=${state.user.id}`),
        apiCall(`student.php?action=status&id=${state.user.id}`)
    ]);

    if (profileRes.status !== 'success') {
        showToast(profileRes.message || 'Failed to load profile', 'error');
    }

    const student = profileRes.data || {};
    const totalSteps = (statusRes.data && statusRes.data.steps) ? statusRes.data.steps.length : 0;
    const completed = (statusRes.data && statusRes.data.steps) ? statusRes.data.steps.filter(s => s.status === 'approved').length : 0;
    const imgUrl = student.profile_image_path ? `http://localhost/EClearence/frontend/${student.profile_image_path}` : 'assets/img/default.png';

    app.innerHTML = `
        <div class="row mb-4">
            ${renderStatCard('Clearance Progress', `${Math.round((completed / totalSteps) * 100 || 0)}%`, 'bi-graph-up-arrow', 'icon-purple')}
            ${renderStatCard('Pending Steps', totalSteps - completed, 'bi-hourglass-split', 'icon-orange')}
            ${renderStatCard('Approved Steps', completed, 'bi-check2-circle', 'icon-blue')}
            ${renderStatCard('Issues', 0, 'bi-exclamation-triangle', 'icon-red')}
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold">Clearance Status</h5></div>
                    <div class="card-body">
                         ${renderClearanceContent(statusRes, student)}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 text-center p-4">
                     <img src="${imgUrl}" class="rounded-circle mx-auto mb-3 border border-3 border-light shadow-sm" style="width:120px;height:120px;object-fit:cover;">
                     <h5 class="fw-bold">${state.user.name}</h5>
                     <p class="text-muted">${student.reg_no}</p>
                     <div class="bg-light p-3 rounded text-start small">
                        <div><strong>Father's Name:</strong> ${student.father_name || '-'}</div>
                        <div><strong>CNIC:</strong> ${student.cnic || '-'}</div>
                        <div><strong>Dept:</strong> ${student.discipline || '-'}</div>
                        <div><strong>Hostel:</strong> ${student.hostel_name || 'Day Scholar'}</div>
                        <div><strong>Fee Slip:</strong> ${student.fee_slip_id || '-'}</div>
                     </div>
                </div>
            </div>
        </div>
    `;
    setupApplyListener();
}

// --- Department Dashboard ---
async function renderDepartmentDashboard() {
    if (!state.user.department || !state.user.department.id) {
        showToast('Department details missing', 'error');
        return;
    }

    document.getElementById('page-title').textContent = `${state.user.department.name} Dashboard`;

    const [pendingRes, historyRes] = await Promise.all([
        apiCall(`department.php?action=pending&id=${state.user.department.id}`),
        apiCall(`department.php?action=history&id=${state.user.department.id}`)
    ]);

    const pending = pendingRes.status === 'success' ? (pendingRes.data || []) : [];
    const history = historyRes.status === 'success' ? (historyRes.data || []) : [];

    app.innerHTML = `
        <div class="row mb-4">
            ${renderStatCard('Pending Requests', pending.length, 'bi-clock-history', 'icon-orange')}
            ${renderStatCard('Processed Today', history.length, 'bi-check2-all', 'icon-blue')}
            ${renderStatCard('Total Processed', history.length + 50, 'bi-archive', 'icon-purple')}
        </div>

        <ul class="nav nav-tabs mb-4" id="deptTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button">Pending Requests</button></li>
            <li class="nav-item"><button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button">All Records</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="pending">
                 <div class="row">
                    ${pending.length === 0 ? '<p class="text-muted p-4">No pending requests.</p>' : pending.map(req => `
                        <div class="col-md-6 mb-3">
                            <div class="card shadow-sm border-0 p-3">
                                <div class="d-flex align-items-start gap-3">
                                    <img src="${req.profile_image_path ? '../' + req.profile_image_path : 'assets/img/default.png'}" class="rounded-circle" style="width:50px;height:50px;object-fit:cover;">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="fw-bold mb-0">${req.student_name} <small class="text-muted">(${req.reg_no})</small></h5>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        </div>
                                        <div class="small text-muted mb-2">
                                            <div><strong>Dept:</strong> ${req.discipline} | <strong>Sem:</strong> ${req.semester}</div>
                                            <div><strong>Father:</strong> ${req.father_name} | <strong>CNIC:</strong> ${req.cnic || '-'}</div>
                                            <div><strong>Hostel:</strong> ${req.hostel_name || 'Day Scholar'}</div>
                                            <div><strong>Fee Slip:</strong> ${req.fee_slip_id || '-'}</div>
                                            <div class="mt-1 text-primary fw-bold">Purpose: ${req.purpose.replace(/_/g, ' ').toUpperCase()}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <button class="btn btn-success btn-sm flex-grow-1" onclick="updateRequest(${req.step_id}, 'approved', ${state.user.id})">Approve</button>
                                    <button class="btn btn-outline-danger btn-sm flex-grow-1" onclick="rejectRequest(${req.step_id}, ${state.user.id})">Reject</button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                 </div>
            </div>
            <div class="tab-pane fade" id="history">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>Student</th><th>Date</th><th>Status</th><th>Remarks</th></tr></thead>
                            <tbody>
                                ${history.map(h => `
                                    <tr>
                                        <td>${h.student_name}<br><small class="text-muted">${h.reg_no}</small></td>
                                        <td>${h.updated_at || '-'}</td>
                                        <td><span class="badge bg-${h.status === 'approved' ? 'success' : 'danger'}">${h.status}</span></td>
                                        <td>${h.remarks || '-'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// --- Admin Dashboard ---
async function renderAdminDashboard() {
    document.getElementById('page-title').textContent = 'Admin Overview';
    const usersRes = await apiCall('admin.php?action=users');
    const users = usersRes.data || [];

    app.innerHTML = `
        <div class="row mb-4">
             ${renderStatCard('Total Users', users.length, 'bi-people', 'icon-blue')}
             ${renderStatCard('Departments', 12, 'bi-building', 'icon-purple')}
             <div class="col-md-6">
                <div class="card shadow-sm border-0 stat-card h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3"><i class="bi bi-shield-check text-success me-2"></i>Verify Certificate</h5>
                        <div class="input-group">
                            <input type="text" id="verifyInput" class="form-control" placeholder="Enter Certificate Code (e.g. CRT-2026-...)">
                            <button class="btn btn-primary" onclick="verifyCertificate()">Verify</button>
                        </div>
                        <div id="verifyResult" class="mt-3"></div>
                    </div>
                </div>
             </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3"><h5 class="mb-0">User Management</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Role</th><th>Dept</th><th>Action</th></tr></thead>
                    <tbody>
                        ${users.map(u => `
                            <tr>
                                <td>${u.id}</td>
                                <td><div class="fw-bold">${u.name}</div><small class="text-muted">${u.email}</small></td>
                                <td><span class="badge bg-secondary">${u.role}</span></td>
                                <td>${u.department_name || '-'}</td>
                                <td><button class="btn btn-sm btn-light"><i class="bi bi-three-dots"></i></button></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

// --- Helpers & Global functions ---

window.verifyCertificate = async () => {
    const code = document.getElementById('verifyInput').value;
    if (!code) return;

    const res = await apiCall('admin.php?action=verify', 'POST', { code });
    const resDiv = document.getElementById('verifyResult');

    if (res.status === 'success') {
        resDiv.innerHTML = `
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-patch-check-fill fs-3 me-3"></i>
                <div>
                    <strong>Verified Successfully!</strong><br>
                    Student: ${res.data.name}<br>
                    Reg No: ${res.data.reg_no}<br>
                    Date: ${res.data.completed_date}
                </div>
            </div>
        `;
    } else {
        resDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>${res.message}</div>`;
    }
};

window.updateRequest = async (stepId, status, userId) => {
    const res = await apiCall('department.php?action=update', 'POST', { step_id: stepId, status, user_id: userId });
    if (res.status === 'success') { showToast('Updated!'); renderDashboard(); }
};

window.rejectRequest = async (stepId, userId) => {
    const r = prompt("Reason:");
    if (r) {
        const res = await apiCall('department.php?action=update', 'POST', { step_id: stepId, status: 'rejected', remarks: r, user_id: userId });
        renderDashboard();
    }
}

function renderClearanceContent(res, student) {
    if (!res || !res.data) return `<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyModal">Start Clearance</button>
    <div class="modal fade" id="applyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">New Request</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form id="applyForm">
                         <div class="mb-3">
                             <label>Purpose</label>
                             <select id="purposeSelect" class="form-select">
                                <option value="degree">Degree</option>
                                <option value="provisional_certificate">Provisional Certificate</option>
                                <option value="transcript">Transcript</option>
                                <option value="admission_cancellation">Admission / Hostel Cancellation</option>
                                <option value="synopsis_submission">Synopsis Submission</option>
                                <option value="thesis_submission">Thesis Submission</option>
                             </select>
                         </div>
                         <button type="submit" class="btn btn-primary w-100">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>`;

    const { request, steps } = res.data;
    const allApproved = request.status === 'completed';

    return `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="text-${allApproved ? 'success' : 'warning'} mb-0">Status: ${request.status.toUpperCase()}</h5>
                <small class="text-muted">Applying for: <strong>${request.purpose.replace(/_/g, ' ').toUpperCase()}</strong></small>
            </div>
            ${allApproved ? `<a href="http://localhost/EClearence/backend/api/certificate.php?id=${request.id}" target="_blank" class="btn btn-success"><i class="bi bi-download me-2"></i>Download Certificate</a>` : ''}
        </div>
        <div class="list-group list-group-flush border rounded">
            ${steps.map((s, index) => `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-light text-dark me-2 border rounded-circle" style="width:25px;height:25px;line-height:18px;">${index + 1}</span>
                        <strong>${s.dept_name}</strong>
                        ${s.remarks ? `<br><small class="text-danger ms-4"><i class="bi bi-info-circle me-1"></i>${s.remarks}</small>` : ''}
                    </div>
                    <span class="badge bg-${s.status === 'approved' ? 'success' : (s.status === 'rejected' ? 'danger' : 'secondary')} rounded-pill">${s.status.toUpperCase()}</span>
                </div>
            `).join('')}
        </div>
    `;
}

function setupApplyListener() {
    const form = document.getElementById('applyForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const p = document.getElementById('purposeSelect').value;
            const res = await apiCall('student.php?action=submit', 'POST', { student_id: state.user.id, purpose: p });
            if (res.status === 'success') { const m = bootstrap.Modal.getInstance(document.getElementById('applyModal')); m.hide(); renderDashboard(); }
        });
    }
}

window.logout = async () => {
    await apiCall('auth.php?action=logout');
    state.user = null;
    renderLogin();
}

// Init
(async () => {
    const res = await apiCall('auth.php?action=check');
    if (res && res.status === 'success') { state.user = res.user; renderDashboard(); } else { renderLogin(); }
})();
