// =============================================================================
// Buscador de servicios: lista las ofertas de los profesionales y permite
// solicitarlas (solo usuarios PROFESIONAL con sesión).
// =============================================================================

import { fetchJSON, escapeHTML, textoError } from './api.js';

const app = document.getElementById('app-buscar');
const idUsuario = Number.parseInt(app.dataset.usuarioId, 10) || null;
const rol = app.dataset.rol;
const puedeSolicitar = rol === 'PROFESIONAL';

const formFiltros = document.getElementById('form-filtros');
const campoTexto = document.getElementById('q');
const selectCategoria = document.getElementById('id_categoria');
const lista = document.getElementById('app-ofertas');
const mensaje = document.getElementById('mensaje');

// Ventana de solicitud: solo existe en la página si el usuario es PROFESIONAL.
const dialogo = document.getElementById('dialogo-solicitud');
const formSolicitud = document.getElementById('form-solicitud');
const campoOferta = document.getElementById('id_oferta');
const campoDuracionHoras = document.getElementById('duracion-horas');
const campoDuracionMinutos = document.getElementById('duracion-minutos');
const campoFecha = document.getElementById('fecha_propuesta');
const textoResumen = document.getElementById('dialogo-resumen');
const textoTotal = document.getElementById('dialogo-total');
const textoSaldo = document.getElementById('dialogo-saldo');
const mensajeDialogo = document.getElementById('dialogo-mensaje');
const btnEnviar = document.getElementById('btn-enviar-solicitud');
const btnCerrar = document.getElementById('btn-cerrar-dialogo');

let ofertas = [];

// -----------------------------------------------------------------------------
// Carga y render
// -----------------------------------------------------------------------------

async function cargarCategorias() {
    const categorias = await fetchJSON('/api/categorias');
    selectCategoria.insertAdjacentHTML(
        'beforeend',
        categorias.map((c) => `<option value="${c.id_categoria}">${escapeHTML(c.nombre)}</option>`).join('')
    );
}

async function buscar() {
    lista.innerHTML = '<p>Buscando…</p>';

    const params = new URLSearchParams();
    if (selectCategoria.value) params.set('id_categoria', selectCategoria.value);
    const texto = campoTexto.value.trim();
    if (texto) params.set('q', texto);
    const consulta = params.toString();

    try {
        ofertas = await fetchJSON('/api/ofertas' + (consulta ? `?${consulta}` : ''));
        renderizar();
    } catch (error) {
        lista.innerHTML = `<p role="alert">No se pudieron cargar los servicios: ${escapeHTML(error.message)}</p>`;
    }
}

function accionDe(oferta) {
    if (idUsuario === null) {
        return '<a class="enlace-boton" href="/login">Inicia sesión para solicitar</a>';
    }
    if (!puedeSolicitar) {
        return '<span class="nota">Solo los profesionales pueden solicitar servicios</span>';
    }
    if (oferta.id_profesional === idUsuario) {
        return '<span class="nota">Es tu propia oferta</span>';
    }
    return `<button type="button" class="btn-solicitar" data-id="${oferta.id_oferta}">Solicitar</button>`;
}

function renderizar() {
    if (ofertas.length === 0) {
        lista.innerHTML = '<p>No hay servicios que coincidan con tu búsqueda.</p>';
        return;
    }

    lista.innerHTML = '<ul class="lista-servicios">' + ofertas.map((o) => `
        <li class="ticket">
            <div class="ticket-cuerpo">
                <h4>${escapeHTML(o.servicio_nombre)}</h4>
                <p class="ticket-categoria">${escapeHTML(o.categoria_nombre)}</p>
                <p class="ticket-profesional">
                    ${o.foto ? `<img class="avatar" src="${escapeHTML(o.foto)}" alt="" loading="lazy">` : ''}
                    <span>Lo ofrece <strong><a href="/profesionales/${o.id_profesional}">${escapeHTML(o.profesional)}</a></strong></span>
                    <a href="/profesionales/${o.id_profesional}">Ver perfil y reseñas</a>
                </p>
                ${o.descripcion_personal ? `<p class="ticket-desc">${escapeHTML(o.descripcion_personal)}</p>` : ''}
                <div class="acciones">${accionDe(o)}</div>
            </div>
            <div class="ticket-talon">
                <strong>${o.puntos_hora}</strong>
                <span>puntos/hora</span>
            </div>
        </li>`).join('') + '</ul>';
}

