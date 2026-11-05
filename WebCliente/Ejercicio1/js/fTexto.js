/*
Funcion que devuelve las posiciones en las que se encuentra un texto dentro de otro.
*/
function buscarPosiciones(texto,busqueda) {
    let posicion = -1;
    let respuesta  = [];

    do {
        posicion = texto.indexOf(busqueda,posicion +1)
        if (posicion >= 0){
            respuesta.push(posicion)
        }
    } while (posicion != -1)
        return respuesta;
}