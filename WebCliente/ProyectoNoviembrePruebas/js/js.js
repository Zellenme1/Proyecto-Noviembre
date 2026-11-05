window.addEventListener("load",function(){
    let form=document.forms["alumno"];
    let btn=form["enviar"];
    btn.onclick=function(ev){
        ev.preventDefault();
        annadirUsuario(form["nombre"].value,form["apellidos"].value)
                .then(respuesta=>{
                    if (respuesta.success){
                    form.reset();
                    window.location.reload();
                    }
                });


    }

        pedirUsuarios().then(objeto=>{
            if (objeto.success){
                for (let i=0; i<objeto.datos.length;i++){
                    insertarFila(objeto.datos[i].id, objeto.datos[i].nombre);
                }
            }
        })

        function insertarFila(id, nombre){
            let div=document.createElement("div");
            div.textContent=id + ". " + nombre;
            document.getElementById("listado").appendChild(div);
            div.onclick=programarBorrar(id);
        }

        function programarBorrar(id){
            return  function(){
                let objeto=this;
                borrarAlumno(id).then(respuesta=>{
                    if(respuesta.success){
                        objeto.remove();
                    }
                });
            }
        }

        async function  annadirUsuario(nombre, apellidos){
            let objeto={nombre:nombre,apellidos:apellidos};
            let peticion=await fetch("php/insertar.php",
                {
                    headers: {
                        'Content-Type' : 'application/json'
                    },
                    method:"post",
                    body: JSON.stringify(objeto)
                });
            let contenido = await peticion.json();
            return contenido;
        }

        async function pedirUsuarios(){
            let peticion=await fetch("php/alumnos.php");
            let contenido = await peticion.json();
            return contenido;
        }

        async function borrarAlumno(id){
            let peticion=await fetch("php/borrar.php?id=" + encodeURIComponent(id));
            let contenido=await peticion.json();
            return contenido;
        }
})