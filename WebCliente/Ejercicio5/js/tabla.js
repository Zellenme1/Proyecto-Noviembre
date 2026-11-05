//Metodos propios de fila
//fila.cargar(vector)
//En la fila se crean todos los TDs necesarios
//para albergar el vector
HTMLTableRowElement.prototype.cargar=function(v){
    let tamano=v.length;
    for (let i=0; i<v.length; i++){
        let td=document.createElement("td");
        td.innerHTML=v[i];
        this.appendChild(td);
    }
}
//Elimina todos los td de la fila
//fila.vaciar()
HTMLTableRowElement.prototype.VACIAR = function(){
    this.innerHTML="";
}

//Cargar un array con un conjunto de datos
//los datos se encuentran en un array bidimensional
HTMLTableElement.prototype.cargar=function(datos, ntBody=0){
    let tBody
    if (this.tBodies.length==0){
        tBody=document.createElement("tbody");
        this.appendChild(tBody);
    } else {
        tBody = this.tBodies[ntBody];
    }
    let tamano = datos.length;
    for (let i=0; i<tamano; i++){
        let tr=document.createElement("tr");
        tr.cargar(datos[i]);
        tBody.appendChild(tr);
    }   
}

//tabla ordenar (columna, typeOrder=0, orden=1)
//columna indice de la columna empezando en 0
//typeorder indice del sihuiente vecotr ["lexicografico","numerico"]
// "fecha"]
//orden 1 = creciente, -1 = decreciente

HTMLTableElement.prototype.ordenar=function(columna, typeOrder=0, orden=1){   
    let punteros=[]
    let tBody=this.tBodies[0];
    for (let i=0; i<tBody.rows.length; i++)                                   
        punteros.push(tBody.rows[i]);
    switch (typeOrder){
        case 1:
            punteros.sort(function(col, creciente){
                return function(a,b){
                    // FIX: numérico, se resta (localeCompare compara texto)
                    return creciente * (a.cells[col].textContent - b.cells[col].textContent);
                }
            }(columna, orden))
            break;      
        case 2:
            punteros.sort(function(col, creciente){
                return function(a,b){
                    return creciente * (new Date(a.cells[col].textContent) - new Date(b.cells[col].textContent));
                }
            }(columna, orden))
            break;
        default:
            punteros.sort(funcionOrdenadoraCadena(columna, orden))
    }

    for (let i=0; i<punteros.length; i++)
        tBody.appendChild(punteros[i]);

}


function funcionOrdenadoraCadena(col, creciente=1){                           
    return function(a,b){
        // FIX: se usa textContent de ambas celdas y se aplica el sentido
        return creciente * a.cells[col].textContent.localeCompare(b.cells[col].textContent);
    }
}