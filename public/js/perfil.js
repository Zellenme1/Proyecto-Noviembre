import { fetchJSON, textoError } from './api.js';

const form = document.getElementById('form-perfil');
const mensaje = document.getElementById('mensaje');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    mensaje.hidden = true;

    try {
        await fetchJSON('/api/perfil', {
            method: 'PUT',
            body: JSON.stringify({
                nombre: document.getElementById('nombre').value,
                apellidos: document.getElementById('apellidos').value,
                telefono: document.getElementById('telefono').value,
            }),
        });
        mensaje.dataset.tipo = 'ok';
        mensaje.textContent = 'Datos guardados correctamente';
    } catch (error) {
        mensaje.dataset.tipo = 'error';
        mensaje.textContent = textoError(error);
    }
    mensaje.hidden = false;
});