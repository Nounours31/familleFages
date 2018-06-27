//-----------------------------------------------------------
// memopattern pour memoriser les textbox si besoin lors des refresh
//-----------------------------------------------------------

function getValue(c_name, defVal)
{
	var value = window.sessionStorage[c_name];
	if ((value == null) || (value == ""))
		return defVal;
	return value;
}



function setValue(c_name,value,exdays)
{
	window.sessionStorage[c_name] = escape(value);
}


