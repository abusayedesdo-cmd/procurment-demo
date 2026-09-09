/**
 * Super Admin > Committee Management.
 * Talks to /api/purchase-committees/*, /api/committee-members/*, /api/projects
 * and /api/admin/users/* (for creating brand-new accounts inline).
 * Relies on window.ADMIN_ROLES / window.ADMIN_ROLE_LABELS being set by
 * resources/views/admin/committees.blade.php.
 *
 * Policy §9 sizing: central/main committee = 5–7 members, sub-committee = 3–5.
 * Kept in sync with App\Http\Controllers\Api\CommitteeMemberController.
 */
(function () {
    const roles = window.ADMIN_ROLES || [];
    const roleLabels = window.ADMIN_ROLE_LABELS || {};
    const SIZE_RANGE = { main: [5, 7], sub: [3, 5] };

    const els = {
        tbody: document.getElementById('committeeTableBody'),
        errorBox: document.getElementById('errorBox'),
        noticeBox: document.getElementById('noticeBox'),
        btnNewCommittee: document.getElementById('btnNewCommittee'),

        committeeModalBackdrop: document.getElementById('committeeModalBackdrop'),
        committeeModalTitle: document.getElementById('committeeModalTitle'),
        committeeModalError: document.getElementById('committeeModalError'),
        committeeForm: document.getElementById('committeeForm'),
        btnCancelCommittee: document.getElementById('btnCancelCommittee'),
        c_id: document.getElementById('c_id'),
        c_name: document.getElementById('c_name'),
        c_type: document.getElementById('c_type'),
        c_project_field: document.getElementById('c_project_field'),
        c_project: document.getElementById('c_project'),
        c_parent: document.getElementById('c_parent'),
        c_address: document.getElementById('c_address'),

        membersModalBackdrop: document.getElementById('membersModalBackdrop'),
        membersCommitteeName: document.getElementById('membersCommitteeName'),
        membersCommitteeMeta: document.getElementById('membersCommitteeMeta'),
        membersModalError: document.getElementById('membersModalError'),
        membersModalNotice: document.getElementById('membersModalNotice'),
        currentMembersTable: document.getElementById('currentMembersTable'),
        copySourceCommittee: document.getElementById('copySourceCommittee'),
        btnCopyMembers: document.getElementById('btnCopyMembers'),
        rosterChecklist: document.getElementById('rosterChecklist'),
        btnAssignRoster: document.getElementById('btnAssignRoster'),
        newUserRows: document.getElementById('newUserRows'),
        btnAddUserRow: document.getElementById('btnAddUserRow'),
        btnCloseMembers: document.getElementById('btnCloseMembers'),
    };

    let committees = [];
    let projects = [];
    let currentCommittee = null; // committee object the Members modal is open for
    let rosterMembersCache = []; // last-fetched /procurement-committee-members list, for name lookup

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

    function sizeRange(type) {
        return SIZE_RANGE[type] || SIZE_RANGE.main;
    }

    // ---- Committee list ----
    async function loadAll() {
        els.tbody.innerHTML = '<tr><td colspan="6" class="muted">Loading…</td></tr>';
        try {
            const [{ data: committeeData }, { data: projectData }] = await Promise.all([
                api.get('/purchase-committees?per_page=100'),
                api.get('/projects?per_page=200'),
            ]);
            committees = committeeData;
            projects = projectData;
            renderRows();
            renderProjectOptions();
            renderParentOptions();
        } catch (err) {
            els.tbody.innerHTML = `<tr><td colspan="6" class="muted">Failed to load: ${escapeHtml(err.message)}</td></tr>`;
        }
    }

    function renderRows() {
        if (!committees.length) {
            els.tbody.innerHTML = '<tr><td colspan="6" class="muted">No committees yet — create one to get started.</td></tr>';
            return;
        }

        els.tbody.innerHTML = committees.map(c => {
            const [min, max] = sizeRange(c.type);
            const count = (c.members || []).length;
            const sizeOk = count >= min && count <= max;
            return `
                <tr>
                    <td><strong>${escapeHtml(c.name)}</strong>${c.address ? `<div class="muted" style="font-size:.78rem;">${escapeHtml(c.address)}</div>` : ''}</td>
                    <td><span class="badge type-${c.type}">${c.type === 'sub' ? 'Sub-committee' : 'Main / Central'}</span></td>
                    <td>${c.project ? escapeHtml(c.project.name) : '<span class="muted">—</span>'}</td>
                    <td>${c.parent_committee ? escapeHtml(c.parent_committee.name) : '<span class="muted">—</span>'}</td>
                    <td><span class="badge ${sizeOk ? 'size-ok' : 'size-warn'}">${count} of ${min}–${max}</span></td>
                    <td class="row-actions">
                        <button class="btn sm outline" onclick="AdminCommittees.manageMembers(${c.id})">Manage Members</button>
                        <button class="btn sm outline" onclick="AdminCommittees.editCommittee(${c.id})">Edit</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderProjectOptions() {
        els.c_project.innerHTML = '<option value="">—</option>' +
            projects.map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`).join('');
    }

    function renderParentOptions(excludeId) {
        const opts = committees.filter(c => c.id !== excludeId);
        els.c_parent.innerHTML = '<option value="">—</option>' +
            opts.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
    }

    function toggleProjectFieldVisibility() {
        els.c_project_field.style.display = els.c_type.value === 'sub' ? '' : 'none';
        if (els.c_type.value !== 'sub') els.c_project.value = '';
    }

    // ---- New/Edit Committee modal ----
    function openNewCommitteeModal() {
        els.committeeForm.reset();
        els.c_id.value = '';
        els.committeeModalTitle.textContent = 'New Committee';
        els.committeeModalError.style.display = 'none';
        renderParentOptions();
        toggleProjectFieldVisibility();
        els.committeeModalBackdrop.classList.add('open');
    }

    function editCommittee(id) {
        const c = committees.find(x => x.id === id);
        if (!c) return;
        els.c_id.value = c.id;
        els.c_name.value = c.name;
        els.c_type.value = c.type;
        els.c_address.value = c.address || '';
        renderParentOptions(c.id);
        els.c_parent.value = c.parent_committee_id || '';
        toggleProjectFieldVisibility();
        els.c_project.value = c.project_id || '';
        els.committeeModalTitle.textContent = 'Edit Committee';
        els.committeeModalError.style.display = 'none';
        els.committeeModalBackdrop.classList.add('open');
    }

    function closeCommitteeModal() {
        els.committeeModalBackdrop.classList.remove('open');
    }

    async function submitCommittee(e) {
        e.preventDefault();
        els.committeeModalError.style.display = 'none';

        const id = els.c_id.value;
        const payload = {
            name: els.c_name.value.trim(),
            address: els.c_address.value.trim() || null,
            type: els.c_type.value,
            project_id: els.c_type.value === 'sub' ? (els.c_project.value || null) : null,
            parent_committee_id: els.c_parent.value || null,
        };

        try {
            const { data, message } = id
                ? await api.put(`/purchase-committees/${id}`, payload)
                : await api.post('/purchase-committees', payload);
            closeCommitteeModal();
            showNotice(message || 'Committee saved.');
            await loadAll();
            // Straight into member assignment for a brand-new committee —
            // naming it is only the first step.
            if (!id) manageMembers(data.id);
        } catch (err) {
            els.committeeModalError.textContent = err.message;
            els.committeeModalError.style.display = 'block';
        }
    }

    // ---- Members modal ----
    async function manageMembers(committeeId) {
        clearMessages();
        els.membersModalError.style.display = 'none';
        els.membersModalNotice.style.display = 'none';
        els.currentMembersTable.innerHTML = '<tbody><tr><td class="muted">Loading…</td></tr></tbody>';
        els.rosterChecklist.innerHTML = '<span class="empty">Loading…</span>';
        els.newUserRows.innerHTML = '';
        addUserRow();

        els.membersModalBackdrop.classList.add('open');

        try {
            const [{ data: committee }, { data: roster }] = await Promise.all([
                api.get(`/purchase-committees/${committeeId}`),
                api.get('/procurement-committee-members'),
            ]);

            currentCommittee = committee;
            els.membersCommitteeName.textContent = committee.name;
            const [min, max] = sizeRange(committee.type);
            els.membersCommitteeMeta.textContent =
                `${committee.type === 'sub' ? 'Sub-committee' : 'Main / Central committee'} — needs ${min}–${max} members per Policy §9.`;

            rosterMembersCache = roster;
            renderCurrentMembers();
            renderCopySourceOptions();
            renderRosterChecklist(roster);
        } catch (err) {
            els.membersModalError.textContent = err.message;
            els.membersModalError.style.display = 'block';
        }
    }

    function renderCurrentMembers() {
        const members = currentCommittee.members || [];
        if (!members.length) {
            els.currentMembersTable.innerHTML = '<tbody><tr><td class="muted">No members yet.</td></tr></tbody>';
            return;
        }
        els.currentMembersTable.innerHTML = '<tbody>' + members.map(m => `
            <tr data-member-id="${m.id}">
                <td>${escapeHtml(m.member_name || (m.user_id ? ('User #' + m.user_id) : 'Roster #' + m.procurement_committee_member_id))}${m.procurement_committee_member_id ? ' <span class="hint">(roster)</span>' : ''}</td>
                <td class="designation-cell">
                    <input type="text" value="${escapeHtml(m.designation_in_committee || '')}" placeholder="Designation" data-member-id="${m.id}">
                </td>
                <td style="width:1%; white-space:nowrap;">
                    <button type="button" class="rm-btn" data-remove-id="${m.id}">Remove</button>
                </td>
            </tr>
        `).join('') + '</tbody>';

        // Save designation on blur.
        els.currentMembersTable.querySelectorAll('input[data-member-id]').forEach(input => {
            input.addEventListener('change', () => updateDesignation(Number(input.dataset.memberId), input.value.trim()));
        });
        els.currentMembersTable.querySelectorAll('button[data-remove-id]').forEach(btn => {
            btn.addEventListener('click', () => removeMember(Number(btn.dataset.removeId)));
        });
    }

    async function updateDesignation(memberId, designation) {
        try {
            await api.put(`/committee-members/${memberId}`, { designation_in_committee: designation || null });
            showNotice('Designation updated.');
        } catch (err) {
            els.membersModalError.textContent = err.message;
            els.membersModalError.style.display = 'block';
        }
    }

    async function removeMember(memberId) {
        clearMessages();
        try {
            await api.del(`/committee-members/${memberId}`);
            await manageMembers(currentCommittee.id);
            await loadAll();
        } catch (err) {
            els.membersModalError.textContent = err.message;
            els.membersModalError.style.display = 'block';
        }
    }

    function currentMemberUserIds() {
        return new Set((currentCommittee.members || []).map(m => m.user_id).filter(Boolean));
    }

    function currentMemberRosterIds() {
        return new Set((currentCommittee.members || []).map(m => m.procurement_committee_member_id).filter(Boolean));
    }

    // Committee-roster members only ever get a login to do procurement work
    // — never Requester/Approver/ED/etc. — so the role is fixed, not picked.
    const PROCUREMENT_ROLE_NAME = 'procurement_officer';
    function procurementRoleId() {
        return roles.find(rl => rl.name === PROCUREMENT_ROLE_NAME)?.id || null;
    }

    function renderRosterChecklist(rosterMembers) {
        const already = currentMemberRosterIds();
        const candidates = rosterMembers.filter(r => !already.has(r.id));

        if (!candidates.length) {
            els.rosterChecklist.innerHTML = '<span class="empty">Everyone active on the roster is already on this committee.</span>';
            return;
        }

        els.rosterChecklist.innerHTML = candidates.map(r => `
            <div class="uc-row">
                <input type="checkbox" class="uc-select-cb" value="${r.id}" id="rc_${r.id}">
                <label for="rc_${r.id}" class="uc-name">${escapeHtml(r.name)} <span class="email">${escapeHtml(r.designation)}</span></label>
                <input type="text" placeholder="Designation" id="rc_desig_${r.id}" value="${escapeHtml(r.designation)}">
                <label class="uc-login-toggle" for="rc_login_${r.id}">
                    <input type="checkbox" class="uc-login-cb" id="rc_login_${r.id}" data-roster-id="${r.id}"> লগইন দিন
                </label>
            </div>
            <div class="uc-login-fields" id="rc_login_fields_${r.id}">
                <input type="email" placeholder="Email" id="rc_email_${r.id}">
                <span class="uc-role-fixed">Role: Procurement Officer</span>
            </div>
        `).join('');

        // Toggling "লগইন দিন" reveals the email input for that row only.
        els.rosterChecklist.querySelectorAll('.uc-login-cb').forEach(cb => {
            cb.addEventListener('change', () => {
                const fields = document.getElementById(`rc_login_fields_${cb.dataset.rosterId}`);
                if (fields) fields.classList.toggle('show', cb.checked);
            });
        });
    }

    async function assignRoster() {
        if (!currentCommittee) return;
        // Only the per-row "select this member" checkbox — not the separate
        // "লগইন দিন" toggle checkbox, which lives in the same container.
        const checked = [...els.rosterChecklist.querySelectorAll('input.uc-select-cb:checked')];

        if (!checked.length) {
            els.membersModalError.textContent = 'Select at least one roster member to add.';
            els.membersModalError.style.display = 'block';
            return;
        }

        clearMessages();
        let added = 0;
        const createdLogins = [];
        let assignError = null;
        try {
            for (const cb of checked) {
                const rosterId = cb.value;
                const desigInput = document.getElementById(`rc_desig_${rosterId}`);
                const designation = desigInput ? desigInput.value.trim() || null : null;
                const loginToggle = document.getElementById(`rc_login_${rosterId}`);

                let userId = null;
                if (loginToggle && loginToggle.checked) {
                    const emailInput = document.getElementById(`rc_email_${rosterId}`);
                    const email = emailInput ? emailInput.value.trim() : '';
                    const rosterRow = rosterMembersCache.find(r => String(r.id) === String(rosterId));
                    const name = rosterRow ? rosterRow.name : `Committee Member #${rosterId}`;

                    if (!email) {
                        throw new Error(`"${name}" এর জন্য লগইন চাওয়া হয়েছে কিন্তু Email দেওয়া হয়নি.`);
                    }
                    const roleId = procurementRoleId();
                    if (!roleId) {
                        throw new Error('"Procurement Officer" role খুঁজে পাওয়া যায়নি — প্রথমে Role Seeder/সেটআপ চেক করুন.');
                    }

                    const password = generatePassword();
                    const { data: user } = await api.post('/admin/users', {
                        name,
                        email,
                        password,
                        role_id: roleId,
                        // Procurement Officer is project-exempt, so this works
                        // for both a Sub-committee (has a project) and the
                        // Main/Central committee (no project) alike.
                        project_id: currentCommittee.project_id || null,
                        designation,
                        is_active: true,
                    });
                    userId = user.id;
                    createdLogins.push(`${name} (${email}) — password: ${password}`);
                }

                await api.post('/committee-members', {
                    committee_id: currentCommittee.id,
                    procurement_committee_member_id: rosterId,
                    user_id: userId,
                    designation_in_committee: designation,
                });
                added++;
            }
        } catch (err) {
            assignError = err;
        }

        // manageMembers() clears the notice/error boxes as soon as it starts,
        // so refresh first and only set the final message afterward — otherwise
        // a freshly generated password would be wiped before anyone reads it.
        await manageMembers(currentCommittee.id);
        await loadAll();

        if (assignError) {
            els.membersModalError.textContent = `${assignError.message}${added ? ` (${added} member(s) added before this happened.)` : ''}`;
            els.membersModalError.style.display = 'block';
        } else {
            els.membersModalNotice.innerHTML = `${added} roster member(s) added to ${currentCommittee.name}.`
                + (createdLogins.length
                    ? '<br>নতুন লগইন তৈরি হয়েছে — একবার শেয়ার করে নিন:<br>' + createdLogins.map(escapeHtml).join('<br>')
                    : '');
            els.membersModalNotice.style.display = 'block';
        }
    }

    function renderCopySourceOptions() {
        const others = committees.filter(c => c.id !== currentCommittee.id);
        els.copySourceCommittee.innerHTML = '<option value="">Select a committee…</option>' +
            others.map(c => `<option value="${c.id}">${escapeHtml(c.name)} (${(c.members || []).length} members)</option>`).join('');
    }

    async function copyMembers() {
        const sourceId = Number(els.copySourceCommittee.value);
        if (!sourceId) {
            els.membersModalError.textContent = 'Pick a committee to copy members from.';
            els.membersModalError.style.display = 'block';
            return;
        }
        const source = committees.find(c => c.id === sourceId);
        if (!source) return;

        const alreadyUsers = currentMemberUserIds();
        const alreadyRoster = currentMemberRosterIds();
        const toCopy = (source.members || []).filter(m =>
            m.user_id ? !alreadyUsers.has(m.user_id) : !alreadyRoster.has(m.procurement_committee_member_id)
        );

        if (!toCopy.length) {
            els.membersModalError.textContent = 'Every member of that committee is already on this one.';
            els.membersModalError.style.display = 'block';
            return;
        }

        clearMessages();
        let added = 0;
        try {
            for (const m of toCopy) {
                await api.post('/committee-members', {
                    committee_id: currentCommittee.id,
                    user_id: m.user_id || null,
                    procurement_committee_member_id: m.procurement_committee_member_id || null,
                    designation_in_committee: m.designation_in_committee,
                });
                added++;
            }
            els.membersModalNotice.textContent = `Copied ${added} member(s) from "${source.name}".`;
            els.membersModalNotice.style.display = 'block';
        } catch (err) {
            // Most likely the max-size cap (Policy §9) — report what got through.
            els.membersModalError.textContent = `${err.message}${added ? ` (${added} member(s) copied before this happened.)` : ''}`;
            els.membersModalError.style.display = 'block';
        }
        await manageMembers(currentCommittee.id);
        await loadAll();
    }

    // ---- New user rows (inline, inside the Members modal) ----
    function addUserRow() {
        const row = document.createElement('div');
        row.className = 'new-user-row';
        const roleOpts = roles.map(r => `<option value="${r.id}">${roleLabel(r.name)}</option>`).join('');
        row.innerHTML = `
            <input type="text" placeholder="Full name" class="nu_name">
            <input type="email" placeholder="Email" class="nu_email">
            <select class="nu_role">${roleOpts}</select>
            <input type="text" placeholder="Phone (optional)" class="nu_phone">
            <input type="text" placeholder="Designation" class="nu_desig">
        `;
        els.newUserRows.appendChild(row);
    }

    function generatePassword() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        return Array.from({ length: 10 }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
    }

    async function createNewUsers() {
        if (!currentCommittee) return;
        const rows = [...els.newUserRows.querySelectorAll('.new-user-row')];
        const created = [];
        els.membersModalError.style.display = 'none';

        for (const row of rows) {
            const name = row.querySelector('.nu_name').value.trim();
            const email = row.querySelector('.nu_email').value.trim();
            if (!name || !email) continue; // skip untouched rows

            const password = generatePassword();
            const designation = row.querySelector('.nu_desig').value.trim() || null;
            try {
                const { data: user } = await api.post('/admin/users', {
                    name,
                    email,
                    password,
                    role_id: Number(row.querySelector('.nu_role').value),
                    // Sub-committees belong to a project — most roles require
                    // one. Main/Central committees have no project, so this
                    // stays null (fine for Admin/Procurement Officer roles;
                    // other roles will get a clear validation error to pick
                    // a project-bearing sub-committee instead).
                    project_id: currentCommittee.project_id || null,
                    phone: row.querySelector('.nu_phone').value.trim() || null,
                    is_active: true,
                });
                await api.post('/committee-members', {
                    committee_id: currentCommittee.id,
                    user_id: user.id,
                    designation_in_committee: designation,
                });
                created.push(`${name} (${email}) — password: ${password}`);
            } catch (err) {
                els.membersModalError.textContent = `Couldn't add ${name}: ${err.message}`;
                els.membersModalError.style.display = 'block';
                return created; // stop on first failure, keep what worked
            }
        }

        if (created.length) {
            els.membersModalNotice.innerHTML = 'New accounts created and added — share these once:<br>' + created.map(escapeHtml).join('<br>');
            els.membersModalNotice.style.display = 'block';
            els.newUserRows.innerHTML = '';
            addUserRow();
            await manageMembers(currentCommittee.id);
            await loadAll();
        }
        return created;
    }

    function closeMembersModal() {
        els.membersModalBackdrop.classList.remove('open');
        currentCommittee = null;
    }

    // ---- Wire up ----
    els.btnNewCommittee.addEventListener('click', openNewCommitteeModal);
    els.btnCancelCommittee.addEventListener('click', closeCommitteeModal);
    els.committeeModalBackdrop.addEventListener('click', e => { if (e.target === els.committeeModalBackdrop) closeCommitteeModal(); });
    els.committeeForm.addEventListener('submit', submitCommittee);
    els.c_type.addEventListener('change', toggleProjectFieldVisibility);

    els.btnCopyMembers.addEventListener('click', copyMembers);
    els.btnAssignRoster.addEventListener('click', assignRoster);
    els.btnAddUserRow.addEventListener('click', addUserRow);
    els.btnCloseMembers.addEventListener('click', async () => {
        await createNewUsers();
        if (els.membersModalError.style.display === 'block') return; // keep it open so they can see/fix the error
        closeMembersModal();
        await loadAll();
    });
    els.membersModalBackdrop.addEventListener('click', e => { if (e.target === els.membersModalBackdrop) closeMembersModal(); });

    window.AdminCommittees = { manageMembers, editCommittee };

    loadAll();
})();