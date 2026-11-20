// =============================================================================
// Registro. Pasos: crear la cuenta (con los servicios marcados) -> iniciar
// sesión -> subir la foto (si hay). La foto va al final porque para subirla
// hace falta tener la sesión iniciada.
// =============================================================================

import { fetchJSON, textoError } from './api.js';

const MAX_FOTO_BYTES = 2 * 1024 * 1024;
const TIPOS_FOTO = ['image/jpeg', 'image/png', 'image/webp'];

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
    const foto = document.getElementById('foto').files[0] ?? null;

    // Se comprueba todo lo que se puede antes de crear nada, para no dejar la cuenta a medias.
    if (password !== document.getElementById('password2').value) {
        return mostrarError('Las contraseñas no coinciden');
    }
    if (foto && !TIPOS_FOTO.includes(foto.type)) {
        return mostrarError('La foto debe ser JPG, PNG o WEBP');
    }
    if (foto && foto.size > MAX_FOTO_BYTES) {
        return mostrarError('La foto no puede superar los 2 MB');
    }

    // Los servicios son las casillas marcadas
    const servicios = [...form.querySelectorAll('input[name="servicios"]:checked')].map((c) => Number(c.value));

    let sesion;
    try {
        await fetchJSON('/api/registro', {
            method: 'POST',
            body: JSON.stringify({
                nombre: document.getElementById('nombre').value,
                apellidos: document.getElementById('apellidos').value,
                email,
                telefono: document.getElementById('telefono').value,
                password,
                servicios,
            }),
        });
        sesion = await fetchJSON('/api/login', { method: 'POST', body: JSON.stringify({ email, password }) });
    } catch (error) {
        return mostrarError(textoError(error));
    }

    // El login renueva el token CSRF: hay que usar el nuevo en la siguiente petición.
    document.querySelector('meta[name="csrf-token"]').content = sesion.csrf_token;

    if (foto) {
        try {
            const datos = new FormData();
            datos.append('foto', foto);
            await fetchJSON('/api/foto', { method: 'POST', body: datos });
        } catch (error) {
            // La cuenta ya existe y tiene sesión: se avisa y se continúa igualmente.
            mostrarError(`Tu cuenta está creada, pero no se pudo subir la foto: ${error.message}`);
            boton.disabled = true;
            return setTimeout(() => window.location.assign(sesion.redirect), 3500);
        }
    }

    window.location.assign(sesion.redirect);
});
