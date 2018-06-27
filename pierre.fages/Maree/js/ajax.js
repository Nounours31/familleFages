
function getInfoFromAjax(Date, Port, Seuil, Heure)
{
	var xmlhttp=new XMLHttpRequest();

	xmlhttp.onreadystatechange=function()
	{
		var insideTable = document.getElementById("TableBody");
		if (xmlhttp.readyState==4 && xmlhttp.status==200 && (insideTable != null))
	    {
			if (insideTable != null)
				insideTable.innerHTML=xmlhttp.responseText;
	    }
		else
		{
			if (insideTable != null)
				insideTable.innerHTML="xmlhttp a foirer";
		}
	}
	
	xmlhttp.open("POST","php/BuildDBReponse.php",true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send("Date=" + Date + "&Port=" + Port + "&Seuil=" + Seuil+ "&Heure=" + Heure);
}


function getInfoGrandeMareeFromAjax(insideTable, Port)
{
	var xmlhttp=new XMLHttpRequest();

	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4 && xmlhttp.status==200 && (insideTable != null))
	    {
			if (insideTable != null)
				insideTable.innerHTML=xmlhttp.responseText;
	    }
		else
		{
			if (insideTable != null)
				insideTable.innerHTML="<tr><td>Calcul en cours</td></tr>";
		}
	}
	
	xmlhttp.open("POST","php/BuildDBReponseGDMaree.php",true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send("Port=" + Port);
}
