<?php
include_once($_SERVER['DOCUMENT_ROOT']."/bricolage/include/ServeurFonctions.php");
include_once($_SERVER['DOCUMENT_ROOT']."/bricolage/include/Traces.php");

// ----------------------------------------------
// Info db mysql
// create database brico_test;
// use brico_test;
// ---------------------------------------------
function connectMaBase()
{
	
/********************************************
  	$Host = "bricolage.ceds.sql.free.fr";
	$User = "bricolage.ceds";
	$Password = "gcnpfs31";
	$BaseName= "bricolage_ceds";
*********************************************/
	$Host = GetHostForDump ();
	$User = GetSQLUser ();
	$Password = GetSQLPassword();
	$BaseName= GetSQLDbName ();
	
	$ServeurName = GetServeurName();
	//AddInGlobalTrace ("[".date('d/m/Y-H:i:s')."] connectMaBase ServeurName=".$ServeurName);
	if (strcmp ($ServeurName, 'free') != 0)
	{
		$Host = "localhost";
		$User = GetSQLUser ();
		$Password = GetSQLPassword();
		$BaseName= GetSQLDbName ();
	}
	$base = 0;
    $base = mysql_connect ($Host, $User, $Password) or die ("Impossible de se connecter : ".mysql_error());
	//AddInGlobalTrace ("[".date('d/m/Y-H:i:s')."] connectMaBase base=".$base);
    mysql_select_db ($BaseName, $base) ;
    return $base;
}




function getUserIdFromMatricule( $Matricule)
{
	$base = connectMaBase ();
	$sql = 'SELECT UID, Matricule, PasswdMD5 FROM user WHERE Matricule='.$Matricule;
	$db_messages = mysql_query($sql);

	if (empty ($db_messages))
		return 0;
		
	if (mysql_num_rows ($db_messages) == 0)
		return 0;
		
	while($message = mysql_fetch_array($db_messages))
	{
		$UID = $message['UID'];
		$Matricule = $message['Matricule'];
		$PasswdMD5 = $message['PasswdMD5'];
	}
	mysql_close($base);
	return $UID;
}

function getMatriculeFromId( $Id)
{
	$base = connectMaBase ();
	$sql = 'SELECT Matricule FROM user WHERE UID='.$Id;
	$db_messages = mysql_query($sql);

	$Trigram = 0;
	while($message = mysql_fetch_array($db_messages))
	{
		$Trigram = $message['Matricule'];
	}
	mysql_close($base);
	return $Trigram;
}



function getPasswdFromMatricule( $Matricule)
{
	$base = connectMaBase ();
	$sql = 'SELECT UID, Matricule, PasswdMD5 FROM user WHERE Matricule='.$Matricule;
	$db_messages = mysql_query($sql);

	while($message = mysql_fetch_array($db_messages))
	{
		$UID = $message['UID'];
		$Matricule = $message['Matricule'];
		$PasswdMD5 = $message['PasswdMD5'];
	}
	mysql_close($base);
	return $PasswdMD5;
}

	
function setPasswdFromMatricule( $Matricule,  $MD5PasswdNew)
{
	$base = connectMaBase ();
	$sql = "update user set PasswdMD5='".$MD5PasswdNew."' WHERE Matricule=".$Matricule;
	$db_messages = mysql_query($sql);
	mysql_close($base);
}


function getNomFromMatricule( $Matricule)
{
	$base = connectMaBase ();
	$sql = 'SELECT Nom FROM user WHERE Matricule='.$Matricule;
	$db_messages = mysql_query($sql);

	$message = mysql_fetch_array($db_messages);
	
	$Nom = $message['Nom'];
	mysql_close($base);
	return $Nom;

}

function getemailFromMatricule( $Matricule)
{
	$base = connectMaBase ();
	$sql = 'SELECT email, email_perso FROM user WHERE Matricule='.$Matricule;
	$db_messages = mysql_query($sql);

	$message = mysql_fetch_array($db_messages);
	
	$Email = $message['email'];
	DebugTrace ("getemailFromMatricule : email=".$Email);
	if (!empty ($message['email_perso']))
	{
		$EmailPerso = $message['email_perso'];
		$Email = $Email.",".$EmailPerso;
	}
	DebugTrace ("getemailFromMatricule : email+Emailperso=".$Email);
	mysql_close($base);
	return $Email;

}

function getPrenomFromMatricule( $Matricule)
{
	$base = connectMaBase ();
	$sql = 'SELECT Prenom FROM user WHERE Matricule='.$Matricule;
	$db_messages = mysql_query($sql);

	$message = mysql_fetch_array($db_messages);
	$Prenom = $message['Prenom'];
	
	mysql_close($base);
	return $Prenom;
}


function getNomPrenomFromMatricule( $Matricule)
{
	$base = connectMaBase ();
	$sql = 'SELECT Nom, Prenom, UID, Matricule, PasswdMD5 FROM user WHERE Matricule='.$Matricule;
	$db_messages = mysql_query($sql);

	while($message = mysql_fetch_array($db_messages))
	{
		$Nom = $message['Nom'];
		$Prenom = $message['Prenom'];
	}
	mysql_close($base);
	return $Nom." ".$Prenom;
}

