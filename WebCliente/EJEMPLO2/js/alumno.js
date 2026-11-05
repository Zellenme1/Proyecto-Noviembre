/*
Objecto de la clase Alumno con toda la logica 
*/

// Constructor
/*
Documento el construtor parametros de entrada
y objecto creado.
*/
function Alumno(nombre, apellidos, dni, FNacimiento, curso){
    this.alta=new Date();
    this.nombre=nombre;
    this.apelidos=apellidos;
    this.dni=dni;
    this.FNacimiento=FNacimiento;
    this.curso=curso||"";
}


Alumno.prototype.nombreCompleto=function(){
    return this.nombre+" "+this.apelidos;
}

Alumno.prototype.matricular=function(curso){
    this.curso=curso;
}

Alumno.prototype.cambiarNombre=function(nombre){
    this.nombre=nombre;
}

