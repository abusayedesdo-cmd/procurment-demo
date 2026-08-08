/**
 * Super Admin > User Management.
 * Talks to /api/admin/users/* (see routes/api.php, role:admin only).
 * Relies on window.currentUserId, window.ADMIN_ROLES,
 * window.ADMIN_ROLE_LABELS being set by resources/views/admin/users.blade.php.
 */
(function () {
    const roles = window.ADMIN_ROLES || [];
    const roleLabels = window.ADMIN_ROLE_LABELS || {};
    const currentUserId = window.currentUserId;

    const els = {
        tbody: document.getElementById('userTableBody'),
        errorBox: document.getElementById('errorBox'),
        noticeBox: document.getElementById('noticeBox'),
        search: document.getElementById('searchInput'),
        roleFilter: document.getElementById('roleFilter'),
        statusFilter: document.getElementById('statusFilter'),
        statTotal: document.getElementById('statTotal'),
        statActive: document.getElementById('statActive'),
        statInactive: document.getElementById('statInactive'),
        statAdmins: document.getElementById('statAdmins'),
        btnNewUser: document.getElementById('btnNewUser'),
        modalBackdrop: document.getElementById('userModalBackdrop'),
        modalTitle: document.getElementById('modalTitle'),
        modalSub: document.getElementById('modalSub'),
        modalError: document.getElementById('modalError'),
        form: document.getElementById('userForm'),
        btnCancel: document.getElementById('btnCancel'),
        f_name: document.getElementById('f_name'),
        f_email: document.getElementById('f_email'),
        f_role: document.getElementById('f_role'),
        f_phone: document.getElementById('f_phone'),
        f_designation: document.getElementById('f_designation'),
        f_password: document.getElementById('f_password'),
        f_is_active: document.getElementById('f_is_active'),
        passwordField: document.getElementById('passwordField'),
        passwordHint: document.getElementById('passwordHint'),
    };

    let editingUser = null; // null = create mode, otherwise the user object being edited
    let searchDebounce = null;

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

    // ---- Populate role dropdowns (filter + form) ----
    function populateRoleSelects() {
        const filterOpts = roles.map(r => `<option value="${r.id}">${roleLabel(r.name)}</option>`).join('');
        els.roleFilter.insertAdjacentHTML('beforeend', filterOpts);

        els.f_role.innerHTML = roles.map(r => `<option value="${r.id}">${roleLabel(r.name)}</option>`).join('');
    }

    // ---- List loading ----
    function buildQuery() {
        const params = new URLSearchParams();
        params.set('per_page', 100);
        if (els.search.value.trim()) params.set('q', els.search.value.trim());
        if (els.roleFilter.value) params.set('role_id', els.roleFilter.value);
        if (els.statusFilter.value) params.set('status', els.statusFilter.value);
        return params.toString();
    }

    async function loadUsers() {
        els.tbody.innerHTML = '<tr><td colspan="6" class="muted">Loading…</td></tr>';
        try {
            const { data, stats } = await api.get(`/admin/users?${buildQuery()}`);
            renderStats(stats);
            renderRows(data);
        } catch (err) {
            els.tbody.innerHTML = '<tr><td colspan="6" class="muted">Failed to load users.</td></tr>';
            showError(err.message || String(err));
        }
    }

    function renderStats(stats) {
        if (!stats) return;
        els.statTotal.textContent = stats.total;
        els.statActive.textContent = stats.active;
        els.statInactive.textContent = stats.inactive;
        els.statAdmins.textContent = stats.admins;
    }

    function renderRows(users) {
        if (!users.length) {
            els.tbody.innerHTML = '<tr><td colspan="6" class="muted">No users match your filters.</td></tr>';
            return;
        }

        els.tbody.innerHTML = users.map(u => {
            const roleName = u.role ? u.role.name : null;
            const isSelf = u.id === currentUserId;
            const statusBadge = u.is_active
                ? '<span class="badge status-active">Active</span>'
                : '<span class="badge status-inactive">Inactive</span>';
            const roleBadge = `<span class="badge role-${roleName || 'none'}">${roleLabel(roleName)}</span>`;

            return `
                <tr data-id="${u.id}" data-active="${u.is_active ? '1' : '0'}">
                    <td class="name-cell">
                        <div class="who">${escapeHtml(u.name)}${isSelf ? ' <span class="muted">(you)</span>' : ''}</div>
                        <div class="email">${escapeHtml(u.email)}</div>
                    </td>
                    <td>${roleBadge}</td>
                    <td>${escapeHtml(u.phone || '—')}</td>
                    <td>${escapeHtml(u.designation || '—')}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="row-actions">
                            <button class="btn outline sm" data-action="edit">Edit</button>
                            <button class="btn outline sm" data-action="reset-password">Reset PW</button>
                            <button class="btn outline sm" data-action="toggle" ${isSelf ? 'disabled title="You can\'t deactivate your own account"' : ''}>${u.is_active ? 'Deactivate' : 'Reactivate'}</button>
                            <button class="btn danger sm" data-action="delete" ${isSelf ? 'disabled title="You can\'t delete your own account"' : ''}>Delete</button>
                        </div>
                    </td>
                </tr>`;
        }).join('');
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    // ---- Modal open/close ----
    function openCreateModal() {
        editingUser = null;
        els.modalTitle.textContent = 'New User';
        els.modalSub.textContent = 'Create a login for a staff member and assign their role.';
        els.form.reset();
        els.f_is_active.checked = true;
        els.passwordField.style.display = '';
        els.f_password.required = true;
        els.passwordHint.textContent = 'Minimum 8 characters. Shown as plain text so you can share it with the user.';
        els.modalError.style.display = 'none';
        els.modalBackdrop.classList.add('open');
        els.f_name.focus();
    }

    function openEditModal(user) {
        editingUser = user;
        els.modalTitle.textContent = 'Edit User';
        els.modalSub.textContent = `Editing ${user.name}. Use "Reset PW" from the list to change their password.`;
        els.f_name.value = user.name;
        els.f_email.value = user.email;
        els.f_role.value = user.role_id;
        els.f_phone.value = user.phone || '';
        els.f_designation.value = user.designation || '';
        els.f_is_active.checked = !!user.is_active;
        els.f_password.value = '';
        els.f_password.required = false;
        els.passwordField.style.display = 'none';
        els.modalError.style.display = 'none';
        els.modalBackdrop.classList.add('open');
        els.f_name.focus();
    }

    function closeModal() {
        els.modalBackdrop.classList.remove('open');
        editingUser = null;
    }

    // ---- Form submit (create or update) ----
    async function handleFormSubmit(e) {
        e.preventDefault();
        els.modalError.style.display = 'none';

        const payload = {
            name: els.f_name.value.trim(),
            email: els.f_email.value.trim(),
            role_id: parseInt(els.f_role.value, 10),
            phone: els.f_phone.value.trim() || null,
            designation: els.f_designation.value.trim() || null,
            is_active: els.f_is_active.checked,
        };

        if (!editingUser && els.f_password.value.trim()) {
            payload.password = els.f_password.value.trim();
        }

        try {
            if (editingUser) {
                await api.put(`/admin/users/${editingUser.id}`, payload);
                showNotice(`${payload.name} was updated.`);
            } else {
                if (!payload.password) {
                    els.modalError.textContent = 'Password is required for a new user.';
                    els.modalError.style.display = 'block';
                    return;
                }
                await api.post('/admin/users', payload);
                showNotice(`${payload.name} was created.`);
            }
            closeModal();
            await loadUsers();
        } catch (err) {
            els.modalError.textContent = err.message || String(err);
            els.modalError.style.display = 'block';
        }
    }

    // ---- Row actions ----
    async function handleTableClick(e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn || btn.disabled) return;

        const tr = btn.closest('tr');
        const id = tr.dataset.id;
        const action = btn.dataset.action;

        clearMessages();

        try {
            if (action === 'edit') {
                const { data } = await api.get(`/admin/users/${id}`);
                openEditModal(data);
                return;
            }

            if (action === 'toggle') {
                const nowActive = tr.dataset.active !== '1';
                await api.put(`/admin/users/${id}`, { is_active: nowActive });
                showNotice(`Status updated.`);
                await loadUsers();
                return;
            }

            if (action === 'reset-password') {
                const custom = prompt('Enter a new password (min 8 characters), or leave blank to auto-generate one:');
                if (custom === null) return; // cancelled
                const body = custom.trim() ? { password: custom.trim() } : {};
                const { data } = await api.post(`/admin/users/${id}/reset-password`, body);
                if (data && data.generated_password) {
                    alert(`New password set:\n\n${data.generated_password}\n\nShare this with the user securely — it will not be shown again.`);
                } else {
                    showNotice('Password reset successfully.');
                }
                return;
            }

            if (action === 'delete') {
                const who = tr.querySelector('.who').textContent;
                if (!confirm(`Delete ${who}? This cannot be undone. (Users with procurement history are deactivated instead of deleted.)`)) return;
                const { message } = await api.del(`/admin/users/${id}`);
                showNotice(message || 'User removed.');
                await loadUsers();
                return;
            }
        } catch (err) {
            showError(err.message || String(err));
        }
    }

    // ---- Wire up ----
    populateRoleSelects();
    els.btnNewUser.addEventListener('click', openCreateModal);
    els.btnCancel.addEventListener('click', closeModal);
    els.modalBackdrop.addEventListener('click', (e) => { if (e.target === els.modalBackdrop) closeModal(); });
    els.form.addEventListener('submit', handleFormSubmit);
    els.tbody.addEventListener('click', handleTableClick);
    els.roleFilter.addEventListener('change', loadUsers);
    els.statusFilter.addEventListener('change', loadUsers);
    els.search.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(loadUsers, 300);
    });

    loadUsers();
})();
