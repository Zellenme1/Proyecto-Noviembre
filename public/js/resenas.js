import { fetchJSON, escapeHTML, formatearFecha } from './api.js';

const seccion = document.getElementById('resenas');
const resumen = document.getElementById('resumen-resenas');
const lista = document.getElementById('lista-resenas');

// 4 -> "★★★★☆"
function estrellas(n) {
    // Repite estrellas llenas y vacías según la puntuación.
    return '★'.repeat(n) + '☆'.repeat(5 - n);
}

function pintarResumen(datos) {
    // Valida la respuesta y muestra la nota media.
    if (datos === null) {
        resumen.innerHTML = '';
        return;
    }

    if (!datos || datos.total == null) {
        throw new Error('La respuesta del servidor no contiene un resumen válido.');
    }

    const total = Number(datos.total);
    if (!Number.isFinite(total)) {
        throw new Error('La respuesta del servidor contiene un resumen no válido.');
    }

    if (total === 0) {
        resumen.innerHTML = '';
        return;
    }

    const media = Number(datos.media);
    if (!Number.isFinite(media)) {
        throw new Error('La respuesta del servidor contiene una media no válida.');
    }

    const mediaTexto = media.toLocaleString('es-ES', { minimumFractionDigits: 1 });
    resumen.innerHTML = `
        <p class="media-total">
            <span class="estrellas" aria-hidden="true">${estrellas(Math.round(media))}</span>
            <strong>${mediaTexto}</strong> de 5 según ${total} ${total === 1 ? 'reseña' : 'reseñas'}
        </p>`;
}

function pintarLista(resenas) {
    // Crea una fila por cada reseña recibida.
    if (resenas === null || (Array.isArray(resenas) && resenas.length === 0)) {
        lista.innerHTML = '<p>No hay reseñas.</p>';
        return;
    }
    if (!Array.isArray(resenas)) {
        throw new Error('La respuesta del servidor no contiene una lista válida de reseñas.');
    }

    lista.innerHTML = '<ul class="lista-resenas">' + resenas.map((r) => `
        <li>
            <p>
                <span class="estrellas" aria-hidden="true">${estrellas(r.puntuacion)}</span>
                <span class="solo-lectores">${r.puntuacion} de 5.</span>
                ${r.comentario ? escapeHTML(r.comentario) : '<em>Sin comentario</em>'}
            </p>
            <p class="ayuda">${escapeHTML(r.autor)} sobre ${escapeHTML(r.servicio)}, ${escapeHTML(formatearFecha(r.fecha))}</p>
        </li>`).join('') + '</ul>';
}

async function cargar() {
    // Pide el resumen y las reseñas del profesional.
    try {
        const datos = await fetchJSON(`/api/profesionales/${seccion.dataset.profesional}/resenas`);
        if (!datos || typeof datos !== 'object') {
            throw new Error('La respuesta del servidor no es válida.');
        }
        pintarResumen(datos.resumen);
        pintarLista(datos.resenas);
    } catch (error) {
        lista.innerHTML = `<p role="alert">No se pudieron cargar las reseñas: ${escapeHTML(error.message)}</p>`;
    }
}

if (seccion) {
    await cargar();
}