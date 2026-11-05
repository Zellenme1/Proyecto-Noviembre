class Fila {
  constructor(datos) {
    this.tr = document.createElement("tr");

    // fila.celdas
    this.celdas = datos.map(d => {
      const td = document.createElement("td");
      td.textContent = d;
      this.tr.appendChild(td);
      return td;
    });

    this.editando = false;

    // columna con el botón Editar / Guardar
    const acciones = document.createElement("td");
    this.btn = document.createElement("button");
    this.btn.textContent = "Editar";
    this.btn.onclick = () => (this.editando ? this.guardar() : this.editar());
    acciones.appendChild(this.btn);
    this.tr.appendChild(acciones);
  }

  // fila.editar(): cada celda pasa a ser un input
  editar() {
    this.celdas.forEach(td => {
      const input = document.createElement("input");
      input.value = td.textContent;
      td.textContent = "";
      td.appendChild(input);
    });
    this.editando = true;
    this.btn.textContent = "Guardar";
  }

  // fila.guardar(): el valor del input vuelve a ser texto de la celda
  guardar() {
    this.celdas.forEach(td => {
      const input = td.querySelector("input");
      if (input) td.textContent = input.value;
    });
    this.editando = false;
    this.btn.textContent = "Editar";
  }

  // devuelve un array con los valores de la fila
  obtenerDatos() {
    return this.celdas.map(td => {
      const input = td.querySelector("input");
      return input ? input.value : td.textContent;
    });
  }
}

class Tabla {
  constructor(id) {
    this.el = document.getElementById(id);
    this.filas = [];
    this.asc = true;
  }

  // tabla.cargar(cabeceras, datos)
  // cabeceras: ["DNI", "Nombre", ...]   datos: [[...], [...], ...]
  cargar(cabeceras, datos) {
    this.el.innerHTML = "";
    this.filas = [];

    const filaCab = this.el.createTHead().insertRow();
    cabeceras.forEach((c, i) => {
      const th = document.createElement("th");
      th.textContent = c;
      th.onclick = () => this.ordenar(i, "texto");
      filaCab.appendChild(th);
    });
    filaCab.appendChild(document.createElement("th")); // columna de acciones

    this.tbody = this.el.createTBody();
    datos.forEach(d => {
      const fila = new Fila(d);
      this.filas.push(fila);
      this.tbody.appendChild(fila.tr);
    });
  }

  // tabla.ordenar(col, type)   type: "num" | "texto"
  ordenar(col, type = "texto") {
    const dir = this.asc ? 1 : -1;
    const valor = f => f.obtenerDatos()[col];

    this.filas.sort((a, b) =>
      type === "num"
        ? (valor(a) - valor(b)) * dir
        : String(valor(a)).localeCompare(String(valor(b))) * dir
    );

    this.asc = !this.asc;
    this.filas.forEach(f => this.tbody.appendChild(f.tr));
  }

  // tabla.obtenerDatos(): devuelve la matriz de datos
  obtenerDatos() {
    return this.filas.map(f => f.obtenerDatos());
  }
}

// ---- Uso ----
const tabla = new Tabla("alumnos");

tabla.cargar(["DNI", "Nombre", "Curso", "Nota"], [
  ["12345678A", "Ana", "1º DAW", "8"],
  ["87654321B", "Luis", "1º DAW", "6"],
  ["11223344C", "Marta", "2º DAW", "9"],
]);

document.getElementById("btnDatos").onclick = () => {
  document.getElementById("salida").textContent =
    JSON.stringify(tabla.obtenerDatos(), null, 2);
};
