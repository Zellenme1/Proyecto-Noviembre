window.addEventListener("load",function(){
    let botonesPestana=document.querySelectorAll("#pestana>button");
    let pestanas = document.getElementById("contenedor").children;
    let tamano=btnPestana.length;
    for(let i=0; i<botonesPestana.length;i++){
        botonesPestana[i].onclick=pulsado(i);
    }

    function pulsado(i){
        return function(){
            for(let j=0;j<tamano;j++){
                btnPestana[j].disabled=false;
                pestanas[j].className="oculto";

            }
            btnPestana[i].disabled=true;
            pestanas[i].className="";
        }
    }

})