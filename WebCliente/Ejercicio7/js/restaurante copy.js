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
        mesa.addEventListener("wheel", cambiarTamano);
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
            let tamanoMesa = mesaActiva.offsetWidth;
            let nuevoX = mesaActiva.offsetLeft + evento.movementX;
            let nuevoY = mesaActiva.offsetTop + evento.movementY;

            nuevoX = Math.max(0, Math.min(nuevoX, 1000 - tamanoMesa));
            nuevoY = Math.max(0, Math.min(nuevoY, 1000 - tamanoMesa));

            mesaActiva.style.left = nuevoX + "px";
            mesaActiva.style.top = nuevoY + "px";
        }
    }

    function cambiarTamano(evento) {
        evento.preventDefault();
        let tamano = this.clientWidth;
        let incTamano = 20 * Math.sign(evento.wheelDelta);
        let nuevoTamano = tamano + incTamano;
        if (nuevoTamano >= 100 && nuevoTamano <= 400) {
            this.style.width = nuevoTamano + "px";
            this.style.height = nuevoTamano + "px";
        }
    }
});