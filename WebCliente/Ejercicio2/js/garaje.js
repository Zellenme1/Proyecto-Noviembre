const coches = [];

const matricula = document.getElementById("matricula");
const color = document.getElementById("color");
const puertas = document.getElementById("puertas");
const annio = document.getElementById("annio");

const annadir = document.getElementById("annadir");
const limpiar = document.getElementById("limpiar");

const mostrar = document.getElementById("lista-coches");
const ordenarMatricula = document.getElementById("ordenarMatricula");
const ordenarColor = document.getElementById("ordenarColor");
const ordenarPuertas = document.getElementById("ordenarPuertas");
const ordenarAnnio = document.getElementById("ordenarAnnio");

annadir.onclick = function () {
    coches.push({
        matricula: matricula.value,
        color: color.value,
        puertas: Number(puertas.value),
        annio: Number(annio.value)
    });
    matricula.value = "";
    color.value = "";
    puertas.value = "";
    annio.value = "";

    matricula.focus();

    mostrarCoches();
}

limpiar.onclick = function () {
    limpiarTable();
}

ordenarMatricula.onclick = function () {
    coches.sort((a, b) => a.matricula.localeCompare(b.matricula));
    mostrarCoches();
}

ordenarColor.onclick = function () {
    coches.sort((a, b) => a.color.localeCompare(b.color));
    mostrarCoches();
}

ordenarPuertas.onclick = function () {
    coches.sort((a, b) => a.puertas - b.puertas);
    mostrarCoches();
}

ordenarAnnio.onclick = function () {
    coches.sort((a, b) => a.annio - b.annio);
    mostrarCoches();
}

function limpiarTable() {
    coches.length = 0;
    mostrar.innerHTML = "";
}

function mostrarCoches() {
    
    mostrar.innerHTML = "";
    for (let i = 0; i < coches.length; i++) {
        mostrar.innerHTML += `
        <tr>
            <td>${coches[i].matricula}</td>
            <td>${coches[i].color}</td>
            <td>${coches[i].puertas}</td>
            <td>${coches[i].annio}</td>
        </tr>
        `;
    }
}