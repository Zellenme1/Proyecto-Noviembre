import { fetchJSON, escapeHTML, formatearFecha } from './api.js';

const seccion = document.getElementById('resenas');
const resumen = document.getElementById('resumen-resenas');
const lista = document.getElementById('lista-resenas');

// 4 -> "★★★★☆"
function estrellas(n) {
    return '★'.repeat(n) + '☆'.repeat(5 - n);
}

function pintarResumen({ media, total }) {
    if (total === 0) {
        resumen.innerHTML = '';
        return;
    }
    const mediaTexto = media.toLocaleString('es-ES', { minimumFractionDigits: 1 });
    resumen.innerHTML = `
        <p class="media-total">
            <span class="estrellas" aria-hidden="true">${estrellas(Math.round(media))}</span>
            <strong>${mediaTexto}</strong> de 5 según ${total} ${total === 1 ? 'reseña' : 'reseñas'}
        </p>`;
}

function pintarLista(resenas) {
    if (resenas.length === 0) {
        lista.innerHTML = '<p>Todavía no hay reseñas.</p>';
        return;
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
    try {
        const datos = await fetchJSON(`/api/profesionales/${seccion.dataset.profesional}/resenas`);
        pintarResumen(datos.resumen);
        pintarLista(datos.resenas);
    } catch (error) {
        lista.innerHTML = `<p role="alert">No se pudieron cargar las reseñas: ${escapeHTML(error.message)}</p>`;
    }
}

if (seccion) {
    await cargar();
}