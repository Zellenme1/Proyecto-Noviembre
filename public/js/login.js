import { fetchJSON, textoError } from './api.js';

const form = document.getElementById('form-login');
const mensaje = document.getElementById('mensaje');
const boton = document.getElementById('btn-entrar');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    mensaje.hidden = true;
    boton.disabled = true;

    try {
        const respuesta = await fetchJSON('/api/login', {
            method: 'POST',
            body: JSON.stringify({
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
            }),
        });
        window.location.assign(respuesta.redirect);
    } catch (error) {
        mensaje.textContent = textoError(error);
        mensaje.hidden = false;
        boton.disabled = false;
    }
});
