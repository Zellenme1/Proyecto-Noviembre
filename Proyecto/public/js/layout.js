// Comportamiento común del layout: cerrar sesión.
import { fetchJSON } from './api.js';

document.getElementById('btn-logout')?.addEventListener('click', async () => {
    try {
        await fetchJSON('/api/logout', { method: 'POST' });
    } finally {
        window.location.assign('/login');
    }
});
