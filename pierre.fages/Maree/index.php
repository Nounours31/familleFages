<!DOCTYPE html>

<html>
<head>
	<meta charset="ISO-8859-1"/>
	<title>Fiche maree</title>
	
	<link rel="stylesheet" type="text/css" href="css/table.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
	
 	<link rel="Stylesheet"  type="text/css" href="jquery-ui-1.10.3/themes/base/jquery-ui.css" />  
 	<script type="text/javascript" src="jquery/jquery-2.0.2.js"></script>
 	<script type="text/javascript" src="jquery-ui-1.10.3/ui/jquery-ui.js"></script>

 	<script type="text/javascript" src="js/ajax.js"></script>
 	<script type="text/javascript" src="js/callbacks.js"></script>
 	<script type="text/javascript" src="js/memopattern.js"></script>
 	<script type="text/javascript" src="js/initJQuery.js"></script>
 	<?php
	 	include_once 'php/db_maree.php';
 	?>
</head>

<body onload="return RefreshSeuil();" style="font-family: Cambria,Georgia,Serif; font-size:100%">
<form action="">
	Port:
		<select id="lPorts" name="lPorts" onchange="return ChangePortAction();">
<?php 
				//------------------------------------------------------------------------------
				// Recup de la liste des ports dispo et mise dans le combo
				// Par defaut StQuay
				//------------------------------------------------------------------------------
				$lPortDispo = getAvailablePort();
				foreach ($lPortDispo as $port)
				{
					if (strcmp($port, "StQuay") != 0)
						print ('<option value="'.$port.'" >'.$port.'</option>');
					else
						print  ('<option selected="yes" value="'.$port.'" >'.$port.'</option>');
				}
				//-------------------------------------------------------------------------------
?>					
		</select>
				
				
	Date : 
<?php 
				//------------------------------------------------------------------------------
				// Recup de la date du jour par defaut
				//------------------------------------------------------------------------------
				print ('<input type="text" id="datepicker" onchange="return ChangeDate();" value="'.date('m/d/Y').'">');
				//-------------------------------------------------------------------------------
?>					
		<input type="button" id="form_sendbutton" name="BT_Envoyer" value="Envoyer" onclick="return getInfoFromDB();"/>
					
					
	H seuil(m):
		<input id="Hseuil" type="text" name="Hseuil" value="1.0" onchange="return ChangeSeuil();">
					
	Heure (hh.mm):
		<input id="Heure" type="text" name="Heure" value="12.00" onchange="return ChangeHeure();">


		<br/>
		<hr/>
		<table cellspacing="0" cellpadding="0"> 
		 <caption id="TblCap" style="font-size:150%">
		 Info Maree
		 </caption>
		 <thead> 
			<tr> 
			 <th id="J-1">J-1</th> 
			 <th colspan="10"> J </th> 
			 <th id="Jx1">J+1</th> 
			</tr> 
		 </head> 
		 <tbody id="TableBody">
		 </tbody> 
		</table>
	</form>
	

	<br/>
	<br/>
	<hr/>
	<table cellspacing="0" cellpadding="0"> 
		 <caption id="TblCap" style="font-size:150%">
		 Info Grandes Marees (H < 1m)
		 </caption>
		 <tbody id="GdMareeTableBody">
		 </tbody> 
		</table>
</body>
</html>