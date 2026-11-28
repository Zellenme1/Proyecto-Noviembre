// Comportamiento común a todas las páginas (menú y cerrar sesión).
import { fetchJSON } from './api.js';

// Marca en el menú la página en la que estamos.
document.querySelectorAll('nav.principal a').forEach((enlace) => {
    if (enlace.pathname === location.pathname) {
        enlace.setAttribute('aria-current', 'page');
    }
});

const menuBoton = document.getElementById('btn-menu');
const menuPanel = document.getElementById('panel-menu');

if (menuBoton && menuPanel) {
    const establecerMenuAbierto = (abierto) => {
        // Actualiza ambos atributos para abrir o cerrar el menú móvil.
        menuPanel.dataset.abierto = String(abierto);
        menuBoton.setAttribute('aria-expanded', String(abierto));
        menuBoton.setAttribute('aria-label', abierto ? 'Cerrar menú' : 'Abrir menú');
    };

    menuBoton.addEventListener('click', () => {
        // Cambia el menú cuando se pulsa el botón.
        establecerMenuAbierto(menuBoton.getAttribute('aria-expanded') !== 'true');
    });

    menuPanel.addEventListener('click', (e) => {
        // Cierra el menú después de elegir una página.
        if (e.target.closest('a')) establecerMenuAbierto(false);
    });

    document.addEventListener('keydown', (e) => {
        // Permite cerrar el menú con Escape.
        if (e.key === 'Escape' && menuBoton.getAttribute('aria-expanded') === 'true') {
            establecerMenuAbierto(false);
            menuBoton.focus();
        }
    });
}

document.getElementById('btn-logout')?.addEventListener('click', async () => {
    try {
        await fetchJSON('/api/logout', { method: 'POST' });
    } finally {
        window.location.assign('/login');
    }
});
