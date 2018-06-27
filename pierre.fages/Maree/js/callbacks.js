//---------------------------------------------------------------------------
// implementation des callbacks de index.php
//---------------------------------------------------------------------------
var CookieContent="Seuil";
var CookieContentHeure="Heure";
var CookieDefVal = 1.0;
var CookieDefHVal = '12.00';

function RefreshSeuil()
{
	var child=document.getElementById("Hseuil");
	child.value = getValue(CookieContent, CookieDefVal);

	var childH=document.getElementById("Heure");
	childH.value = getValue(CookieContentHeure, CookieDefHVal);
	
	return true;
}

function modifieTexte() {
    var t2 = document.getElementById("t2");
    t2.firstChild.nodeValue = "trois";    
  }


function ChangeSeuil(evt)
{
	var SeuilText = document.getElementById("Hseuil");
	var seuilValue = SeuilText.value;
	setValue(CookieContent, seuilValue, 365);
	return true;
}

function ChangeHeure(evt)
{
	var HeureText = document.getElementById("Heure");
	var HeureValue = HeureText.value;
	setValue(CookieContentHeure, HeureValue, 365);
	return true;
}

function ChangePortAction(evt)
{
	var selectPortCombo = document.getElementById("lPorts");

	//--------------------------------------
	// Lorsque je change de port je dois changer les date de grande maree
	//--------------------------------------
	var selectTabInfoGdMaree = document.getElementById("GdMareeTableBody");	
	var selectPort = selectPortCombo.options[selectPortCombo.selectedIndex].value;
	getInfoGrandeMareeFromAjax (selectTabInfoGdMaree, selectPort);
	
    return true;
}

function ChangeDate(evt)
{
	var selectDate = document.getElementById("datepicker");
	//alert (selectDate.value);
	return true;
}

function getInfoFromDB(evt)
{
	var selectDate = document.getElementById("datepicker").value;
	var selectPortCombo = document.getElementById("lPorts");
	var selectPort = selectPortCombo.options[selectPortCombo.selectedIndex].value;
	var selectSeuil = document.getElementById("Hseuil").value;
	var selectHeure = document.getElementById("Heure").value;
	getInfoFromAjax(selectDate, selectPort, selectSeuil, selectHeure);

	//--------------------------------------
	// Lorsque je change de port je dois changer les date de grande maree
	//--------------------------------------
	var selectTabInfoGdMaree = document.getElementById("GdMareeTableBody");	
	getInfoGrandeMareeFromAjax (selectTabInfoGdMaree, selectPort);
	return true;
}

