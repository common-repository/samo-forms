__SAIDA__

function evalScripts(obj){
    scripts = obj.getElementsByTagName('script');
    for(var iScript=0; iScript<scripts.length; iScript++){
        var itn = obj.getElementsByTagName('script').item(iScript);
        var src = itn.getAttribute('src') || '';
        
        if( src != '' ){
            (function(d, s) {
                var js, SF = d.getElementsByTagName(s)[0];
                var id = 'SAMOFORM-LoadScript-' +iScript;
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = src;
                SF.parentNode.insertBefore(js, SF);
            }(document, 'script'))
        }else{
            try{
                eval( itn.innerHTML );
            } catch(e){
                console.table(itn.innerHTML);
                console.log(e);
            }
             
        }
        
    }
    //alert( obj.getElementsByTagName('script')[0].getAttribute('src') );
}

function SendForm__ID__(elm){
    //alert('Sucesso, amanhã a gente acaba!');

    var bkp = elm.innerHTML;
    elm.innerHTML = 'Aguarde...';
    
    var request = new XMLHttpRequest();
    var f = document.getElementById('SAMOFORM-FORM-__ID__');
    var data = new FormData(f);

    request.open('POST', '__URL__', true);
    request.onload = function (e) {
        if (request.readyState == 4) 
        {
            // Check if the get was successful.
            if (request.status != 200) 
            {
                console.error(request.statusText);
            } 
            else 
            {
                try{
                    result = eval('('+request.responseText+')');
                    formMessage = document.getElementById('form-message-__ID__');
                    formMessage.innerHTML = result.message;
                    formMessage.style.display = 'block';
                    f.reset();
                }
                catch(e){
                    console.log(request.responseText);
                    console.error(e);
                }
                
            }
            elm.innerHTML = bkp;
        }
    };

    // Catch errors:
    request.onerror = function (e) { console.error(request); console.table(e);  };
    request.send(data);
}

var div = document.getElementById('SAMOFORM-root-__ID__');
try{
    console.log('__URL__');
    var idForm = 'SAMOFORM-FORM-__ID__';
    if (!document.getElementById(idForm)){
        f = document.createElement("form"); 
        f.id = idForm;
        f.action = '__URL__';
        f.method = 'POST';
        f.target = '_blank';
        f.innerHTML = "<p id='form-message-__ID__' style='display:none;'>&nbsp;</p>";
        f.innerHTML+= saida;
        //div.parentNode.insertBefore(f, div);
        div.innerHTML = '';
        div.appendChild(f);
        
        evalScripts(document.getElementById(idForm));
    }
}catch(e){
    console.log(e);
    div.innerHTML = 'Erro na geração do fomulário!';
}