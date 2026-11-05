function crearVelo(){
    let div=document.createElement("div");
    div.style.position="fixed";
    div.style.top="0";
    div.style.left="0";
    div.style.width="100%";
    div.style.height="100%";
    div.style.backgroundColor="rgba(150,150,150,0.3)";
    div.style.zIndex="999";
    document.body.appendChild(div);
    return div;
}