function getStatusFromMatricule( $Matricule)
{
	$base = connectMaBase ();
	$sql = 'SELECT ce_user_status FROM user WHERE Matricule='.$Matricule;
	$db_messages = mysql_query($sql);
	mysql_close($base);
	
	$retour = "";
	while($message = mysql_fetch_array($db_messages))
	{
		$retour = $message['ce_user_status'];
	}
	return $retour;
}


function getReglementStatusFromMatricule( $Matricule)
{
	$base = connectMaBase ();
	$sql = 'SELECT okreglement FROM user WHERE Matricule='.$Matricule;
	$db_messages = mysql_query($sql);
	mysql_close($base);
	
	$retour = -1;
	while($message = mysql_fetch_array($db_messages))
	{
		$retour = $message['okreglement'];
	}
	return $retour;
}

function getUpdateReglementStatusFromMatricule( $Matricule)
{
	$base = connectMaBase ();
	$sql = 'SELECT UpdateReglement FROM user WHERE Matricule='.$Matricule;
	$db_messages = mysql_query($sql);
	mysql_close($base);
	
	$retour = -1;
	while($message = mysql_fetch_array($db_messages))
	{
		$retour = $message['UpdateReglement'];
	}
	return $retour;
}


function GetColones ($NomTable)
{
	$base = connectMaBase ();
	$sql = "describe ".strtolower($NomTable);
	$db_Resp = mysql_query($sql);
	while($db_Categorie = mysql_fetch_array($db_Resp))
	{
		$colone[] = $db_Categorie ['Field'];
	}
	mysql_close($base);
	return $colone;
}

function getOutilsxxxWithUID($NomTable, $AttrNom, $uid)
{
	$base = connectMaBase ();
	$sql = "select * from ".strtolower($NomTable)." ORDER BY ".$AttrNom;
	$db_Resp = mysql_query($sql);
	$colone = array();
	if (!empty($db_Resp) && (mysql_num_rows($db_Resp) > 0))
	{
	   while($db_Categorie = mysql_fetch_array($db_Resp))
	   {
		if ($uid == 1)
			$colone[] = $db_Categorie [$AttrNom].";".$db_Categorie ['uid'];
		else
			$colone[] = $db_Categorie [$AttrNom];
	   }
	}	
	mysql_close($base);
	DebugTrace ("getOutilsxxxWithUID Requete sql= ".$sql);
	return $colone;
}

function getOutilsxxx($NomTable, $AttrNom)
{
	return getOutilsxxxWithUID($NomTable, $AttrNom, 1);
}

function MiseenFormeRequeteSQL($sql)
{
	$base = connectMaBase ();
	$db_messages = mysql_query($sql);
	$passage = 0;
	$Message = "Pas d'alerte";
	if (mysql_num_rows ($db_messages) > 0)
	{
		
		$Message = "<table bgcolor=\"black\" border=\"2\" cellpadding=\"2\" cellspacing=\"2\">";
		
		while($message = mysql_fetch_array($db_messages))
		{
			if ($passage == 0)
			{
				$Message = $Message."<tr>";
				$TabClef = array_keys ($message);
				for ($indicekey = 1; $indicekey < count ($TabClef); $indicekey += 2)
				{
					$Message = $Message."<th>";
					$Message = $Message.$TabClef[$indicekey];
					$Message = $Message."</th>";
				}
				$Message = $Message."</tr>";
			}
			$Message = $Message."<tr>";
			for ($indicekey = 1; $indicekey < count ($TabClef); $indicekey += 2)
			{
				$Message = $Message."<td>";
				$Message = $Message.$message[$TabClef[$indicekey]];
				$Message = $Message."</td>";
			}
			$Message = $Message."</tr>";
			$passage++;
		}
		$Message = $Message."</table>";
	}
	mysql_close($base);
	return $Message;
}

function getOutilsFromUID( $uid)
{
	$base = connectMaBase ();
	$sql = 'SELECT nom FROM outils WHERE uid='.$uid;
	$db_messages = mysql_query($sql);

	$message = mysql_fetch_array($db_messages);
	$Prenom = $message['nom'];
	
	mysql_close($base);
	return $Prenom;
}

function getAccessoireFromUID( $uid)
{
	$base = connectMaBase ();
	$sql = 'SELECT DescriptionDetaillee FROM accessoire WHERE uid='.$uid;
	$db_messages = mysql_query($sql);

	$message = mysql_fetch_array($db_messages);
	$Prenom = $message['DescriptionDetaillee'];
	
	mysql_close($base);
	return $Prenom;
}

function getFournitureFromUID( $uid)
{
	$base = connectMaBase ();
	$sql = 'SELECT DescriptionDetaillee FROM fourniture WHERE uid='.$uid;
	$db_messages = mysql_query($sql);

	$message = mysql_fetch_array($db_messages);
	$Prenom = $message['DescriptionDetaillee'];
	
	mysql_close($base);
	return $Prenom;
}

?>
