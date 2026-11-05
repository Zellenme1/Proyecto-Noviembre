window.addEventListener("load", function () {

    let select = document.getElementById("provincias");
    let btnCargar = document.getElementById("btnCargar");
    let display = document.getElementById("display");

    btnCargar.addEventListener("click", function () {
        fetch("https://api.el-tiempo.net/json/v3/provincias")
            .then(respuesta => respuesta.json())
            .then(datos => {
                for (let i = 0; i < datos.provincias.length; i++) {
                    let opt = document.createElement("option");
                    opt.value=datos.provincias[i].CODPROV;
                    opt.innerText = datos.provincias[i].NOMBRE_PROVINCIA;
                    opt.onclick=pedirDatosProvincia;
                    select.appendChild(opt);
                }
            })
    })


    function pedirDatosProvincia(){
        let codProv=this.value;
        fetch("https://api.el-tiempo.net/json/v3/provincias/"+codProv)
        .then(respuesta=>respuesta.json())
        .then(datos=>{
            let ciudades=datos.ciudades;
            for(leti=0;i<ciudades.length;i++){
                let ciudad=ciudades[i].name;
                let min=ciudades[i].temperatures.min;
                let max=ciudades[i].temperatures.min;
                let parrafo=document.createElement("p");
                parrafo.innerHTML=`
                    <p><span class="ciudad">$</span>
                        <span class="min">${min}</span>
                        <span class="min">${max}</span>
                    </p>`
                display.appendChild(parrafo)
            }
        })
    }

});