function mostrarMensajeHTML(html) {
    mensaje.innerHTML = html; // solo se usa con texto fijo escrito aquí, nunca con datos del usuario
    mensaje.dataset.tipo = 'ok';
    mensaje.hidden = false;
    mensaje.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// -----------------------------------------------------------------------------
// Ventana de solicitud
// -----------------------------------------------------------------------------

/** "Ahora" en hora local con el formato de <input type="datetime-local">. */
function ahoraLocal() {
    const d = new Date();
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0, 16);
}

function ofertaElegida() {
    return ofertas.find((o) => o.id_oferta === Number.parseInt(campoOferta.value, 10));
}

/** Mismo cálculo que el servidor: puntos/hora x horas, redondeado hacia arriba. */
function actualizarTotal() {
    const oferta = ofertaElegida();
    const horas = obtenerDuracionHoras();
    const total = oferta && horas !== null
        ? Math.ceil(Math.round(oferta.puntos_hora * horas * 100) / 100)
        : 0;
    textoTotal.textContent = String(total);
}

function obtenerDuracionHoras() {
    const horas = Number.parseInt(campoDuracionHoras.value, 10);
    const minutos = Number.parseInt(campoDuracionMinutos.value, 10);
    const duracion = horas + minutos / 60;
    return duracion >= 0.5 && duracion <= 24 ? duracion : null;
}

async function abrirDialogo(idOferta) {
    const oferta = ofertas.find((o) => o.id_oferta === idOferta);
    if (!oferta || !dialogo) return;

    campoOferta.value = String(oferta.id_oferta);
    campoDuracionHoras.value = '1';
    campoDuracionMinutos.value = '0';
    campoFecha.value = '';
    campoFecha.min = ahoraLocal();
    textoResumen.textContent =
        `${oferta.servicio_nombre} · ${oferta.profesional} · ${oferta.puntos_hora} puntos/hora`;
    textoSaldo.textContent = 'Consultando…';
    mensajeDialogo.hidden = true;
    btnEnviar.disabled = true;
    actualizarTotal();

    dialogo.showModal();
    campoDuracionHoras.focus();

    try {
        const saldo = await fetchJSON('/api/puntos-disponibles');
        textoSaldo.textContent = `${saldo.puntos_disponibles} puntos`;
        btnEnviar.disabled = false;
    } catch (error) {
        mensajeDialogo.textContent = `No se pudo consultar tu saldo disponible: ${textoError(error)}`;
        mensajeDialogo.hidden = false;
    }
}

if (dialogo) {
    campoDuracionHoras.addEventListener('change', actualizarTotal);
    campoDuracionMinutos.addEventListener('change', actualizarTotal);
    btnCerrar.addEventListener('click', () => dialogo.close());

    formSolicitud.addEventListener('submit', async (e) => {
        e.preventDefault();
        mensajeDialogo.hidden = true;
        btnEnviar.disabled = true;

        const horasEstimadas = obtenerDuracionHoras();
        if (horasEstimadas === null) {
            mensajeDialogo.textContent = 'Elige una duración de al menos 30 minutos y como máximo 24 horas.';
            mensajeDialogo.hidden = false;
            btnEnviar.disabled = false;
            return;
        }

        try {
            const resultado = await fetchJSON('/api/solicitudes', {
                method: 'POST',
                body: JSON.stringify({
                    id_oferta: Number.parseInt(campoOferta.value, 10),
                    horas_estimadas: horasEstimadas,
                    fecha_propuesta: campoFecha.value,
                }),
            });
            textoSaldo.textContent = `${resultado.puntos_disponibles} puntos`;
            dialogo.close();
            mostrarMensajeHTML('Solicitud enviada. El profesional la verá en su lista. <a href="/solicitudes">Ver mis solicitudes</a>');
        } catch (error) {
            mensajeDialogo.textContent = textoError(error);
            mensajeDialogo.hidden = false;
            btnEnviar.disabled = false;
        }
    });
}

// -----------------------------------------------------------------------------
// Eventos y arranque
// -----------------------------------------------------------------------------

formFiltros.addEventListener('submit', (e) => {
    e.preventDefault();
    buscar();
});
selectCategoria.addEventListener('change', buscar);

lista.addEventListener('click', (e) => {
    const boton = e.target.closest('button.btn-solicitar');
    if (boton) void abrirDialogo(Number.parseInt(boton.dataset.id, 10));
});

try {
    await cargarCategorias();
} catch {
    // sin categorías el buscador sigue funcionando con el filtro de texto
}
await buscar();
