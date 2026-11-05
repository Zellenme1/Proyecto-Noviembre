const display = document.getElementById("hora");
imprimeHora();
setInterval(imprimeHora, 500);
function imprimeHora(){
    const hora=new Date();
    display.innerHTML=dosDig(hora.getHours()) +
                    ":" + dosDig(hora.getMinutes()) + 
                    ":" + dosDig(hora.getSeconds());
}

function dosDig(num){
    return ((num<10) ? "0":"")+num;
}