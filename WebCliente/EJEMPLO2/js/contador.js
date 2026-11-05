siguiente=function(){
    let a=0;
    return function(){
        a++;
        return a;
    }
}()