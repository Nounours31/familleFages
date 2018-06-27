/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function request(callback, divId, Url, uidSortie, uidPersone) {
    var xhr = getXMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
            callback(xhr.responseText, divId);
        }
    };
	
    var bAsync = true;
    xhr.open("POST", Url, bAsync);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("uid_sortie="+uidSortie+"&uid_persone="+uidPersone);
}

function callbackInscription(sReponse, divId) {
    if (sReponse == null)
        return;
    
    var x = sReponse.replace(/\n|\r/gm,'');
    var OKResponse = x.indexOf("200: ");
    var msg = x.substr(5);
    
    var div = document.getElementById(divId);
    if (div !== null)
        div.innerHTML = msg;
    
    if (OKResponse === 0)
    {
         div.innerHTML = div.innerHTML + "<br/>Mise a jour de la page en cours ...";
         window.location.reload();
    }
}

