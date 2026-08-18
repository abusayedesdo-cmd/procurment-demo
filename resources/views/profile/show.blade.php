@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <h1>My Profile</h1>

    <div id="errorBox" class="error-box" style="display:none;"></div>
    <div id="successBox" class="error-box" style="display:none; background:#ecfdf5; color:#065f46; border-color:#a7f3d0;"></div>

    <div class="card" style="max-width:520px;">
        <div class="row">
            <div>
                <label>Name</label>
                <input type="text" id="profileName" disabled>
            </div>
            <div>
                <label>Designation</label>
                <input type="text" id="profileDesignation" disabled>
            </div>
        </div>

        <hr style="margin:1.25rem 0; border:none; border-top:1px solid #e5e7eb;">

        <label>My Signature</label>
        <p style="font-size:0.85rem; color:#6b7280; margin-top:-4px;">
            Upload a photo or scan of your signature. It will print automatically wherever
            your name appears on generated PR, RFQ, and Tender documents, instead of a blank
            signature line.
        </p>

        <div id="signaturePreviewWrap" style="display:none; margin:12px 0;">
            <img id="signaturePreview" alt="Current signature" style="max-height:80px; max-width:100%; border:1px solid #e5e7eb; border-radius:4px; padding:6px; background:#fff;">
        </div>

        <input type="file" id="signatureFile" accept=".jpg,.jpeg,.png">
        <div class="row" style="margin-top:12px; gap:8px;">
            <button type="button" id="uploadSignatureBtn" class="btn">Upload Signature</button>
            <button type="button" id="removeSignatureBtn" class="btn secondary" style="display:none;">Remove Signature</button>
        </div>
    </div>

    <script>
        const errorBox = document.getElementById('errorBox');
        const successBox = document.getElementById('successBox');
        const preview = document.getElementById('signaturePreview');
        const previewWrap = document.getElementById('signaturePreviewWrap');
        const removeBtn = document.getElementById('removeSignatureBtn');

        function showError(message) {
            successBox.style.display = 'none';
            errorBox.textContent = message;
            errorBox.style.display = 'block';
        }

        function showSuccess(message) {
            errorBox.style.display = 'none';
            successBox.textContent = message;
            successBox.style.display = 'block';
        }

        function renderSignature(user) {
            if (user.signature_url) {
                preview.src = user.signature_url;
                previewWrap.style.display = 'block';
                removeBtn.style.display = 'inline-block';
            } else {
                previewWrap.style.display = 'none';
                removeBtn.style.display = 'none';
            }
        }

        async function init() {
            try {
                const { data: me } = await api.get('/me');
                document.getElementById('profileName').value = me.name ?? '';
                document.getElementById('profileDesignation').value = me.designation ?? '';
                renderSignature(me);
            } catch (err) {
                showError(err.message);
            }
        }

        function csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content;
        }

        document.getElementById('uploadSignatureBtn').addEventListener('click', async () => {
            const file = document.getElementById('signatureFile').files[0];
            if (!file) {
                showError('Please choose a signature image first (JPG or PNG).');
                return;
            }

            const formData = new FormData();
            formData.append('signature', file);

            try {
                const res = await fetch('/api/profile/signature', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });
                const json = await res.json();
                if (!res.ok || !json.success) {
                    throw new Error(json.message || 'Failed to upload signature.');
                }
                renderSignature(json.data);
                document.getElementById('signatureFile').value = '';
                showSuccess('Signature uploaded successfully.');
            } catch (err) {
                showError(err.message);
            }
        });

        removeBtn.addEventListener('click', async () => {
            try {
                const res = await fetch('/api/profile/signature', {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await res.json();
                if (!res.ok || !json.success) {
                    throw new Error(json.message || 'Failed to remove signature.');
                }
                renderSignature(json.data);
                showSuccess('Signature removed.');
            } catch (err) {
                showError(err.message);
            }
        });

        init();
    </script>
@endsection