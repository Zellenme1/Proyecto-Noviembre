// =============================================================================
// Utilidades compartidas por todos los módulos JS
// =============================================================================

/** Token CSRF que el servidor deja en <meta name="csrf-token">. */
export function csrfToken() {
    // Lee el token actualizado que protege las peticiones POST, PUT y DELETE.
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/**
 * fetch + JSON con manejo de errores común.
 * - Añade la cabecera X-CSRF-Token en toda petición que modifica datos.
 * - Si el servidor responde 401 (sesión caducada) lleva al login.
 * - Lanza Error con .status y .details (errores por campo) si la respuesta no es OK.
 */
export async function fetchJSON(url, options = {}) {
    // Centraliza las llamadas y convierte la respuesta a un error fácil de leer.
    const method = (options.method ?? 'GET').toUpperCase();
    const headers = { Accept: 'application/json', ...(options.headers ?? {}) };

    // Con FormData (subida de archivos) el navegador pone él mismo el Content-Type con su frontera.
    if (options.body !== undefined && !(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }
    if (method !== 'GET' && method !== 'HEAD') {
        headers['X-CSRF-Token'] = csrfToken();
    }

    const response = await fetch(url, { ...options, method, headers, credentials: 'same-origin' });

    let data = null;
    try {
        data = await response.json();
    } catch {
        // respuesta sin cuerpo JSON
    }

    if (!response.ok) {
        if (response.status === 401 && !location.pathname.startsWith('/login')) {
            window.location.assign('/login');
        }
        const error = new Error(data?.error ?? `Error HTTP ${response.status}`);
        error.status = response.status;
        error.details = data?.errores ?? null;
        throw error;
    }
    return data;
}

export function escapeHTML(valor) {
    // Cambia caracteres especiales para evitar que datos se interpreten como HTML.
    return String(valor ?? '').replace(/[&<>'"]/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;',
    }[c]));
}

/** Texto de error listo para mostrar (incluye los errores por campo si los hay). */
export function textoError(error) {
    return error.details
        ? `${error.message}: ${Object.values(error.details).join(' · ')}`
        : error.message;
}

/** Fecha de MySQL ("2026-11-20 09:00:00") en formato legible para el usuario. */
export function formatearFecha(valor) {
    // Convierte la fecha guardada en MySQL a un formato español.
    const fecha = new Date(String(valor ?? '').replace(' ', 'T'));
    return Number.isNaN(fecha.getTime())
        ? String(valor ?? '')
        : fecha.toLocaleString('es-ES', { dateStyle: 'medium', timeStyle: 'short' });
}

const ETIQUETAS_ESTADO = {
    SOLICITADO: 'Pendiente de respuesta',
    ACEPTADO: 'Aceptada',
    RECHAZADO: 'Rechazada',
    EN_EJECUCION: 'En curso',
    COMPLETADO: 'Completada',
    CANCELADO: 'Cancelada',
};

export function etiquetaEstado(estado) {
    return ETIQUETAS_ESTADO[estado] ?? estado;
}
