/**
 * Super Admin > Project Management.
 * Talks to /api/projects/* (read open, admin-only writes) and reuses
 * /api/admin/users/* for assigning/creating team members.
 * Relies on window.ADMIN_ROLES / window.ADMIN_ROLE_LABELS being set by
 * resources/views/admin/projects.blade.php.
 */
(function () {
    const roles = window.ADMIN_ROLES || [];
    const roleLabels = window.ADMIN_ROLE_LABELS || {};

    const els = {
        tbody: document.getElementById('projectTableBody'),
        errorBox: document.getElementById('errorBox'),
        noticeBox: document.getElementById('noticeBox'),
        btnNewProject: document.getElementById('btnNewProject'),

        projectModalBackdrop: document.getElementById('projectModalBackdrop'),
        projectModalError: document.getElementById('projectModalError'),
        projectForm: document.getElementById('projectForm'),
        btnCancelProject: document.getElementById('btnCancelProject'),
        p_name: document.getElementById('p_name'),
        p_code: document.getElementById('p_code'),
        p_description: document.getElementById('p_description'),
        p_is_active: document.getElementById('p_is_active'),

        teamModalBackdrop: document.getElementById('teamModalBackdrop'),
        teamProjectName: document.getElementById('teamProjectName'),
        teamModalError: document.getElementById('teamModalError'),
        teamModalNotice: document.getElementById('teamModalNotice'),
        currentTeamList: document.getElementById('currentTeamList'),
        existingUserChecklist: document.getElementById('existingUserChecklist'),
        btnAssignExisting: document.getElementById('btnAssignExisting'),
        newUserRows: document.getElementById('newUserRows'),
        btnAddUserRow: document.getElementById('btnAddUserRow'),
        btnCloseTeam: document.getElementById('btnCloseTeam'),
    };

    let currentTeamProject = null; // the project object the Team modal is open for

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function roleLabel(name) {
        return roleLabels[name] || name || '—';
    }

    function showError(msg) {
        els.errorBox.textContent = msg;
        els.errorBox.style.display = 'block';
        els.noticeBox.style.display = 'none';
    }

    function showNotice(msg) {
        els.noticeBox.textContent = msg;
        els.noticeBox.style.display = 'block';
        els.errorBox.style.display = 'none';
    }

    function clearMessages() {
        els.errorBox.style.display = 'none';
        els.noticeBox.style.display = 'none';
    }

    // ---- Project list ----
    async function loadProjects() {
        els.tbody.innerHTML = '<tr><td colspan="6" class="muted">Loading…</td></tr>';
        try {
            const { data } = await api.get('/projects?per_page=100');
            renderRows(data);
        } catch (err) {
            els.tbody.innerHTML = `<tr><td colspan="6" class="muted">Failed to load: ${escapeHtml(err.message)}</td></tr>`;
        }
    }

    function renderRows(projects) {
        if (!projects.length) {
            els.tbody.innerHTML = '<tr><td colspan="6" class="muted">No projects yet — create one to get started.</td></tr>';
            return;
        }

        els.tbody.innerHTML = projects.map(p => {
            const subCommittee = (p.committees || [])[0];
            return `
                <tr>
                    <td><strong>${escapeHtml(p.name)}</strong>${p.description ? `<div class="muted" style="font-size:.78rem;">${escapeHtml(p.description)}</div>` : ''}</td>
                    <td>${escapeHtml(p.code) || '—'}</td>
                    <td>${subCommittee ? `<span class="badge committee">${escapeHtml(subCommittee.name)}</span>` : '<span class="muted">—</span>'}</td>
                    <td>${p.users_count ?? 0} ${p.users_count === 1 ? 'member' : 'members'}</td>
                    <td><span class="badge ${p.is_active ? 'status-active' : 'status-inactive'}">${p.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td class="row-actions">
                        <button class="btn sm outline" onclick="AdminProjects.manageTeam(${p.id})">Manage Team</button>
                        <button class="btn sm outline" onclick="AdminProjects.toggleActive(${p.id}, ${p.is_active ? 'false' : 'true'})">${p.is_active ? 'Deactivate' : 'Activate'}</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function toggleActive(id, nextActive) {
        clearMessages();
        try {
            await api.put(`/projects/${id}`, { is_active: nextActive });
            showNotice('Project updated.');
            await loadProjects();
        } catch (err) {
            showError(err.message);
        }
    }

    // ---- New Project modal ----
    function openProjectModal() {
        els.projectForm.reset();
        els.p_is_active.checked = true;
        els.projectModalError.style.display = 'none';
        els.projectModalBackdrop.classList.add('open');
    }

    function closeProjectModal() {
        els.projectModalBackdrop.classList.remove('open');
    }

    async function submitProject(e) {
        e.preventDefault();
        els.projectModalError.style.display = 'none';

        const payload = {
            name: els.p_name.value.trim(),
            code: els.p_code.value.trim() || null,
            description: els.p_description.value.trim() || null,
            is_active: els.p_is_active.checked,
        };

        try {
            const { data, message } = await api.post('/projects', payload);
            closeProjectModal();
            showNotice(message || 'Project created.');
            await loadProjects();
            // Straight into team assignment — the whole point of naming a
            // project is to say who's on it.
            manageTeam(data.id);
        } catch (err) {
            els.projectModalError.textContent = err.message;
            els.projectModalError.style.display = 'block';
        }
    }

    // ---- Team modal ----
    async function manageTeam(projectId) {
        clearMessages();
        els.teamModalError.style.display = 'none';
        els.teamModalNotice.style.display = 'none';
        els.currentTeamList.innerHTML = '<span class="muted" style="font-size:.82rem;">Loading…</span>';
        els.existingUserChecklist.innerHTML = '<span class="empty">Loading…</span>';
        els.newUserRows.innerHTML = '';
        addUserRow();

        els.teamModalBackdrop.classList.add('open');

        try {
            const [{ data: project }, { data: allUsers }] = await Promise.all([
                api.get(`/projects/${projectId}`),
                api.get('/admin/users?per_page=200'),
            ]);

            currentTeamProject = project;
            els.teamProjectName.textContent = project.name;

            renderCurrentTeam(project.users || []);
            renderExistingUserChecklist(allUsers, project);
        } catch (err) {
            els.teamModalError.textContent = err.message;
            els.teamModalError.style.display = 'block';
        }
    }

    function renderCurrentTeam(users) {
        if (!users.length) {
            els.currentTeamList.innerHTML = '<span class="muted" style="font-size:.82rem;">No one assigned yet.</span>';
            return;
        }
        els.currentTeamList.innerHTML = users.map(u => `<span class="chip">${escapeHtml(u.name)}</span>`).join('');
    }

    function renderExistingUserChecklist(allUsers, project) {
        // Exclude users already on this project; note where the rest
        // currently sit, since picking one here moves them (a user
        // belongs to exactly one project at a time).
        const candidates = allUsers.filter(u => u.project_id !== project.id);

        if (!candidates.length) {
            els.existingUserChecklist.innerHTML = '<span class="empty">No other users to assign.</span>';
            return;
        }

        els.existingUserChecklist.innerHTML = candidates.map(u => `
            <label>
                <input type="checkbox" value="${u.id}">
                ${escapeHtml(u.name)} <span class="email">${escapeHtml(u.email)}${u.project ? ` — currently: ${escapeHtml(u.project.name)}` : ''}</span>
            </label>
        `).join('');
    }

    async function assignExisting() {
        if (!currentTeamProject) return;
        const checked = [...els.existingUserChecklist.querySelectorAll('input[type="checkbox"]:checked')].map(cb => cb.value);

        if (!checked.length) {
            els.teamModalError.textContent = 'Select at least one user to assign.';
            els.teamModalError.style.display = 'block';
            return;
        }

        els.teamModalError.style.display = 'none';
        try {
            for (const userId of checked) {
                await api.put(`/admin/users/${userId}`, { project_id: currentTeamProject.id });
            }
            els.teamModalNotice.textContent = `${checked.length} user(s) assigned to ${currentTeamProject.name}.`;
            els.teamModalNotice.style.display = 'block';
            await manageTeam(currentTeamProject.id);
            await loadProjects();
        } catch (err) {
            els.teamModalError.textContent = err.message;
            els.teamModalError.style.display = 'block';
        }
    }

    // ---- New user rows (inline, inside the Team modal) ----
    function addUserRow() {
        const row = document.createElement('div');
        row.className = 'new-user-row';
        const roleOpts = roles.map(r => `<option value="${r.id}">${roleLabel(r.name)}</option>`).join('');
        row.innerHTML = `
            <input type="text" placeholder="Full name" class="nu_name">
            <input type="email" placeholder="Email" class="nu_email">
            <select class="nu_role">${roleOpts}</select>
            <input type="text" placeholder="Phone (optional)" class="nu_phone">
        `;
        els.newUserRows.appendChild(row);
    }

    function generatePassword() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        return Array.from({ length: 10 }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
    }

    async function createNewUsers() {
        if (!currentTeamProject) return;
        const rows = [...els.newUserRows.querySelectorAll('.new-user-row')];
        const created = [];
        els.teamModalError.style.display = 'none';

        for (const row of rows) {
            const name = row.querySelector('.nu_name').value.trim();
            const email = row.querySelector('.nu_email').value.trim();
            if (!name || !email) continue; // skip untouched rows

            const password = generatePassword();
            try {
                await api.post('/admin/users', {
                    name,
                    email,
                    password,
                    role_id: Number(row.querySelector('.nu_role').value),
                    project_id: currentTeamProject.id,
                    phone: row.querySelector('.nu_phone').value.trim() || null,
                    is_active: true,
                });
                created.push(`${name} (${email}) — password: ${password}`);
            } catch (err) {
                els.teamModalError.textContent = `Couldn't create ${name}: ${err.message}`;
                els.teamModalError.style.display = 'block';
                return created; // stop on first failure, keep what worked
            }
        }

        if (created.length) {
            els.teamModalNotice.innerHTML = 'New accounts created — share these once:<br>' + created.map(escapeHtml).join('<br>');
            els.teamModalNotice.style.display = 'block';
            els.newUserRows.innerHTML = '';
            addUserRow();
            await manageTeam(currentTeamProject.id);
            await loadProjects();
        }
        return created;
    }

    function closeTeamModal() {
        els.teamModalBackdrop.classList.remove('open');
        currentTeamProject = null;
    }

    // ---- Wire up ----
    els.btnNewProject.addEventListener('click', openProjectModal);
    els.btnCancelProject.addEventListener('click', closeProjectModal);
    els.projectModalBackdrop.addEventListener('click', e => { if (e.target === els.projectModalBackdrop) closeProjectModal(); });
    els.projectForm.addEventListener('submit', submitProject);

    els.btnAssignExisting.addEventListener('click', assignExisting);
    els.btnAddUserRow.addEventListener('click', addUserRow);
    els.btnCloseTeam.addEventListener('click', async () => {
        await createNewUsers();
        if (els.teamModalError.style.display === 'block') return; // keep it open so they can see/fix the error
        closeTeamModal();
        await loadProjects();
    });
    els.teamModalBackdrop.addEventListener('click', e => { if (e.target === els.teamModalBackdrop) closeTeamModal(); });

    window.AdminProjects = { manageTeam, toggleActive };

    loadProjects();
})();