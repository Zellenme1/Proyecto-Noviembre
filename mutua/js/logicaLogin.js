window.addEventListener("load", function() {

    let btnRegistrar = document.getElementById("btnRegistrar");
    btnRegistrar.onclick = function(ev) {
        ev.preventDefault();1
        // traerPlantillaRegistro();
        cargarModal("Registro Usuario", "plantillas/registro.html", "js/registro.js");
    }

    async function traerPlantillaRegistro() {
        let peticion = await fetch("plantillas/registro.html");
        if (peticion.ok) { 
            let texto = await peticion.text();
            let div = document.createElement("div");
            div.innerHTML = texto;
            crearModal("Registro", [div.children]);
        } else {
            throw "error al traer la plantilla de registro";
        }
    }

    async function cargarModal(titulo, urlPlantilla, urlJs){
        let petPlantilla = await fetch(urlPlantilla);
        let petJs = await fetch(urlJs);
        if (petPlantilla.ok && petJs.ok) {
            let contenido = await petPlantilla.text();
            let contenidoJs = await petJs.text();
            let div = document.createElement("div");
            div.innerHTML = contenido;
            let script = document.createElement("script");
            script.innerHTML = contenidoJs;
            div.appendChild(script);
            crearModal(titulo, div.children);
        }else {
            throw "error al servidor";
        }
    }
})