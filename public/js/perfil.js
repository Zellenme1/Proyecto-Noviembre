import { fetchJSON, textoError } from './api.js';

const form = document.getElementById('form-eliminar-cuenta');
const mensaje = document.getElementById('mensaje');
const boton = form.querySelector('button[type="submit"]');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    mensaje.hidden = true;

    if (!window.confirm('¿Seguro que quieres eliminar tu cuenta? Esta acción cerrará tu sesión.')) return;

    boton.disabled = true;
    try {
        await fetchJSON('/api/perfil', {
            method: 'DELETE',
            body: JSON.stringify({
                password: document.getElementById('password-confirmacion').value,
            }),
        });
        window.location.assign('/');
    } catch (error) {
        mensaje.dataset.tipo = 'error';
        mensaje.textContent = textoError(error);
        boton.disabled = false;
        mensaje.hidden = false;
    }
});