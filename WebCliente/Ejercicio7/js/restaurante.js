document.addEventListener("DOMContentLoaded", function () {
    let rest = document.getElementById("restaurante");
    let mesaActiva = null;

    if (rest) {
        rest.addEventListener("dblclick", crearMesa);
        rest.addEventListener("mousemove", moverMesa);
    }

    function crearMesa(evento) {
        if (evento.target !== rest) return;
        let mesa = document.createElement("div");
        mesa.className = "mesa";
        mesa.style.left = (evento.offsetX - 50) + "px";
        mesa.style.top = (evento.offsetY - 50) + "px";
        rest.appendChild(mesa);
    }

    function moverMesa(evento) {
        if (!evento.ctrlKey) {
            mesaActiva = null;
            return;
        }

        if (!mesaActiva && evento.target.classList.contains("mesa")) {
            mesaActiva = evento.target;
        }

        if (mesaActiva) {
            let caja = rest.getBoundingClientRect();
            let x = evento.clientX - caja.left - rest.clientLeft;
            let y = evento.clientY - caja.top - rest.clientTop;

            mesaActiva.style.left = (x - 50) + "px";
            mesaActiva.style.top = (y - 50) + "px";
        }
    }
});