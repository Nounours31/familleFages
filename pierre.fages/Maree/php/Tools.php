<?php
		//==========================================================================================
		// les methodes
		//==========================================================================================
		function getDisplayInfoFromInfoMaree ($infoMaree, $rangAAfficher)
		{
			//-------------------------------------------------------
			// migration PHP4
			//-------------------------------------------------------
			$JOUR = 'jour';
			$UIDPORT = 'uidport';
			$HEURE = 'heure';
			$HAUTEUR = 'hauteur';
			$COEFCORRIGE = 'coefCorrige';
			$TYPE = 'type';
			$COEF = 'coef';
			//-------------------------------------------------------
			
			$msgDebug = '';
			$msg = '';
			$msgCoef = '';
			$rangMaree = 1;
			$bAddCoef = FALSE;
			
			foreach ($infoMaree as $key => $value)
			{
				if (strcmp ($key, $UIDPORT) == 0) continue;
				if (strcmp ($key, $JOUR) == 0) continue;
					
				
				$msgDebug .= '[key:'.$key.']';
				
				$pattern = '/[0-9]{2}:[0-9]{2}:[0-9]{2}/';
				if (preg_match($pattern, $key))
				{
					//---------------------------------
					// suis je au bon endroit ?
					//---------------------------------
					if ($rangMaree != $rangAAfficher)
					{
						$rangMaree++;
						continue;
					}
					
					$msg .= '['.$infoMaree[$JOUR].']<br/>';
					$infoUneMaree = $value;
					foreach ($infoUneMaree as $keyUneMaree => $valueUneMaree)
					{
						$msgDebug .= '[keyUneMaree:'.$keyUneMaree.'/ valueUneMaree:'.$valueUneMaree.']';
						if (strcmp ($keyUneMaree, $HAUTEUR) == 0) $msg .= '['.((int)($valueUneMaree*100.0)/100.0).' m]<br/>';
						if (strcmp ($keyUneMaree, $HEURE) == 0) $msg .= '['.$valueUneMaree.']<br/>';
						if ((strcmp ($keyUneMaree, $TYPE) == 0) && (strcmp ($valueUneMaree, "PM") == 0))$bAddCoef = TRUE;
						if ((strcmp ($keyUneMaree, $COEF) == 0) || (strcmp ($keyUneMaree, $COEFCORRIGE) == 0))
						{
							if (strcmp ($keyUneMaree, $COEF) == 0)
							{
								if (strlen ($msgCoef) == 0)
									$msgCoef = '[C='.$valueUneMaree.' <br/> ';
								else
									$msgCoef = '[C='.$valueUneMaree.' <br/> '.$msgCoef.'<br/>';
							}
							else
							{
								if (strlen ($msgCoef) == 0)
									$msgCoef = 'C='.$valueUneMaree.']';
								else
									$msgCoef = $msgCoef.'C='.$valueUneMaree.']<br/>';
							}
						}
					}
					break;
				}
			}
				
			if ($bAddCoef)
				$msg .= $msgCoef;
			
			
			return $msg;
		} 
		
		
		
		function HourToInt ($str)
		{
			$tab = explode (":", $str);
			if (count($tab) < 2)
				$tab = explode (".", $str);
				
			$ret = $tab[0] * 60 +  $tab[1];
			return $ret;
		}
		
		function IntToHour ($i)
		{
			$ret ='';
			
			$heure = (int)($i / 60.0);
			$minute = (int)($i - ($heure * 60.0));
			$LaVeille = FALSE;
			$LeLendemain = FALSE;
			
			if ($minute < 0.0)
			{
				$heure -= 1.0;
				$minute += 60.0;
			}
				
			if ($heure > 24.0)
			{
				$heure -= 24.0;
				$LeLendemain = TRUE;
			}
				
			
			if ($heure < 0.0)
			{
				$heure += 24.0;
				$LaVeille = TRUE;
			}
			
			if ($heure > 24.0)
			{
				$heure -= 24.0;
				$LeLendemain = TRUE;
			}
			
			if ($heure < 10)
				$heure = '0'.$heure;
			
			if ($minute < 10)
				$minute = '0'.$minute;
			
			$ret .= $heure.':'.$minute.':00'; 
			if ($LaVeille)
				$ret = '[LaVeille]'.$ret;
			if ($LeLendemain)
				$ret = '[Lendemain]'.$ret;
				
			return $ret;
		}
		
		function dumpTab ($i)
		{
			$ret = '';
			if (is_array($i))
			{
				foreach ($i as $key => $value)
				{
					$ret .= '[Key='.$key.' # Value=';
					if (is_array($value))
						$ret .= dumpTab($value);
					else
						$ret .= $value;
					$ret .= ']'.PHP_EOL;
				}
			}
			return $ret;
		}
?>