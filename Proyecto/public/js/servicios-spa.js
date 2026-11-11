// =============================================================================
// Catálogo de servicios (admin): listar, crear, editar y eliminar vía fetch.
// =============================================================================

import { fetchJSON, escapeHTML, textoError } from './api.js';

const form = document.getElementById('form-servicio');
const lista = document.getElementById('app-servicios');
const mensaje = document.getElementById('mensaje');
const selectCategoria = document.getElementById('id_categoria');
const campoId = document.getElementById('id_servicio');
const tituloForm = document.getElementById('form-titulo');
const btnGuardar = document.getElementById('btn-guardar');
const btnCancelar = document.getElementById('btn-cancelar');

let servicios = [];

// -----------------------------------------------------------------------------
// Utilidades
// -----------------------------------------------------------------------------

function mostrarMensaje(texto, tipo = 'ok') {
    mensaje.textContent = texto;
    mensaje.dataset.tipo = tipo; // 'ok' | 'error' (para estilizar con CSS)
    mensaje.hidden = false;
}

// -----------------------------------------------------------------------------
// Carga y render
// -----------------------------------------------------------------------------

async function cargarCategorias() {
    const categorias = await fetchJSON('/api/categorias');
    selectCategoria.innerHTML =
        '<option value="">Selecciona una categoría</option>' +
        categorias
            .map((c) => `<option value="${c.id_categoria}">${escapeHTML(c.nombre)}</option>`)
            .join('');
}

async function cargarServicios() {
    lista.innerHTML = '<p>Cargando servicios…</p>';
    try {
        servicios = await fetchJSON('/api/servicios');
        renderizarLista();
    } catch (error) {
        lista.innerHTML = `<p role="alert">No se pudieron cargar los servicios: ${escapeHTML(error.message)}</p>`;
    }
}

function renderizarLista() {
    if (servicios.length === 0) {
        lista.innerHTML = '<p>Todavía no hay servicios en el catálogo. Crea el primero con el formulario.</p>';
        return;
    }

    lista.innerHTML = '<ul class="lista-servicios">' + servicios.map((s) => `
        <li id="servicio-${s.id_servicio}">
            <h4>${escapeHTML(s.nombre)}</h4>
            <p><strong>${escapeHTML(s.categoria_nombre)}</strong> · ${s.puntos_hora_referencia} puntos/hora</p>
            <p>${escapeHTML(s.descripcion)}</p>
            <button type="button" class="btn-editar" data-id="${s.id_servicio}">Editar</button>
            <button type="button" class="btn-eliminar" data-id="${s.id_servicio}">Eliminar</button>
        </li>`).join('') + '</ul>';
}

// -----------------------------------------------------------------------------
// Formulario: crear / editar
// -----------------------------------------------------------------------------

function resetFormulario() {
    form.reset();
    campoId.value = '';
    tituloForm.textContent = 'Nuevo servicio';
    btnGuardar.textContent = 'Crear servicio';
    btnCancelar.hidden = true;
}

function editarServicio(id) {
    const s = servicios.find((x) => x.id_servicio === id);
    if (!s) return;

    campoId.value = s.id_servicio;
    document.getElementById('nombre').value = s.nombre;
    document.getElementById('descripcion').value = s.descripcion ?? '';
    selectCategoria.value = String(s.id_categoria);
    document.getElementById('puntos_hora_referencia').value = s.puntos_hora_referencia;

    tituloForm.textContent = 'Editar servicio';
    btnGuardar.textContent = 'Guardar cambios';
    btnCancelar.hidden = false;
    form.scrollIntoView({ behavior: 'smooth' });
    document.getElementById('nombre').focus();
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const id = campoId.value;
    const payload = {
        nombre: document.getElementById('nombre').value,
        descripcion: document.getElementById('descripcion').value,
        id_categoria: parseInt(selectCategoria.value, 10),
        puntos_hora_referencia: parseInt(document.getElementById('puntos_hora_referencia').value, 10),
    };

    try {
        await fetchJSON(id ? `/api/servicios/${id}` : '/api/servicios', {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(payload),
        });
        mostrarMensaje(id ? 'Servicio actualizado correctamente' : 'Servicio creado correctamente');
        resetFormulario();
        await cargarServicios();
    } catch (error) {
        mostrarMensaje(textoError(error), 'error');
    }
});

btnCancelar.addEventListener('click', resetFormulario);

// -----------------------------------------------------------------------------
// Acciones de la lista (un solo listener por delegación de eventos)
// -----------------------------------------------------------------------------

lista.addEventListener('click', async (e) => {
    const boton = e.target.closest('button[data-id]');
    if (!boton) return;

    const id = parseInt(boton.dataset.id, 10);

    if (boton.classList.contains('btn-editar')) {
        editarServicio(id);
        return;
    }

    if (boton.classList.contains('btn-eliminar')) {
        const s = servicios.find((x) => x.id_servicio === id);
        if (!confirm(`¿Eliminar el servicio «${s?.nombre ?? id}»?`)) return;

        try {
            await fetchJSON(`/api/servicios/${id}`, { method: 'DELETE' });
            mostrarMensaje('Servicio eliminado correctamente');
            if (campoId.value === String(id)) resetFormulario();
            await cargarServicios();
        } catch (error) {
            mostrarMensaje(textoError(error), 'error');
        }
    }
});

// -----------------------------------------------------------------------------
// Arranque
// -----------------------------------------------------------------------------

try {
    await cargarCategorias();
} catch (error) {
    mostrarMensaje(`No se pudieron cargar las categorías: ${error.message}`, 'error');
}
await cargarServicios();
