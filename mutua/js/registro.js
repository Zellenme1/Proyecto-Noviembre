window.addEventListener("load", function() {
    let form = document.forms["RegistrarUsuario"];
    form["guardar"].onclick = function(event) {
        event.preventDefault();
        alert("Estoy ejecutandome");
    }
})