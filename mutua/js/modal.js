function Modal(titulo){
    this.velo=document.createElement("div");
    this.modal=document.createElement("div");
    this.titulo=document.createElement("div");
    this.tituloTexto=document.createElement("div");
    this.cerrarModal=document.createElement("div");
    this.contenido=document.createElement("div");
    this.codigo=document.createElement("script");

    this.velo.id="velo";
    document.body.appendChild(this.velo);

    this.modal.id="modal";
    document.body.appendChild(this.modal);

    this.modal.appendChild(this.titulo);
    this.titulo.appendChild(this.tituloTexto);
    this.titulo.appendChild(this.cerrarModal);
    this.cerrarModal.innerHTML="X";
    this.modal.appendChild(this.contenido);
    
}


Modal.prototype.cerrar = function(){
    this.velo.remove();
    this.modal.remove();

};

Modal.prototype.cargarHTML = function(){

};

Modal.prototype.cargarUrl = function(){

};

Modal.prototype.cargarJS = function(){

};