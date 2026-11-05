window.addEventListener("load",function(){
    fetch("plantillas/comienzo.html")
        .then(respuesta=>respuesta.text())
        .then(texto=>{
            document.body.innerHTML=texto
            let sct=document.createElement("script");
            document.head.appendChild(sct)
            sct.src="js/eltiempo.js"
        })
})