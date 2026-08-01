/**
 * Tiny fetch wrapper for calling this app's own /api/* routes from Blade
 * pages, using the logged-in session cookie (Sanctum stateful auth) —
 * no manual token handling needed.
 *
 * Usage:
 *   const { data } = await api.get('/purchase-requisitions');
 *   const { data } = await api.post('/purchase-requisitions', payload);
 */
const api = (() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    async function request(method, path, body) {
        const res = await fetch(`/api${path}`, {
            method,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body ? JSON.stringify(body) : undefined,
        });

        const json = await res.json().catch(() => ({}));

        if (!res.ok) {
            const message = json.message
                || (json.errors ? Object.values(json.errors).flat().join(', ') : null)
                || `Request failed (${res.status})`;
            throw new Error(message);
        }

        return json;
    }

    return {
        get: (path) => request('GET', path),
        post: (path, body) => request('POST', path, body),
        put: (path, body) => request('PUT', path, body),
        del: (path) => request('DELETE', path),
    };
})();
