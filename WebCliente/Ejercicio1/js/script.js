// Capturar los elementos HTML implicados (los ID) PRIMER PASO
const texto = document.getElementById("texto");
const palabra = document.getElementById("palabra");
const boton = document.getElementById("boton");
const resultado = document.getElementById("resultado");

boton.onclick = function() {
    let contenido = texto.value;
    let busqueda = palabra.value;

    let posiciones = buscarPosiciones(contenido,busqueda);
    resultado.innerHTML = posiciones;
}
