// Comportamiento común a todas las páginas (menú y cerrar sesión).
import { fetchJSON } from './api.js';

// Marca en el menú la página en la que estamos.
document.querySelectorAll('nav.principal a').forEach((enlace) => {
    if (enlace.pathname === location.pathname) {
        enlace.setAttribute('aria-current', 'page');
    }
});

document.getElementById('btn-logout')?.addEventListener('click', async () => {
    try {
        await fetchJSON('/api/logout', { method: 'POST' });
    } finally {
        window.location.assign('/login');
    }
});
