function crearModal(titulo, contenido){
    let div = document.createElement("div");

    // Estilos del modal
    
    document.body.appendChild(div);

    // Encabezado
    let divTitulo = document.createElement("div");

    divTitulo.style.display = "flex";
    divTitulo.style.justifyContent = "space-between";
    divTitulo.style.alignItems = "center";
    divTitulo.style.paddingBottom = "15px";
    divTitulo.style.borderBottom = "1px solid #CCCCCC";

    // Texto del título
    let divTextoTitulo = document.createElement("div");

    divTextoTitulo.innerHTML = titulo;
    divTextoTitulo.style.fontSize = "22px";
    divTextoTitulo.style.fontWeight = "bold";

    // Botón cerrar
    let divCerrar = document.createElement("div");

    divCerrar.innerHTML = "X";
    divCerrar.style.cursor = "pointer";
    divCerrar.style.fontSize = "20px";
    divCerrar.style.fontWeight = "bold";

    divCerrar.onclick = function(){
        div.velo.remove();
        div.remove();
    };

    divTitulo.appendChild(divTextoTitulo);
    divTitulo.appendChild(divCerrar);

    // Contenido
    let divContenido = document.createElement("div");

    divContenido.style.overflowY = "auto";
    divContenido.style.flex = "1";
    divContenido.style.paddingTop = "15px";

    while(contenido.length > 0){
        divContenido.appendChild(contenido[0]);
    }

    // Añadir elementos al modal
    div.appendChild(divTitulo);
    div.appendChild(divContenido);

    return div;
}