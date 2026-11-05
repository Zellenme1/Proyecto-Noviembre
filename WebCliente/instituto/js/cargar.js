window.addEventListener("load", function(){
    cargarAlumnos();


    
    async function cargarAlumnos(){
        let ajxPlantilla = await fetch("plantillas/alumnosV2.pepe")
        let ajxDatos = await fetch("apirest/alumnos.json");
        let htmlPlantilla = await ajxPlantilla.text();
        let datosAlumnos = await ajxDatos.json();
        let contenedor = document.createElement("div");
        contenedor.innerHTML=htmlPlantilla;

        let alumnos = contenedor.querySelector(".alumnos");
        let modelo = contenedor.querySelector(".alumno");

        for (let i=0; i<datosAlumnos.length;i++){
            let nuevoAlumno = modelo.cloneNode(true);
            nuevoAlumno.querySelector(".id").innerHTML=datosAlumnos[i].id;
            nuevoAlumno.querySelector(".nombre").innerHTML=datosAlumnos[i].nombre;
            nuevoAlumno.querySelector(".dni").innerHTML=datosAlumnos[i].dni;
            nuevoAlumno.querySelector(".apellidos").innerHTML=datosAlumnos[i].apellidos;
            nuevoAlumno.querySelector(".curso").innerHTML=datosAlumnos[i].curso;
            alumnos.appendChild(nuevoAlumno);
        }
        modelo.remove();
        document.body.appendChild(contenedor);
    }
})  