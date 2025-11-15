//reloj funcion 
var actualizarHora = function(){
    
    var fecha = new Date(),
        horas = fecha.getHours(),
        minutos = fecha.getMinutes(),
        amPm,
        segundos = fecha.getSeconds()
     
        
            var pHoras = document.getElementById("hora"),
            pMinutos = document.getElementById("minutos"),
            pAmPm = document.getElementById("ampm"),
            pSegundos = document.getElementById("segundos")
     

    if(horas > 12){
        amPm = "PM";
    }else{
        amPm = "AM";
    }
    
    if(minutos < 10){
        minutos = "0" + minutos;
    }
    if(segundos < 10){
            segundos = "0" + segundos;
    }


        pHoras.textContent = horas,
        pMinutos.textContent = minutos,
        pSegundos.textContent = segundos


}

actualizarHora();
setInterval(actualizarHora,1000);

    
    