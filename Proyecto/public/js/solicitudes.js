// =============================================================================
// Mis solicitudes: las que me han enviado (recibidas) y las que he enviado yo.
// =============================================================================

import { fetchJSON, escapeHTML, textoError, formatearFecha, etiquetaEstado } from './api.js';

const listaRecibidas = document.getElementById('lista-recibidas');
const listaEnviadas = document.getElementById('lista-enviadas');
const mensaje = document.getElementById('mensaje');

function mostrarMensaje(texto, tipo = 'ok') {
    mensaje.textContent = texto;
    mensaje.dataset.tipo = tipo;
    mensaje.hidden = false;
}

// -----------------------------------------------------------------------------
// Render
// -----------------------------------------------------------------------------

/** Botones según quién soy en la solicitud y en qué estado está. */
function accionesDe(s, tipo) {
    if (tipo === 'recibidas' && s.estado === 'SOLICITADO') {
        return `
            <button type="button" data-accion="aceptar" data-id="${s.id_solicitud}">Aceptar</button>
            <button type="button" class="btn-eliminar" data-accion="rechazar" data-id="${s.id_solicitud}">Rechazar</button>`;
    }
    if (tipo === 'enviadas' && (s.estado === 'SOLICITADO' || s.estado === 'ACEPTADO')) {
        return `<button type="button" class="btn-eliminar" data-accion="cancelar" data-id="${s.id_solicitud}">Cancelar solicitud</button>`;
    }
    return '';
}

function confirmaciones(s) {
    if (s.estado !== 'EN_EJECUCION') return '';
    const cliente = s.confirmado_cliente ? 'confirmado' : 'pendiente';
    const prestador = s.confirmado_prestador ? 'confirmado' : 'pendiente';
    return `<p class="ticket-desc">Finalización: cliente ${cliente} · profesional ${prestador}</p>`;
}

function tarjeta(s, tipo) {
    const quien = tipo === 'recibidas' ? 'Cliente' : 'Profesional';
    const acciones = accionesDe(s, tipo);
    const horas = String(s.horas_estimadas).replace('.', ',');

    return `
        <li class="ticket">
            <div class="ticket-cuerpo">
                <h4>${escapeHTML(s.servicio_nombre)}</h4>
                <span class="estado" data-estado="${escapeHTML(s.estado)}">${escapeHTML(etiquetaEstado(s.estado))}</span>
                <p>${quien}: <strong>${escapeHTML(s.otra_persona)}</strong></p>
                <p class="ticket-desc">${escapeHTML(formatearFecha(s.fecha_propuesta))} · ${escapeHTML(horas)} h</p>
                ${confirmaciones(s)}
                ${acciones ? `<div class="acciones">${acciones}</div>` : ''}
            </div>
            <div class="ticket-talon">
                <strong>${s.puntos_totales}</strong>
                <span>puntos</span>
            </div>
        </li>`;
}

function pintar(contenedor, solicitudes, tipo, vacio) {
    contenedor.innerHTML = solicitudes.length === 0
        ? `<p>${vacio}</p>`
        : '<ul class="lista-servicios">' + solicitudes.map((s) => tarjeta(s, tipo)).join('') + '</ul>';
}

async function cargar() {
    try {
        const datos = await fetchJSON('/api/solicitudes');
        pintar(listaRecibidas, datos.recibidas, 'recibidas', 'Todavía no has recibido ninguna solicitud.');
        pintar(listaEnviadas, datos.enviadas, 'enviadas', 'Todavía no has pedido ningún servicio.');
    } catch (error) {
        const aviso = `<p role="alert">No se pudieron cargar las solicitudes: ${escapeHTML(error.message)}</p>`;
        listaRecibidas.innerHTML = aviso;
        listaEnviadas.innerHTML = aviso;
    }
}

// -----------------------------------------------------------------------------
// Acciones (un solo listener por delegación de eventos)
// -----------------------------------------------------------------------------

const TEXTO_OK = {
    aceptar: 'Solicitud aceptada',
    rechazar: 'Solicitud rechazada',
    cancelar: 'Solicitud cancelada',
};

async function onClick(e) {
    const boton = e.target.closest('button[data-accion]');
    if (!boton) return;

    const { accion, id } = boton.dataset;

    if (accion === 'rechazar' && !confirm('¿Rechazar esta solicitud?')) return;
    if (accion === 'cancelar' && !confirm('¿Cancelar esta solicitud?')) return;

    boton.disabled = true;
    try {
        await fetchJSON(`/api/solicitudes/${id}`, {
            method: 'PUT',
            body: JSON.stringify({ accion }),
        });
        mostrarMensaje(TEXTO_OK[accion]);
    } catch (error) {
        mostrarMensaje(textoError(error), 'error');
    }
    await cargar();
}

listaRecibidas.addEventListener('click', onClick);
listaEnviadas.addEventListener('click', onClick);

await cargar();
