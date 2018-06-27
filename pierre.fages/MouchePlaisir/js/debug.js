/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function PrintDebug(msg)
{
    var x = document.getElementById("div_debug");
    if (x !== null)
    {
        x.innerHTML = x.innerHTML + "<br/>" + msg;
    }
}

function perfoTag(msg, from, to)
{
    var delta = to - from;
    var x = document.getElementById("div_debug");
    if (x !== null)
    {
        x.innerHTML = x.innerHTML + "<br/>" + msg + " [duree: "+delta+"]";
    }
}
