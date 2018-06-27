<?php
include_once './db_maree.php';
include_once './maree.php';
include_once './Tools.php';

	$DEBUG=FALSE;

	if (
		(isset($_POST['Date']) && ($_POST['Date'] != null)) && 
		(isset($_POST['Port']) && ($_POST['Port'] != null)) && 
		(isset($_POST['Heure']) && ($_POST['Heure'] != null)) && 
		(isset($_POST['Seuil']) && ($_POST['Seuil'] != null))
	   )
	{
		//-----------------------------------
		// parse input --> du check a faire !!
		//-----------------------------------
		$Date=$_POST['Date'];
		$Port=$_POST['Port'];
		$Seuil=$_POST['Seuil'];
		$Heure=$_POST['Heure'];
		
		
		//-----------------------------------
		// init de la classe
		//-----------------------------------
		
		//----------------------------------
		// Date
		//----------------------------------
		$JourCalculMaree = strtotime ($Date);

		
		//----------------------------------------------------
		// debug
		//----------------------------------------------------
		print ('<tr>');
			print ('<td colspan="12">');
			print ('Info de la requete (debug) : Input = ['.$_POST['Date'].'] ['.$_POST['Port'].'] ['.$_POST['Seuil'].'] ['.$_POST['Heure'].'] <br/>');
			print ('Date reconstituee  : '.date('Y-m-d', $JourCalculMaree).'<br/>');
			print ('</td>');
		print ('</tr>');
		
		
		//----------------------------------------------------
		// recup des infos maree
		//----------------------------------------------------
		$luidPort = findUIDportFromName($Port);
		if (count($luidPort) < 1)
		{
			print ('<tr>');
			print ('<td colspan="12"> Imposible de trouver ce port</td>');
			print ('</tr>');
			return;
		}
		
		
		if ($luidPort[$UIDPORTPRINCIPAL] != 0)
		{
			//----------
			// les corrections
			//----------
			$correctifPort = findCorrectionPourUnPort($luidPort[$UIDPORT]);

			//----------
			// les heure - hauteur - coef
			//----------
			$infoJPrecedent0 = getDerniereInfoDuJourPrecedent($JourCalculMaree, $luidPort[$UIDPORTPRINCIPAL]);
			$infoJCourant0   = getDayInfo($JourCalculMaree,  $luidPort[$UIDPORTPRINCIPAL]);
			$infoJSuivant0   = getPremiereInfoDuJourSuivant($JourCalculMaree,  $luidPort[$UIDPORTPRINCIPAL]);
			
			
			//----------
			// J'applique le correctif pour passer du por principal au port courant.
			// Pb pour la derniere marre du jour d'avant ou du jour suivant je n'ai pas forcement le coef.
			// donc je calcul d'abord la correction du jour courant et je l'envoire au deux autre 
			// --> 'arrayCoef' est envoyer par reference
			//----------
			$arrayCoef = array();
			$pipoarrayCoef = array();
			$infoJCourant = applyCorrection($infoJCourant0, $correctifPort, -1, $arrayCoef);
			
			if (!isset($arrayCoef[1]))
				$arrayCoef[1] = $arrayCoef[0];
			
			$infoJPrecedent = applyCorrection($infoJPrecedent0, $correctifPort, $arrayCoef[0], $pipoarrayCoef);
			$infoJSuivant = applyCorrection($infoJSuivant0, $correctifPort, $arrayCoef[1], $pipoarrayCoef);
		}
		else
		{
			//----------
			// les heure - hauteur - coef
			//----------
			$correctifPort = "Je suis un port principal";
			$infoJPrecedent = getDerniereInfoDuJourPrecedent($JourCalculMaree, $luidPort[$UIDPORT]);
			$infoJCourant = getDayInfo($JourCalculMaree, $luidPort[$UIDPORT]);
			$infoJSuivant = getPremiereInfoDuJourSuivant($JourCalculMaree, $luidPort[$UIDPORT]);
		}
		
		//----------------------------------------------------
		// debug
		//----------------------------------------------------
		if ($DEBUG)
		{
			print ('<tr>');
			print ('<td colspan="12">');
			print ('uidPort  : '.$luidPort[$UIDPORT].'<br/>');
			print ('uidPortPrincipal  : '.$luidPort[$UIDPORTPRINCIPAL].'<br/>');
			print ('correctifPort  : '.dumpCorrection($correctifPort).'<br/>');
			print ('infoJPrecedent0  : '.dumpInfoMaree($infoJPrecedent0).'<br/>');
			print ('infoJPrecedent  : '.dumpInfoMaree($infoJPrecedent).'<br/>');
			print ('infoJCourant0  : '.dumpInfoMaree($infoJCourant0).'<br/>');
			print ('infoJCourant  : '.dumpInfoMaree($infoJCourant).'<br/>');
			print ('infoJSuivant0  : '.dumpInfoMaree($infoJSuivant0).'<br/>');
			print ('infoJSuivant  : '.dumpInfoMaree($infoJSuivant).'<br/>');
			print ('</td>');
			print ('</tr>');
		}
		
		
		//----------------------------------------------------
		// Est ce que je commence par une PM ou BM ?
		//----------------------------------------------------
		$StartByPM = isDemarreParUnPM ($infoJPrecedent);
		$tableauAffichage = array();
		
		for ($i = 0; $i < 3; $i++)
		{
			for ($j = 0; $j < 12; $j++)
			{
				if ($DEBUG)			
					$tableauAffichage[$i][$j] = 'x['.$i.','.$j.']';
				else
					$tableauAffichage[$i][$j] = '';
			}			
		}

		
		//--------------------------------------------------------
		// Comment remplir le tableau
		//--------------------------------------------------------
		$HeureEnInt = HourToInt($Heure);
		$NBMareeCeJour = getNbMareeParJour ($infoJCourant);
		$affichageHauteurParDate=FALSE;
		$JIndice = 0;
		$IIndice = 0;
		$ordreremplissage = array();
		if ($StartByPM)
			$ordreremplissage = array (0, 1, 2, 1, 0, 1, 2, 1, 0, 1, 2, 1, 0, 1, 2, 1, 0);
		else 
			$ordreremplissage = array (2, 1, 0, 1, 2, 1, 0, 1, 2, 1, 0, 1, 2, 1, 0, 1, 2);
		
		//------------------------
		// J-1
		//------------------------
		// PM (ou BM) 
		//------------------------
		$tableauAffichage[$ordreremplissage[$IIndice++]][$JIndice++]  = getDisplayInfoFromInfoMaree ($infoJPrecedent, 1);
		
		//------------------------
		// J (1)
		//------------------------
		// Recherche du seuil, puis de la hauteur/date [si necessaire] puis BM (ou PM) 
		//------------------------
		$tableauAffichage[$ordreremplissage[$IIndice++]][$JIndice++]  = CalculSeuilEntreDeuxJours($infoJPrecedent, $infoJCourant, $Seuil, 1, 1);		
		if (isHeureAvantEtale($HeureEnInt, $infoJCourant, 1))
		{
			$affichageHauteurParDate = TRUE;
			$tableauAffichage[$ordreremplissage[$IIndice - 1]][$JIndice++]  = DisplayHauteurFromHeureDeuxJours ($infoJPrecedent, $infoJCourant, $Heure, 1, 1);
		}
		$tableauAffichage[$ordreremplissage[$IIndice++]][$JIndice++]  = getDisplayInfoFromInfoMaree ($infoJCourant, 1);
		
		
		//------------------------
		// J (2)
		//------------------------
		// Recherche du seuil, puis de la hauteur/date [si necessaire] puis BM (ou PM) 
		//------------------------
		$tableauAffichage[$ordreremplissage[$IIndice++]][$JIndice++]  = CalculSeuil($infoJCourant, 1, $Seuil);					
		if (!$affichageHauteurParDate && isHeureAvantEtale($HeureEnInt, $infoJCourant, 2))
		{
			$affichageHauteurParDate = TRUE;
			$tableauAffichage[$ordreremplissage[$IIndice - 1]][$JIndice++]  = DisplayHauteurFromHeure ($infoJCourant, 1, $Heure);
		}
		
		$tableauAffichage[$ordreremplissage[$IIndice++]][$JIndice++]  = getDisplayInfoFromInfoMaree ($infoJCourant, 2);
			

		//------------------------
		// J (3)
		//------------------------
		// Recherche du seuil, puis de la hauteur/date [si necessaire] puis BM (ou PM) 
		//------------------------
		$tableauAffichage[$ordreremplissage[$IIndice++]][$JIndice++]  = CalculSeuil($infoJCourant, 2, $Seuil);
		if (!$affichageHauteurParDate && isHeureAvantEtale($HeureEnInt, $infoJCourant, 3))
		{
			$affichageHauteurParDate = TRUE;
			$tableauAffichage[$ordreremplissage[$IIndice - 1]][$JIndice++]  = DisplayHauteurFromHeure ($infoJCourant, 2, $Heure);
		}
		$tableauAffichage[$ordreremplissage[$IIndice++]][$JIndice++]  = getDisplayInfoFromInfoMaree ($infoJCourant, 3);
		
		
		//------------------------
		// J (4) si necessaire
		//------------------------
		// Recherche du seuil, puis de la hauteur/date [si necessaire] puis BM (ou PM) 
		//------------------------
		if ($NBMareeCeJour == 4)
		{
			$tableauAffichage[$ordreremplissage[$IIndice++]][$JIndice++]  = CalculSeuil($infoJCourant, 3, $Seuil);
			if (!$affichageHauteurParDate && isHeureAvantEtale($HeureEnInt, $infoJCourant, 4))
			{
				$affichageHauteurParDate = TRUE;
				$tableauAffichage[$ordreremplissage[$IIndice - 1]][$JIndice++]  = DisplayHauteurFromHeure ($infoJCourant, 3, $Heure);
			}
			$tableauAffichage[$ordreremplissage[$IIndice++]][$JIndice++]  = getDisplayInfoFromInfoMaree ($infoJCourant, 4);
		}
		
		
		//------------------------
		// J + 1
		//------------------------
		// Recherche du seuil, puis de la hauteur/date [si necessaire] puis BM (ou PM) 
		//------------------------
		$tableauAffichage[$ordreremplissage[$IIndice++]][$JIndice++]  = CalculSeuilEntreDeuxJours($infoJCourant, $infoJSuivant, $Seuil, $NBMareeCeJour, 1);
		if (!$affichageHauteurParDate)
			$tableauAffichage[$ordreremplissage[$IIndice - 1]][$JIndice++]  = DisplayHauteurFromHeureDeuxJours ($infoJCourant, $infoJSuivant, $Heure, $NBMareeCeJour, 1);
		
		$tableauAffichage[$ordreremplissage[$IIndice++]][$JIndice++] = getDisplayInfoFromInfoMaree ($infoJSuivant, 1);

		
		
		
		for ($i = 0; $i < 3; $i++)
		{
			if($i == 1)
				print ('<tr>');
			else
				print ('<tr>');
			
			for ($j = 0; $j < 12; $j++)
			{
				if($j == 0)
					print ('<td bgcolor="#81F7F3">');
				else if($j == 11)
					print ('<td bgcolor="#81F7F3">');
				else if (($i == 0) && (strlen ($tableauAffichage[$i][$j]) >10))
					print ('<td bgcolor="#58FA58">');
				else if (($i == 1) && (strlen ($tableauAffichage[$i][$j]) >3) && (strncmp ($tableauAffichage[$i][$j],'He:',3) == 0))
					print ('<td bgcolor="#FA58F4">');
				else if (($i == 1) && (strlen ($tableauAffichage[$i][$j]) >10))
					print ('<td bgcolor="#FFFF00">');
				else if (($i == 2) && (strlen ($tableauAffichage[$i][$j]) >10))
					print ('<td bgcolor="#FF0000">');
				else 
					print ('<td>');
				
				print ($tableauAffichage[$i][$j]);
				print ('</td>');
			}
			print ('</tr>');
		}
		/*
		print ('<tr>');
		print ('<td id="J-1"></td>');
		print ('<td></td><td>10:52<br/>12.7m</td><td></td><td></td><td></td><td>14:52<br/>12.7m</td><td></td><td></td><td></td>');
		print ('<td id="Jx1">14:52<br/>12.7m</td>');
		print ('<td></td>');
		print ('</tr>');
		
		print ('<tr>');
		print ('<td id="J-1"></td>');
		print ('<td>5:18</td><td></td><td>11:57</td><td></td><td>13:25</td><td></td><td>15:58</td><td></td><td>18:54</td>');
		print ('<td id="Jx1"></td>');
		print ('<td></td>');
		print ('</tr>');
		
		print ('<tr>');
		print ('<td id="J-1">4:51<br/>4.21m</td><td></td><td></td><td></td><td>12:52<br/>2.7m</td><td></td><td></td><td></td><td>12:52<br/>2.7m</td><td></td>');
		print ('<td id="Jx1"></td>');
		print ('<td id="Seuil"></td>');
		print ('</tr>');
		*/
	}
?>