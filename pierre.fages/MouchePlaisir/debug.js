/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function AddToDebugDiv(msg)
{
    var x = document.getElementById("div_debug");
    var text = x.innerHTML + "<br/>" + msg;
    x.innerHTML = text;
}

