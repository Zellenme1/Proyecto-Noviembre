import { fetchJSON, textoError } from './api.js';

const form = document.getElementById('form-login');
const mensaje = document.getElementById('mensaje');
const boton = document.getElementById('btn-entrar');

form.addEventListener('submit', async (e) => {
    // Evita la recarga normal y usa la API para iniciar sesión.
    e.preventDefault();
    mensaje.hidden = true;
    boton.disabled = true;

    try {
        // Lee el email y la contraseña que escribió el usuario.
        const respuesta = await fetchJSON('/api/login', {
            method: 'POST',
            body: JSON.stringify({
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
            }),
        });
        // El servidor indica la página que corresponde al tipo de usuario.
        window.location.assign(respuesta.redirect);
    } catch (error) {
        // Mantiene al usuario en la página y enseña el aviso.
        mensaje.textContent = textoError(error);
        mensaje.hidden = false;
        boton.disabled = false;
    }
});
