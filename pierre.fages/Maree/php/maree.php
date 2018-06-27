<?php
include_once './Tools.php';
include_once './Logs.php';

		//------------------------------------------------------
		// Les variables privees
		//------------------------------------------------------
		$debug = FALSE;
		
		
		
		
		//==========================================================================================
		// les methodes
		//==========================================================================================
		
		//------------------------------------------------------
		// Les ports dispo
		//------------------------------------------------------
		function applyCorrection ($infoMaree, $correction, $CoefImposed, &$arrayCoef)
		{
			//-------------------------------------------------------
			// migration PHP4
			//-------------------------------------------------------
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
			//-------------------------------------------------------
			
			$newInfoMaree = $infoMaree;
			$indice = 0;
			
			foreach ($newInfoMaree as $key => $value)
			{
				$pattern = '/[0-9]{2}:[0-9]{2}:[0-9]{2}/';
				if (preg_match($pattern, $key))
				{
					$infoOrdonnee[$indice++] = $key;
				}
			}
			
			if (($CoefImposed == -1) && (($indice < 3) || ($indice > 4)))
			{
				return null;
			}
			
			if (($CoefImposed > -1) && ($indice != 1))
			{
				return null;
			}
				
			
			$indiceBM_Memo = -1;
			$Coef = -1;
			for ($i = 0; $i < $indice; $i++)
			{
				//----------------------------------------------------------------
				// Je suis sur un BM, et j'ai un Coed de value je recale
				// Sinon j'attend la prochaine PM pour faire le recalage
				//----------------------------------------------------------------
				if (strcmp ($newInfoMaree[$infoOrdonnee[$i]][$TYPE], "BM") == 0)
				{
					// Cas particulier d'une seule BP, il faut prendre le coefforce
					if (($CoefImposed > -1) && ($indice == 1))
						$Coef = $CoefImposed;
									
					if ($Coef > -1) // un coef a deja ete trouve
					{
						$heure = HourToInt($newInfoMaree[$infoOrdonnee[$i]][$HEURE]);
						$hauteur = $newInfoMaree[$infoOrdonnee[$i]][$HAUTEUR];
						if ($Coef > 60) // VE
						{
							$heure = $heure + $correction[$CORRECTION_HEURE_BM_VE];
							$hauteur = $hauteur + $correction[$CORRECTION_HAUTEUR_BM_VE];
						}
						else // ME
						{
							$heure = $heure + $correction[$CORRECTION_HEURE_BM_ME];
							$hauteur = $hauteur + $correction[$CORRECTION_HAUTEUR_BM_ME];
						}
						$newInfoMaree[$infoOrdonnee[$i]][$HEURE] = IntToHour($heure);
						$newInfoMaree[$infoOrdonnee[$i]][$HAUTEUR] = $hauteur;
					}
					else
						$indiceBM_Memo = $i;
				}
				
				//----------------------------------------------------------------
				// Je suis sur une PM je me recale et s'il y avait une BM avant je la recale
				//----------------------------------------------------------------
				if (strcmp ($newInfoMaree[$infoOrdonnee[$i]][$TYPE], "PM") == 0)
				{
					$Coef = $newInfoMaree[$infoOrdonnee[$i]][$COEF]; 
					if ( $Coef < 0)
						$Coef =  $newInfoMaree[$infoOrdonnee[$i]][$COEFCORRIGE];
					
					if (isset($arrayCoef))
						array_push($arrayCoef, $Coef);
						
					$heure = HourToInt($newInfoMaree[$infoOrdonnee[$i]][$HEURE]);
					$hauteur = $newInfoMaree[$infoOrdonnee[$i]][$HAUTEUR];
						
					if ($Coef > 60) // VE
					{
						$heure = $heure + $correction[$CORRECTION_HEURE_PM_VE];
						$hauteur = $hauteur + $correction[$CORRECTION_HAUTEUR_PM_VE];
					}
					else // ME
					{
						$heure = $heure + $correction[$CORRECTION_HEURE_PM_ME];
						$hauteur = $hauteur + $correction[$CORRECTION_HAUTEUR_PM_ME];
					}
					
					$newInfoMaree[$infoOrdonnee[$i]][$HEURE] = IntToHour($heure);
					$newInfoMaree[$infoOrdonnee[$i]][$HAUTEUR] = $hauteur;
						
					
					//-----------------------
					// La BM
					//-----------------------
					if ($indiceBM_Memo > -1) // une BM en attente
					{
						$heure = HourToInt($newInfoMaree[$infoOrdonnee[$indiceBM_Memo]][$HEURE]);
						$hauteur = $newInfoMaree[$infoOrdonnee[$indiceBM_Memo]][$HAUTEUR];
						if ($Coef > 60) // VE
						{
							$heure = $heure + $correction[$CORRECTION_HEURE_BM_VE];
							$hauteur = $hauteur + $correction[$CORRECTION_HAUTEUR_BM_VE];
						}
						else // ME
						{
							$heure = $heure + $correction[$CORRECTION_HEURE_BM_ME];
							$hauteur = $hauteur + $correction[$CORRECTION_HAUTEUR_BM_ME];
						}
						$newInfoMaree[$infoOrdonnee[$indiceBM_Memo]][$HEURE] = IntToHour($heure);
						$newInfoMaree[$infoOrdonnee[$indiceBM_Memo]][$HAUTEUR] = $hauteur;
						$indiceBM_Memo = -1;
					}
				}
			}
			return $newInfoMaree;
		}

	
		//==========================================================================================
		// le debug
		//==========================================================================================
		function dumpCorrection ($correction)
		{
			//-------------------------------------------------------
			// migration PHP4
			//-------------------------------------------------------
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
			//-------------------------------------------------------
			
			
			$msg = "<table>";
			if (count ($correction) > 1)
			{
				$msg .= "<tr><td> count </td><td>".count ($correction)."</td></tr>";
				$msg .= "<tr><td> port uid </td><td>".$correction[$UIDPORT]."</td></tr>";
				$msg .= "<tr><td> port principal uid </td><td>".$correction[$UIDPORTPRINCIPAL]."</td></tr>";
				$msg .= "<tr><td> Correction_Heure_PM_VE </td><td>".$correction[$CORRECTION_HEURE_PM_VE]."</td></tr>";
				$msg .= "<tr><td> Correction_Heure_PM_ME </td><td>".$correction[$CORRECTION_HEURE_PM_ME]."</td></tr>";
				$msg .= "<tr><td> Correction_Heure_BM_ME </td><td>".$correction[$CORRECTION_HEURE_BM_ME]."</td></tr>";
				$msg .= "<tr><td> Correction_Heure_BM_VE </td><td>".$correction[$CORRECTION_HEURE_BM_VE]."</td></tr>";
				$msg .= "<tr><td> Correction_Hauteur_PM_VE </td><td>".$correction[$CORRECTION_HAUTEUR_PM_VE]."</td></tr>";
				$msg .= "<tr><td> Correction_Hauteur_PM_ME </td><td>".$correction[$CORRECTION_HAUTEUR_PM_ME]."</td></tr>";
				$msg .= "<tr><td> Correction_Hauteur_BM_ME </td><td>".$correction[$CORRECTION_HAUTEUR_BM_ME]."</td></tr>";
				$msg .= "<tr><td> Correction_Hauteur_BM_VE </td><td>".$correction[$CORRECTION_HAUTEUR_BM_VE]."</td></tr>";
			}
			else if (count ($correction) == 1)				
			{
				$msg .= "<tr>";
				$msg .= $correction;
				$msg .= "</tr>";
			}			
			else				
			{
				$msg .= "<tr>";
				$msg .= 'Invalide correctif';
				$msg .= "</tr>";
			}			
			$msg .= "</table>";
			return $msg;
		}
		
		//---------------------------
		// 
		//---------------------------
		function dumpInfoMaree ($infoMaree)
		{
			$msg = "<table>";
			$msg .= '<tr><td colspan="2"> dumpInfoMaree </td></tr>';

			$lKeys = array_keys($infoMaree);
			$msg .= '<tr><td> Nb key: </td><td>'.count($lKeys).'</td></tr>';
			
			foreach ($infoMaree as $key => $value)
			{
				if (is_array($value))
				{
					$msg .= "<tr><td> ".$key." </td><td>";
					$msg .= $this -> dumpInfoMaree ($value);		
					$msg .= "</td></tr>";
				}
				else
					$msg .= "<tr><td> ".$key." </td><td>".$value."</td></tr>";
			}
			$msg .= "</table>";
			return $msg;
		}
		
		
		
		//---------------------------
		// 
		//---------------------------
		function isDemarreParUnPM ($infoMaree)
		{
			//-------------------------------------------------------
			// migration PHP4
			//-------------------------------------------------------
			$JOUR = 'jour';
			$UIDPORT = 'uidport';
			//-------------------------------------------------------
					
			 foreach ($infoMaree as $key => $value)
			 {
			 	if (strcmp ($key, $UIDPORT) == 0) continue;
			 	if (strcmp ($key, $JOUR) == 0) continue;
			 	
				$pattern = '/[0-9]{2}:[0-9]{2}:[0-9]{2}/';
				if (preg_match($pattern, $key))
				{
			 		if (strcmp ($infoMaree[$key]['type'], 'PM') == 0)
			 			return TRUE;
			 		return FALSE;
				}
			 }
		}
		
		
		function getNbMareeParJour($infoMaree)
		{
			$ret = 0;
			foreach ($infoMaree as $key => $value)
			{
				$pattern = '/[0-9]{2}:[0-9]{2}:[0-9]{2}/';
				if (preg_match($pattern, $key))
				{
					$ret ++;
				}
			}
			return $ret;
		}
		
		function getInfoWithRang($infoMaree, $rang)
		{
			$ret = null;
			$currang = 1;
			foreach ($infoMaree as $key => $value)
			{
				$pattern = '/[0-9]{2}:[0-9]{2}:[0-9]{2}/';
				if (preg_match($pattern, $key))
				{
					if ($currang == $rang)
					{
						return $value;
					}
					else
						$currang++;		
				}
			}
			return $ret;
		}
		
		
		function CalculSeuilEntreDeuxJours($infoJPrecedent, $infoJCourant, $Seuil, $rangMareePrecedent, $rangMareeSuivant)
		{
			$infoUneMareePrev = getInfoWithRang($infoJPrecedent, $rangMareePrecedent);
			if ($infoUneMareePrev == null)
				return '';
			
			$infoUneMareeNext = getInfoWithRang($infoJCourant,  $rangMareeSuivant);
			if ($infoUneMareeNext == null)
				return '';
			
			$changementDeDate = 24.0 * 60.0;
			
			return CalculSeuilCommun($infoUneMareePrev, $infoUneMareeNext, $Seuil, $changementDeDate);
		}
		
		function CalculSeuil($infoJCourant, $rangAvant, $Seuil)
		{
			$infoUneMareePrev = getInfoWithRang($infoJCourant, $rangAvant);
			if ($infoUneMareePrev == null)
				return '';
				
			$infoUneMareeNext = getInfoWithRang($infoJCourant, $rangAvant + 1);
			if ($infoUneMareeNext == null)
				return '';	

			$changementDeDate = 0.0;
			
			return CalculSeuilCommun($infoUneMareePrev, $infoUneMareeNext, $Seuil,  $changementDeDate);
		}
		
		
		function CalculSeuilCommun($infoUneMareePrev, $infoUneMareeNext, $hauteur,  $changementDeDate)
		{
			//-------------------------------------------------------
			// migration PHP4
			//-------------------------------------------------------
			$HEURE = 'heure';
			$HAUTEUR = 'hauteur';
			//-------------------------------------------------------
					
			$msg = '';
			
			$msg .= '[Hauteur : '.$hauteur.']<br/>';
			$msg .= '[Hauteur Prev : '.$infoUneMareePrev[$HAUTEUR].']<br/>';
			$msg .= '[Hauteur Next: '.$infoUneMareeNext[$HAUTEUR].']<br/>';
			
			if (($hauteur > $infoUneMareeNext[$HAUTEUR]) && ($hauteur > $infoUneMareePrev[$HAUTEUR]))
				return $msg.'Seuil hors maree (trop grand)';

			if (($hauteur < $infoUneMareeNext[$HAUTEUR]) && ($hauteur < $infoUneMareePrev[$HAUTEUR]))
				return $msg.'Seuil hors maree (trop petit)';
			
			
			$msg .= '[Heure Next = '.$infoUneMareeNext[$HEURE].']<br/>';
			$msg .= '[Heure Prev = '.$infoUneMareePrev[$HEURE].']<br/>';
			
			$heuremaree = ((HourToInt ($infoUneMareeNext[$HEURE]) + $changementDeDate) - HourToInt  ($infoUneMareePrev[$HEURE])) / 6.0;
			$marnage = abs ($infoUneMareeNext[$HAUTEUR] - $infoUneMareePrev[$HAUTEUR]);
			$douzieme = $marnage / 12.0;
			$intervalhoraire = 0.0;
			
			$msg .= '[Heure maree = '.$heuremaree.']<br/>';
			$msg .= '[marnage = '.$marnage.']<br/>';
			$msg .= '[douzieme de marnage = '.$douzieme.']<br/>';
				
			$deltaH = abs ($hauteur - $infoUneMareePrev[$HAUTEUR]);
			$NbDouzieme = $deltaH / $douzieme;
			$msg .= '[NbDouzieme = '.$NbDouzieme.']<br/>';
				
			if ($NbDouzieme < 1)
				$intervalhoraire = $heuremaree * $NbDouzieme;
			
			else if ($NbDouzieme < 3)
				$intervalhoraire = $heuremaree + $heuremaree * ($NbDouzieme - 1) / 2.0;
			
			else if ($NbDouzieme < 6)
				$intervalhoraire = 2.0 * $heuremaree + $heuremaree * ($NbDouzieme - 3) / 3.0;
			
			else if ($NbDouzieme < 9)
				$intervalhoraire = 3.0 * $heuremaree + $heuremaree * ($NbDouzieme - 6) / 3.0;
			
			else if ($NbDouzieme < 11)
				$intervalhoraire = 4.0 * $heuremaree + $heuremaree * ($NbDouzieme - 9) / 2.0;
			
			else if ($NbDouzieme < 12)
				$intervalhoraire = 5.0 * $heuremaree + $heuremaree * ($NbDouzieme - 11);
			
			else
				return 'Interval hors des douzieme';
			
			$msg .= '[intervalhoraire = '.$intervalhoraire.']<br/>';
			$msg .= '[Passage = '.($infoUneMareePrev[$HEURE] + $intervalhoraire).']<br/>';
				
			
			$heureSeuil = IntToHour  (HourToInt($infoUneMareePrev[$HEURE]) + $intervalhoraire);
			$ret = 'H:'.$hauteur.'m<br/><hr/>He:'.$heureSeuil;
			
			return $ret; 
		}
		
		
		
		
		
		
		
		function DisplayHauteurFromHeureDeuxJours($infoJPrecedent, $infoJCourant, $Heure, $rangMareePrecedent, $rangMareeSuivant)
		{
			$infoUneMareePrev = getInfoWithRang($infoJPrecedent, $rangMareePrecedent);
			if ($infoUneMareePrev == null)
				return '';
				
			$infoUneMareeNext = getInfoWithRang($infoJCourant,  $rangMareeSuivant);
			if ($infoUneMareeNext == null)
				return '';
				
			$changementDeDate = 24.0 * 60.0;
				
			return CalculHauteurFromHeureCommun($infoUneMareePrev, $infoUneMareeNext, $Heure, $changementDeDate);
		}
		
		function DisplayHauteurFromHeure($infoJCourant, $rangAvant, $Heure)
		{
			$infoUneMareePrev = getInfoWithRang($infoJCourant, $rangAvant);
			if ($infoUneMareePrev == null)
				return '';
		
			$infoUneMareeNext = getInfoWithRang($infoJCourant, $rangAvant + 1);
			if ($infoUneMareeNext == null)
				return '';
		
			$changementDeDate = 0.0;
			return CalculHauteurFromHeureCommun($infoUneMareePrev, $infoUneMareeNext, $Heure,  $changementDeDate);
		}
		
		function CalculHauteurFromHeureCommun($infoUneMareePrev, $infoUneMareeNext, $Heure,  $changementDeDate)
		{
			//-------------------------------------------------------
			// migration PHP4
			//-------------------------------------------------------
			$HEURE = 'heure';
			$HAUTEUR = 'hauteur';
			//-------------------------------------------------------
			
			$msg = '';
			$msgdbg = '';
				
				
			$heuremaree = ((HourToInt ($infoUneMareeNext[$HEURE]) + $changementDeDate) - HourToInt  ($infoUneMareePrev[$HEURE])) / 6.0;
			$marnage = ($infoUneMareeNext[$HAUTEUR] - $infoUneMareePrev[$HAUTEUR]);
			$douzieme = $marnage / 12.0;
			$intervalhoraire = 0.0;
				
			$deltaH = abs (HourToInt($infoUneMareePrev[$HEURE]) - HourToInt($Heure));
			if ($deltaH > $heuremaree * 6.0) // on est la veille
				$deltaH = abs (HourToInt($infoUneMareePrev[$HEURE]) - $changementDeDate - HourToInt($Heure));
			
			$NbHeureMaree = $deltaH / $heuremaree;
		
			$msgdbg .= '[heuremaree = '.$heuremaree.']<br/>';
			$msgdbg .= '[marnage = '.$marnage.']<br/>';
			$msgdbg .= '[douzieme = '.$douzieme.']<br/>';
			$msgdbg .= '[deltaH = '.$deltaH.']<br/>';
			$msgdbg .= '[intervalhoraire = '.$intervalhoraire.']<br/>';
			$msgdbg .= '$NbHeureMaree = '.$NbHeureMaree.']<br/>';
				
			if ($NbHeureMaree < 1)
				$VariationHauteur = 0 * $douzieme + ($NbHeureMaree - 0) * 1 * $douzieme;
				
			else if ($NbHeureMaree < 2)
				$VariationHauteur = 1 * $douzieme + ($NbHeureMaree - 1) * 2 * $douzieme;
				
			else if ($NbHeureMaree < 3)
				$VariationHauteur =  3 * $douzieme + ($NbHeureMaree - 2) * 3 * $douzieme;
				
			else if ($NbHeureMaree < 4)
				$VariationHauteur =  6 * $douzieme + ($NbHeureMaree - 3) * 3 * $douzieme;
				
			else if ($NbHeureMaree < 5)
				$VariationHauteur =  9 * $douzieme + ($NbHeureMaree - 4) * 2 * $douzieme;
				
			else if ($NbHeureMaree < 6)
				$VariationHauteur =  11 * $douzieme + ($NbHeureMaree - 5) * 1 * $douzieme;
				
			else
				return $msgdbg.': Interval hors des douzieme';
				
		
				
			$ret = 'He:'.$Heure.'<br/><hr/>H:'.((int)(($infoUneMareePrev[$HAUTEUR] + $VariationHauteur)*100.0))/100.0.'m';
				
			return $ret;
		}		
		
		
		
		function isHeureAvantEtale($Heure, $infoMaree, $rang)
		{
			//-------------------------------------------------------
			// migration PHP4
			//-------------------------------------------------------
			$HEURE = 'heure';
			$HAUTEUR = 'hauteur';
			//-------------------------------------------------------
			
			$infoUneMareePrev = getInfoWithRang($infoMaree, $rang);
			if ($infoUneMareePrev == null)
				return FALSE;
				
			$Hmaree = HourToInt($infoUneMareePrev[$HEURE]);
				
			if ($Heure < $Hmaree)
				return TRUE;
			return FALSE;
		}

?>