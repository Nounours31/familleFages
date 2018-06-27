<?php
include_once './db_maree.php';
include_once './maree.php';
include_once './Tools.php';

	$DEBUG=FALSE;

	if (
		(isset($_POST['Port']) && ($_POST['Port'] != null)) 
	   )
	{
		//-----------------------------------
		// parse input --> du check a faire !!
		//-----------------------------------
		$Port=$_POST['Port'];
		
		
		
		//----------------------------------------------------
		// debug
		//----------------------------------------------------
		print ('<tr>');
			print ('<td colspan="12">');
			print ('Info de la requete (debug) : Input = ['.$_POST['Port'].'] <br/>');
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
		
		
		
		//------------------------------------------------------------------------
		// Calcul pour le port principal des jours de Grandes Marees
		//------------------------------------------------------------------------
		$portarequeter = $luidPort[$UIDPORT];
		if ($luidPort[$UIDPORTPRINCIPAL] != 0)
			$portarequeter = $luidPort[$UIDPORTPRINCIPAL];

		$sql = 'select distinct maree_horairemaree.jour from maree_horairemaree, maree_unhorairemaree where (';
		// maree < 1.0
		$sql .= '(maree_unhorairemaree.hauteur < 1.0)';
		//il faut que ce soit dans le bon port;
		$sql .= ' and ((maree_unhorairemaree.uid_tablehoraireMaree = maree_horairemaree.uid) and (maree_horairemaree.uidport = '.$portarequeter.'))';
		// 1 an
		$sql .= " and ((maree_horairemaree.jour < '".date ('Y-m-d',strtotime('+1 year'))."') and (maree_horairemaree.jour > '".date ('Y-m-d')."'))";
		$sql .= ') order by maree_horairemaree.jour asc';
			
			
		$link = initDB();
		$result = mysql_query ($sql, $link);
		if (!$result)
		{
			print ('--- Erreur DB, impossible de effectuer une requête'.$sql.'<br/>');
			print ('Erreur MySQL : '.mysql_error().'<br/>');
			return '';
		}
		
		$reponses = array();
		while ($row = mysql_fetch_assoc($result))
		{
			//----------------------------------------------------
			// recup des infos
			//----------------------------------------------------
			array_push($reponses, $row['jour']);
		}
		mysql_free_result($result);
		closeDB($link);
		
		
		

		
		//------------------------------------------------------------------------
		// Les infos marees
		//------------------------------------------------------------------------
		$allreponses = array();
		if ($luidPort[$UIDPORTPRINCIPAL] != 0)
		{
			//----------
			// les corrections
			//----------
			$correctifPort = findCorrectionPourUnPort($luidPort[$UIDPORT]);
		}
					
		//-----------------------------------------------------------
		// Les infos maree de ces jours la
		//-----------------------------------------------------------
		for ($i = 0; $i < count ($reponses); $i++)
		{							
			//----------
			// J'applique le correctif pour passer du por principal au port courant.
			// Pb pour la derniere marre du jour d'avant ou du jour suivant je n'ai pas forcement le coef.
			// donc je calcul d'abord la correction du jour courant et je l'envoire au deux autre 
			// --> 'arrayCoef' est envoyer par reference
			//----------
			$infoJCourant0 = getDayInfo (strtotime ($reponses[$i]), $portarequeter);
			if ($luidPort[$UIDPORTPRINCIPAL] != 0)			
			{	
				$infoJCourant = applyCorrection($infoJCourant0, $correctifPort, -1, $arrayCoef);
				array_push ($allreponses, $infoJCourant);
			}
			else
				array_push ($allreponses, $infoJCourant0);
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
		
		
		
		
		
		for ($i = 0; $i < count ($allreponses); $i++)
		{
			$color = '#FCBBF4';
			if ($i % 2 != 0) $color = '#C7DEFC';
			
			print ('<tr bgcolor="'.$color.'">');
			print ('<td>'.$allreponses[$i]['jour'].'</td>');
			foreach ($allreponses[$i] as $key => $value)
			{
			
				if (strcmp ($key, 'jour') == 0) continue;
				if (strcmp ($key, 'uidport') == 0) continue;
				if (strcmp ($allreponses[$i][$key]['type'], 'BM') == 0)
					print ('<td>'.((int)($allreponses[$i][$key]['hauteur'] * 100.0))/100.0.'m<br/>'.$allreponses[$i][$key]['heure'].'<br/>'.$allreponses[$i][$key]['type'].'</td>');
				else
					print ('<td>'.((int)($allreponses[$i][$key]['hauteur'] * 100.0))/100.0.'m<br/>'.$allreponses[$i][$key]['heure'].'<br/>coef. :'.$allreponses[$i][$key]['coefCorrige'].'<br/>'.$allreponses[$i][$key]['type'].'</td>');
				continue;
			}
			print ('</tr>');
		}		
	}
?>