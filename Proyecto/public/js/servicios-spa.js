document.addEventListener('DOMContentLoaded', () => {
    const appContainer = document.getElementById('app-servicios');
    const formServicio = document.getElementById('form-servicio');

    // =========================================================================
    // 1. OBTENER Y RENDERIZAR SERVICIOS (GET /api/servicios)
    // =========================================================================
    async function cargarServicios() {
        appContainer.innerHTML = '<p>Cargando servicios...</p>';

        try {
            const response = await fetch('/api/servicios');
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const servicios = await response.json();
            renderizarLista(servicios);
        } catch (error) {
            console.error('Fetch Error:', error);
            appContainer.innerHTML = `<p style="color:red;">Error al cargar datos: ${error.message}</p>`;
        }
    }

    function renderizarLista(servicios) {
        if (servicios.length === 0) {
            appContainer.innerHTML = '<p>No hay servicios registrados actualmente.</p>';
            return;
        }

        let html = '<ul class="lista-servicios">';
        servicios.forEach(s => {
            html += `
                <li id="servicio-${s.id_servicio}" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                    <h4>${escapeHTML(s.nombre)} <small>(Cat ID: ${s.id_categoria})</small></h4>
                    <p>${escapeHTML(s.descripcion)}</p>
                    <button class="btn-eliminar" data-id="${s.id_servicio}">Eliminar</button>
                </li>
            `;
        });
        html += '</ul>';

        appContainer.innerHTML = html;
        asignarEventosEliminar();
    }

    // =========================================================================
    // 2. CREAR NUEVO SERVICIO (POST /api/servicios)
    // =========================================================================
    if (formServicio) {
        formServicio.addEventListener('submit', async (e) => {
            e.preventDefault();

            const payload = {
                nombre: document.getElementById('nombre').value,
                descripcion: document.getElementById('descripcion').value,
                id_categoria: parseInt(document.getElementById('id_categoria').value, 10)
            };

            try {
                const response = await fetch('/api/servicios', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const resultado = await response.json();

                if (response.ok) {
                    formServicio.reset();
                    cargarServicios();
                } else {
                    alert(`Error: ${resultado.error || 'No se pudo crear el servicio'}`);
                }
            } catch (error) {
                console.error('Error al crear:', error);
                alert('Error de conexión al guardar el servicio');
            }
        });
    }

    // =========================================================================
    // 3. ELIMINAR SERVICIO (DELETE /api/servicios/{id})
    // =========================================================================
    function asignarEventosEliminar() {
        const botones = document.querySelectorAll('.btn-eliminar');
        botones.forEach(boton => {
            boton.addEventListener('click', async (e) => {
                const id = e.target.getAttribute('data-id');

                if (!confirm(`¿Confirmas la eliminación del servicio con ID ${id}?`)) {
                    return;
                }

                try {
                    const response = await fetch(`/api/servicios/${id}`, {
                        method: 'DELETE'
                    });

                    if (response.ok) {
                        document.getElementById(`servicio-${id}`).remove();
                    } else {
                        const errorData = await response.json();
                        alert(`Error al eliminar: ${errorData.error}`);
                    }
                } catch (error) {
                    console.error('Error al borrar:', error);
                }
            });
        });
    }

    function escapeHTML(str) {
        return String(str ?? '').replace(/[&<>'"]/g, 
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    }

    cargarServicios();
});