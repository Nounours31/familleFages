<?php
include_once 'Logs.php';

		$PREMS = 1;
		$DER = 2;
		
		//------------------------------------------------------
		// les info de maree
		//------------------------------------------------------
		$CORRECTION_HEURE_PM_VE = 'Correction_Heure_PM_VE';
		$CORRECTION_HEURE_PM_ME = 'Correction_Heure_PM_ME';
		$CORRECTION_HEURE_BM_ME = 'Correction_Heure_BM_ME';
		$CORRECTION_HEURE_BM_VE = 'Correction_Heure_BM_VE';
		$CORRECTION_HAUTEUR_PM_VE = 'Correction_Hauteur_PM_VE';
		$CORRECTION_HAUTEUR_PM_ME = 'Correction_Hauteur_PM_ME';
		$CORRECTION_HAUTEUR_BM_ME = 'Correction_Hauteur_BM_ME';
		$CORRECTION_HAUTEUR_BM_VE = 'Correction_Hauteur_BM_VE';

		$JOUR = 'jour'; 
		$HEURE = 'heure';
		$HAUTEUR = 'hauteur';
		$COEFCORRIGE = 'coefCorrige';
		$TYPE = 'type';
		$COEF = 'coef';
		$COEFREROUTE = 'coefReroute';
		$UIDPORT = 'uidport';
		$UIDPORTPRINCIPAL = 'uidportprincipal';
		
		
		//------------------------------------------------------
		// le singleton et les variables privees
		//------------------------------------------------------
		$singleton = null;
		$link = null;
		$debug = TRUE;
		
		
		
		//==========================================================================================
		// les methodes
		//==========================================================================================
		

		//------------------------------------------------------
		// Init de la DB attetion doit etre close ...
		//------------------------------------------------------
		function initDB ()
		{
			//------------------------------------------------------
			// les info de connexion
			//------------------------------------------------------
			switch ($_SERVER['SERVER_NAME']) 
			{
			  case "pierre.fages.free.fr":
				$db_connect_url = 'sql.free.fr';
				$db_connect_user = 'pierre.fages';
				$db_connect_password = 'frifri';
				$db_name = 'pierre_fages';
			    break;
			  case "site_local":
				$db_connect_url = 'localhost';
				$db_connect_user = 'u-pierre_fages';
				$db_connect_password = 'p-pierre_fages';
				$db_name = 'pierre_fages';
				break;
			  default:
				$db_connect_url = 'localhost';
				$db_connect_user = 'u-pierre_fages';
				$db_connect_password = 'p-pierre_fages';
				$db_name = 'pierre_fages';
			}
				
			$link = mysql_connect($db_connect_url, $db_connect_user, $db_connect_password);
			if (!$link) 
    			die('Could not connect: '.mysql_error());
			
			if (!mysql_select_db($db_name, $link)) 
			{
				mysql_close($link);
			}
			
			return $link;
		}
		
		//------------------------------------------------------
		// Close (pendant de l'init)
		//------------------------------------------------------
		function closeDB ($link)
		{
			mysql_close($link);
		}
		

		
		
		//------------------------------------------------------
		// Les ports dispo
		//------------------------------------------------------
		function getAvailablePort ()
		{
			$reponses = array();

			//-----------------------------
			// maree_portprincipaux:   uid nom long lat
			//-----------------------------
			$sqlmsg = 'select distinct maree_portprincipaux.nom from maree_portprincipaux ';
				
			$link = initDB();			
				
			$result = mysql_query ($sqlmsg, $link);
			if (!$result) 
			{
				print ('Erreur DB, impossible de effectuer une requête : '.$sqlmsg.'<br/>');
				print ('Erreur MySQL : '.mysql_error().'<br/>');
				
				array_push ($reponses,$link);
				array_push ($reponses,'Erreur DB, impossible de effectuer une requête : '.$sqlmsg.'<br/>');
				array_push ($reponses,'Erreur MySQL : '.mysql_error().'<br/>');
				return $reponses;
			}
				
			
			
			while ($row = mysql_fetch_assoc($result)) 
			{
				array_push ($reponses, $row['nom']);
			}
			
			mysql_free_result($result);

			
			//-----------------------------
			// maree_correction:   uid nom ...
			//-----------------------------
			$sqlmsg = 'select distinct maree_correction.nom from maree_correction ';
			if ($debug)
				print ($sqlmsg);
			
			$result = mysql_query ($sqlmsg, $link);
			if (!$result) {
				print ('Erreur DB, impossible de effectuer une requête'.$sqlmsg.'<br/>');
				print ('Erreur MySQL : '.mysql_error().'<br/>');
				return $reponses;
			}
			
			while ($row = mysql_fetch_assoc($result)) 
			{
				array_push ($reponses, $row['nom']);
			}
			
			mysql_free_result($result);
			closeDB($link);
			
			return $reponses;
		}

		
		
		

		
		
		//------------------------------------------------------
		// recup de correction pour un port
		//------------------------------------------------------
		function findCorrectionPourUnPort  ($PortUID)
		{
			//---------------------------------------------
			// migration PHP4
			//---------------------------------------------
			$UIDPORT = 'uidport';
			$UIDPORTPRINCIPAL = 'uidportprincipal';
			$CORRECTION_HEURE_PM_VE = 'Correction_Heure_PM_VE';
			$CORRECTION_HEURE_PM_ME = 'Correction_Heure_PM_ME';
			$CORRECTION_HEURE_BM_ME = 'Correction_Heure_BM_ME';
			$CORRECTION_HEURE_BM_VE = 'Correction_Heure_BM_VE';
			$CORRECTION_HAUTEUR_PM_VE = 'Correction_Hauteur_PM_VE';
			$CORRECTION_HAUTEUR_PM_ME = 'Correction_Hauteur_PM_ME';
			$CORRECTION_HAUTEUR_BM_ME = 'Correction_Hauteur_BM_ME';
			$CORRECTION_HAUTEUR_BM_VE = 'Correction_Hauteur_BM_VE';
			//---------------------------------------------
			
			
			$sqlmsg = 'select maree_correction.* from maree_correction where (';
			$sqlmsg .= "maree_correction.uid = '".$PortUID."'";
			$sqlmsg .= ")";
		
			$link = initDB();
		
			$result = mysql_query ($sqlmsg, $link);
			if (!$result) {
				print ('Erreur DB, impossible de effectuer une requête'.$sqlmsg.'<br/>');
				print ('Erreur MySQL : '.mysql_error().'<br/>');
				return '';
			}
		
			$reponse = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$reponse[$UIDPORT] = $row['uid'];
				$reponse[$UIDPORTPRINCIPAL] = $row['uidportprincipal'];
				$reponse[$CORRECTION_HEURE_PM_VE] = $row['Correction_Heure_PM_VE'];
				$reponse[$CORRECTION_HEURE_PM_ME] = $row['Correction_Heure_PM_ME'];
				$reponse[$CORRECTION_HEURE_BM_ME] = $row['Correction_Heure_BM_ME'];
				$reponse[$CORRECTION_HEURE_BM_VE] = $row['Correction_Heure_BM_VE'];
				$reponse[$CORRECTION_HAUTEUR_PM_VE] = $row['Correction_Hauteur_PM_VE'];
				$reponse[$CORRECTION_HAUTEUR_PM_ME] = $row['Correction_Hauteur_PM_ME'];
				$reponse[$CORRECTION_HAUTEUR_BM_ME] = $row['Correction_Hauteur_BM_ME'];
				$reponse[$CORRECTION_HAUTEUR_BM_VE] = $row['Correction_Hauteur_BM_VE'];
			}
		
			mysql_free_result($result);
			closeDB($link);
			return $reponse;
		}
		
		
		
		//------------------------------------------------------
		// Passage de port a sont UId et port principal
		//------------------------------------------------------
		function findUIDportFromName  ($nomport)
		{
			//---------------------------------------------
			// migration PHP4
			//---------------------------------------------
			$UIDPORT = 'uidport';
			$UIDPORTPRINCIPAL = 'uidportprincipal';
			//---------------------------------------------
			
			$sqlmsg = 'select maree_portprincipaux.uid from maree_portprincipaux where (';
			$sqlmsg .= "maree_portprincipaux.nom = '".$nomport."'";
			$sqlmsg .= ")";
				
				
			$link = initDB();				
			$result = mysql_query ($sqlmsg, $link);
			if (!$result) {
				print ('Erreur DB, impossible de effectuer une requête >>'.$sqlmsg.'<< <br/>');
				print ('Erreur MySQL : '.mysql_error().'<br/>');
				return '';
			}
				
			
			$reponse = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$reponse[$UIDPORT] = $row['uid'];
				$reponse[$UIDPORTPRINCIPAL] = 0;
			}

			mysql_free_result($result);
			
			if (count($reponse) > 0)
			{
				closeDB($link);				
				return $reponse;
			}
			
			
			$sqlmsg = 'select maree_correction.uid, maree_correction.uidportprincipal from maree_correction where (';
			$sqlmsg .= "maree_correction.nom = '".$nomport."'";
			$sqlmsg .= ")";
				
								
			$result = mysql_query ($sqlmsg, $link);
			if (!$result) {
				print ('Erreur DB, impossible de effectuer une requête '.$sqlmsg.'<br/>');
				print ('Erreur MySQL : '.mysql_error().'<br/>');
				return '';
			}
				
			$reponse = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$reponse[$UIDPORT] = $row['uid'];
				$reponse[$UIDPORTPRINCIPAL] = $row['uidportprincipal'];
			}

			mysql_free_result($result);
			closeDB($link);		

			return $reponse;
		}
		
		
		//------------------------------------------------------
		// Les infos maree d'une fin de journee
		//------------------------------------------------------
		function getDerniereInfoDuJourPrecedent  ($dd, $uidPort)
		{
			//---------------------------------------------
			// migration PHP4
			//---------------------------------------------
			$PREMS = 1;
			$DER = 2;
			//---------------------------------------------
			$date = $dd;			
			$date = strtotime('-1 Day', $date);
			return getUneInfoDuJour ($date, $DER, $uidPort);
		}
		
		function getPremiereInfoDuJourSuivant ($dd, $uidPort)
		{
			//---------------------------------------------
			// migration PHP4
			//---------------------------------------------
			$PREMS = 1;
			$DER = 2;
			//---------------------------------------------
			$date = $dd;			
			$date = strtotime('+1 Day', $date);
			return getUneInfoDuJour ($date, $PREMS, $uidPort);
		}
		
		function getUneInfoDuJour ($dd, $rang, $uidPort)
		{
			//---------------------------------------------
			// migration PHP4
			//---------------------------------------------
			$PREMS = 1;
			$DER = 2;
			//---------------------------------------------
					
			//-----------------------------
			// maree_horairemaree:   uid jour(date) port
			// maree_unhorairemaree: uid heure(time) hauteur coef type(BM PM) uid_tablehoraireMaree
			//-----------------------------
			$sqldate = date('Y-m-d', $dd);
			$sqlmsg = 'select maree_horairemaree.uidport, maree_horairemaree.jour, maree_unhorairemaree.* from maree_unhorairemaree, maree_horairemaree where (';
			$sqlmsg .= "((maree_horairemaree.uidport = ".$uidPort.") and (maree_horairemaree.jour = '".$sqldate."'))"; 
			$sqlmsg .= " and ";
			$sqlmsg .= "(maree_horairemaree.uid = maree_unhorairemaree.uid_tablehoraireMaree)"; 
			
			if ($rang == $DER)
				$sqlmsg .= ") order by maree_unhorairemaree.heure DESC LIMIT 0,1";
			
			else if ($rang == $PREMS)
				$sqlmsg .= ") order by maree_unhorairemaree.heure ASC LIMIT 0,1";
			
			else	
				$sqlmsg .= ") order by maree_unhorairemaree.heure ASC LIMIT 0,1";
			
			return getDayMareInfo ($sqlmsg);
		}

		//------------------------------------------------------
		// Les infos maree d'une journee
		//------------------------------------------------------
		function getDayInfo ($dd, $uidPort)
		{
			//-----------------------------
			// maree_horairemaree:   uid jour(date) port
			// maree_unhorairemaree: uid heure(time) hauteur coef type(BM PM) uid_tablehoraireMaree
			//-----------------------------
			$sqldate = date('Y-m-d', $dd);
			$sqlmsg = 'select maree_horairemaree.uidport, maree_horairemaree.jour, maree_unhorairemaree.* from maree_unhorairemaree, maree_horairemaree where (';
			$sqlmsg .= "((maree_horairemaree.uidport = ".$uidPort.") and (maree_horairemaree.jour = '".$sqldate."'))";
			$sqlmsg .= " and ";
			$sqlmsg .= "(maree_horairemaree.uid = maree_unhorairemaree.uid_tablehoraireMaree)";
			$sqlmsg .= ") order by maree_unhorairemaree.heure asc";
			
			return getDayMareInfo ($sqlmsg);
		}
		
		
		
		
		function getDayMareInfo ($sqlmsg)
		{
			//---------------------------------------------
			// migration PHP4
			//---------------------------------------------
			$JOUR = 'jour'; 
			$HEURE = 'heure';
			$HAUTEUR = 'hauteur';
			$COEFCORRIGE = 'coefCorrige';
			$TYPE = 'type';
			$COEF = 'coef';
			$COEFREROUTE = 'coefReroute';
			$UIDPORT = 'uidport';
			$UIDPORTPRINCIPAL = 'uidportprincipal';
			//---------------------------------------------
			
			$link = initDB();
			$result = mysql_query ($sqlmsg, $link);
			if (!$result) {
				print ('getDayMareInfo --- Erreur DB, impossible de effectuer une requête'.$sqlmsg.'<br/>');
				print ('Erreur MySQL : '.mysql_error().'<br/>');
				return '';
			}
				
			$reponses = array();
			$unereponse = array();
			$indicePM = 0 ;
			while ($row = mysql_fetch_assoc($result))
			{
				//----------------------------------------------------
				// recup des infos
				//----------------------------------------------------
				$unereponse[$HEURE] = $row['heure'];
				$unereponse[$HAUTEUR] = $row['hauteur'];
				$unereponse[$COEFCORRIGE] = 0;

				//----------------------------------------------------
				// je compte le nombre de PM
				//----------------------------------------------------
				$unereponse[$TYPE] = $row['type'];
				if (strcmp ($unereponse[$TYPE], "PM") == 0)
					$indicePM++;
				
				//----------------------------------------------------
				// je dois aller checher le coef de Brest si besoin
				//----------------------------------------------------
				$unereponse[$COEF] = $row['coef'];
				if (($unereponse[$COEF] == -1) && (strcmp ($unereponse[$TYPE], "PM") == 0))// je ne suis pas a Brest mais sur un PM ...
				{
					$sqlmsgForCoef = 'select maree_unhorairemaree.coef from maree_unhorairemaree, maree_horairemaree where (';
					$sqlmsgForCoef .= "((maree_horairemaree.uidport = 1) and (maree_horairemaree.jour = '".$row['jour']."'))"; // maree du meme jour
					$sqlmsgForCoef .= " and ";
					$sqlmsgForCoef .= "(maree_unhorairemaree.uid_tablehoraireMaree = maree_horairemaree.uid)"; // je suis sur une PM
					$sqlmsgForCoef .= " and ";
					$sqlmsgForCoef .= "(maree_unhorairemaree.type = 'PM')"; // je suis sur une PM
					$sqlmsgForCoef .= ") order by maree_unhorairemaree.heure asc";
						
					$resultCoef = mysql_query ($sqlmsgForCoef, $link);
					if (!$resultCoef) {
						print ('Erreur DB, impossible de effectuer une requête'.$sqlmsgForCoef.'<br/>');
						print ('Erreur MySQL : '.mysql_error().'<br/>');
						return '';
					}				
					$indicePMForCoef = 0 ;
					
					//-----------------------------------------------------
					// au cas ou i manquerait une maree a Brest ....
					//-----------------------------------------------------
					if (mysql_num_rows($resultCoef) < $indicePM)
						$indicePM = mysql_num_rows($resultCoef);
					
					
					while ($rowCoef = mysql_fetch_assoc($resultCoef))
					{
						$indicePMForCoef++;
						if ($indicePMForCoef == $indicePM)
							$unereponse[$COEFCORRIGE] = $rowCoef['coef'];
					}
					mysql_free_result($resultCoef);
				}

				
				//----------------------------------------------------
				// Maj du tableau
				//----------------------------------------------------
				$reponses[$UIDPORT] = $row['uidport'];
				$reponses[$JOUR] = $row['jour'];
				$reponses[$row['heure']] = $unereponse;
			}
				
			mysql_free_result($result);
			closeDB($link);
				
			return $reponses;
		}		
?>