import { fetchJSON, textoError } from './api.js';

const form = document.getElementById('form-registro');
const mensaje = document.getElementById('mensaje');
const boton = document.getElementById('btn-registrar');

function mostrarError(texto) {
    mensaje.textContent = texto;
    mensaje.hidden = false;
    boton.disabled = false;
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    mensaje.hidden = true;
    boton.disabled = true;

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (password !== document.getElementById('password2').value) {
        mostrarError('Las contraseñas no coinciden');
        return;
    }

    try {
        await fetchJSON('/api/registro', {
            method: 'POST',
            body: JSON.stringify({
                nombre: document.getElementById('nombre').value,
                apellidos: document.getElementById('apellidos').value,
                email,
                telefono: document.getElementById('telefono').value,
                password,
            }),
        });

        // Cuenta creada: se inicia sesión directamente con los mismos datos.
        const sesion = await fetchJSON('/api/login', {
            method: 'POST',
            body: JSON.stringify({ email, password }),
        });
        window.location.assign(sesion.redirect);
    } catch (error) {
        mostrarError(textoError(error));
    }
});
