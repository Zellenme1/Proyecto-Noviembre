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
    if (tipo === 'recibidas' && (s.estado === 'ACEPTADO' || s.estado === 'EN_EJECUCION')) {
        return `<button type="button" data-accion="completar" data-id="${s.id_solicitud}" data-puntos="${s.puntos_totales}">Marcar como hecho</button>`;
    }
    if (tipo === 'enviadas' && (s.estado === 'SOLICITADO' || s.estado === 'ACEPTADO')) {
        return `<button type="button" class="btn-eliminar" data-accion="cancelar" data-id="${s.id_solicitud}">Cancelar solicitud</button>`;
    }
    return '';
}

function bloqueResena(s, tipo) {
    if (tipo !== 'enviadas' || s.estado !== 'COMPLETADO') return '';

    const existe = s.id_valoracion !== null;
    const puntuacion = Number(s.resena_puntuacion) || 0;
    const comentario = s.resena_comentario ? escapeHTML(s.resena_comentario) : '';
    const opciones = [1, 2, 3, 4, 5].map((valor) => `
        <option value="${valor}" ${puntuacion === valor ? 'selected' : ''}>${valor} de 5</option>`).join('');

    return `
        <section class="resena" aria-label="Tu reseña">
            ${existe ? `<p><strong>Tu reseña (${puntuacion}/5)</strong>${comentario ? `<br>${comentario}` : ''}</p>` : ''}
            <button type="button" data-resena-accion="${existe ? 'editar' : 'crear'}">
                ${existe ? 'Editar reseña' : 'Valorar servicio'}
            </button>
            <form class="form-resena" data-id="${s.id_solicitud}" data-method="${existe ? 'PUT' : 'POST'}" hidden>
                <label>
                    Puntuación
                    <select name="puntuacion" required>
                        ${existe ? opciones : '<option value="" selected disabled>Elige una puntuación</option>' + opciones}
                    </select>
                </label>
                <label>
                    Comentario (opcional)
                    <textarea name="comentario" maxlength="2000" rows="3">${comentario}</textarea>
                </label>
                <div class="acciones">
                    <button type="submit">${existe ? 'Guardar cambios' : 'Publicar reseña'}</button>
                    <button type="button" data-resena-accion="cancelar">Cancelar</button>
                </div>
            </form>
        </section>`;
}

function tarjeta(s, tipo) {
    const quien = tipo === 'recibidas' ? 'Cliente' : 'Profesional';
    const acciones = accionesDe(s, tipo);
    const horas = String(s.horas_estimadas).replace('.', ',');
    const fechaCompletado = s.fecha_completado
        ? `<p class="ticket-desc">Completada: ${escapeHTML(formatearFecha(s.fecha_completado))}</p>`
        : '';

    return `
        <li class="ticket">
            <div class="ticket-cuerpo">
                <h4>${escapeHTML(s.servicio_nombre)}</h4>
                <span class="estado" data-estado="${escapeHTML(s.estado)}">${escapeHTML(etiquetaEstado(s.estado))}</span>
                <p>${quien}: <strong>${escapeHTML(s.otra_persona)}</strong></p>
                <p class="ticket-desc">${escapeHTML(formatearFecha(s.fecha_propuesta))} · ${escapeHTML(horas)} h</p>
                ${fechaCompletado}
                ${bloqueResena(s, tipo)}
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
    completar: 'Servicio completado y puntos transferidos',
};

async function onClick(e) {
    const boton = e.target.closest('button[data-accion]');
    if (!boton) return;

    const { accion, id } = boton.dataset;

    if (accion === 'rechazar' && !confirm('¿Rechazar esta solicitud?')) return;
    if (accion === 'cancelar' && !confirm('¿Cancelar esta solicitud?')) return;
    if (accion === 'completar'
        && !confirm(`Al marcarlo como hecho, se transferirán ${boton.dataset.puntos} puntos del cliente a tu saldo. ¿Continuar?`)) return;

    boton.disabled = true;
    try {
        const resultado = await fetchJSON(`/api/solicitudes/${id}`, {
            method: 'PUT',
            body: JSON.stringify({ accion }),
        });
        if (accion === 'completar') {
            const saldo = document.querySelector('.saldo-pill strong');
            if (saldo) saldo.textContent = resultado.puntos_saldo;
        }
        mostrarMensaje(TEXTO_OK[accion]);
    } catch (error) {
        mostrarMensaje(textoError(error), 'error');
    }
    await cargar();
}

function onReviewClick(e) {
    const boton = e.target.closest('button[data-resena-accion]');
    if (!boton) return;

    const bloque = boton.closest('.resena');
    const formulario = bloque.querySelector('.form-resena');
    const activar = bloque.querySelector('button[data-resena-accion="crear"], button[data-resena-accion="editar"]');
    if (boton.dataset.resenaAccion === 'cancelar') {
        formulario.hidden = true;
        activar.hidden = false;
        return;
    }

    boton.hidden = true;
    formulario.hidden = false;
    formulario.querySelector('select').focus();
}

async function onReviewSubmit(e) {
    const formulario = e.target.closest('form.form-resena');
    if (!formulario) return;
    e.preventDefault();

    const boton = formulario.querySelector('button[type="submit"]');
    boton.disabled = true;
    try {
        const datos = new FormData(formulario);
        await fetchJSON(`/api/solicitudes/${formulario.dataset.id}/valoracion`, {
            method: formulario.dataset.method,
            body: JSON.stringify({
                puntuacion: Number.parseInt(datos.get('puntuacion'), 10),
                comentario: datos.get('comentario'),
            }),
        });
        mostrarMensaje(formulario.dataset.method === 'POST' ? 'Reseña publicada' : 'Reseña actualizada');
        await cargar();
    } catch (error) {
        mostrarMensaje(textoError(error), 'error');
        boton.disabled = false;
    }
}

listaRecibidas.addEventListener('click', onClick);
listaEnviadas.addEventListener('click', onClick);
listaEnviadas.addEventListener('click', onReviewClick);
listaEnviadas.addEventListener('submit', onReviewSubmit);

await cargar();
