// =============================================================================
// Registro: datos personales + servicios que ofrece (opcional) + foto (opcional).
// Orden: crear cuenta (con sus ofertas) -> iniciar sesión -> subir la foto.
// =============================================================================

import { fetchJSON, escapeHTML, textoError } from './api.js';

const MAX_FOTO_BYTES = 2 * 1024 * 1024;
const TIPOS_FOTO = ['image/jpeg', 'image/png', 'image/webp'];

const form = document.getElementById('form-registro');
const mensaje = document.getElementById('mensaje');
const boton = document.getElementById('btn-registrar');
const campoFoto = document.getElementById('foto');

const listaCategorias = document.getElementById('lista-categorias');
const listaServicios = document.getElementById('lista-servicios');
const listaSeleccion = document.getElementById('lista-seleccion');

let categorias = [];
let catalogo = [];
let categoriaActiva = null;
const elegidos = new Map(); // id_servicio -> servicio

function mostrarError(texto) {
    mensaje.textContent = texto;
    mensaje.hidden = false;
    boton.disabled = false;
}

// -----------------------------------------------------------------------------
// Selector de servicios: categorías -> servicios -> servicios seleccionados
// -----------------------------------------------------------------------------

function pintarCategorias() {
    listaCategorias.innerHTML = categorias.length === 0
        ? '<li class="selector-vacio">No hay categorías</li>'
        : categorias.map((c) => `
            <li><button type="button" data-categoria="${c.id_categoria}"
                        aria-pressed="${c.id_categoria === categoriaActiva}">${escapeHTML(c.nombre)}</button></li>`).join('');
}

function pintarServicios() {
    if (categoriaActiva === null) {
        listaServicios.innerHTML = '<li class="selector-vacio">Elige una categoría</li>';
        return;
    }
    const servicios = catalogo.filter((s) => s.id_categoria === categoriaActiva);
    listaServicios.innerHTML = servicios.length === 0
        ? '<li class="selector-vacio">Esta categoría aún no tiene servicios</li>'
        : servicios.map((s) => {
            const yaElegido = elegidos.has(s.id_servicio);
            return `
            <li><button type="button" data-servicio="${s.id_servicio}"${yaElegido ? ' disabled' : ''}>
                <span>${escapeHTML(s.nombre)}</span>
                <span class="nota-pts">${yaElegido ? 'añadido' : `${s.puntos_hora_referencia} pts/h`}</span>
            </button></li>`;
        }).join('');
}

function pintarSeleccion() {
    listaSeleccion.innerHTML = elegidos.size === 0
        ? '<li class="selector-vacio">Todavía no has elegido ninguno</li>'
        : [...elegidos.values()].map((s) => `
            <li class="selector-elegido">
                <span>${escapeHTML(s.nombre)}<br>
                    <span class="nota-pts">${escapeHTML(s.categoria_nombre ?? '')} · ${s.puntos_hora_referencia} pts/h</span>
                </span>
                <button type="button" class="selector-quitar" data-quitar="${s.id_servicio}"
                        aria-label="Quitar ${escapeHTML(s.nombre)}">×</button>
            </li>`).join('');
}

listaCategorias.addEventListener('click', (e) => {
    const b = e.target.closest('button[data-categoria]');
    if (!b) return;
    categoriaActiva = Number.parseInt(b.dataset.categoria, 10);
    pintarCategorias();
    pintarServicios();
    listaCategorias.querySelector('[aria-pressed="true"]')?.focus(); // no perder el foco del teclado
});

listaServicios.addEventListener('click', (e) => {
    const b = e.target.closest('button[data-servicio]');
    if (!b) return;
    const servicio = catalogo.find((s) => s.id_servicio === Number.parseInt(b.dataset.servicio, 10));
    if (!servicio) return;
    elegidos.set(servicio.id_servicio, servicio);
    pintarServicios();
    pintarSeleccion();
});

listaSeleccion.addEventListener('click', (e) => {
    const b = e.target.closest('button[data-quitar]');
    if (!b) return;
    elegidos.delete(Number.parseInt(b.dataset.quitar, 10));
    pintarServicios();
    pintarSeleccion();
});

async function cargarCatalogo() {
    try {
        [categorias, catalogo] = await Promise.all([
            fetchJSON('/api/categorias'),
            fetchJSON('/api/servicios'),
        ]);
    } catch {
        listaCategorias.innerHTML =
            '<li class="selector-vacio">No se pudo cargar el catálogo. Puedes registrarte sin elegir servicios.</li>';
        return;
    }
    pintarCategorias();
    pintarServicios();
    pintarSeleccion();
}

// -----------------------------------------------------------------------------
// Envío del formulario
// -----------------------------------------------------------------------------

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

    // La foto se comprueba antes de crear nada, para no dejar la cuenta a medias.
    const foto = campoFoto.files[0] ?? null;
    if (foto && !TIPOS_FOTO.includes(foto.type)) {
        mostrarError('La foto debe ser JPG, PNG o WEBP');
        return;
    }
    if (foto && foto.size > MAX_FOTO_BYTES) {
        mostrarError('La foto no puede superar los 2 MB');
        return;
    }

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
                servicios: [...elegidos.keys()],
            }),
        });

        // Cuenta creada: se inicia sesión directamente con los mismos datos.
        sesion = await fetchJSON('/api/login', {
            method: 'POST',
            body: JSON.stringify({ email, password }),
        });
    } catch (error) {
        mostrarError(textoError(error));
        return;
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
            setTimeout(() => window.location.assign(sesion.redirect), 3500);
            return;
        }
    }

    window.location.assign(sesion.redirect);
});

cargarCatalogo();
