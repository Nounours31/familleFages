<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cParticipants
 *
 * @author PFS
 */
class cParticipants 
{
    function cParticipants() 
    {
    }

    function getNbParticipant($uid) 
    {
        $c = new cDB();
        $sql = sprintf("SELECT count(persone) as nbrow FROM moucheplaisir_participant where (uid_sortie=%s)" , $uid);
        $aKeys = array();
        array_push($aKeys, 'nbrow');
        $resp = $c ->select($sql, $aKeys);
        return $resp[0]['nbrow'];
    }
    
    function getAllParticipants($uidReunion, $tag)
    {
        $c = new cDB();
        // $sql  = "SELECT moucheplaisir_personne.nom as ".$tag.' ';
        // $sql .= "FROM moucheplaisir_personne, moucheplaisir_sortie, moucheplaisir_participant ";
        // $sql .= "Where ((moucheplaisir_personne.uid = moucheplaisir_participant.persone) and (moucheplaisir_participant.uid_sortie = moucheplaisir_sortie.uid) and (moucheplaisir_sortie.uid = ".$uidReunion."))";
     
        $sql  = 'SELECT Pers.nom as '.$tag.' from moucheplaisir_personne as Pers ';
        $sql .= 'inner join moucheplaisir_participant as Pa on (Pa.persone = Pers.uid) ';
        $sql .= 'inner join moucheplaisir_sortie as S on (S.uid = Pa.uid_sortie) ';
        $sql .= 'where (S.uid = '.$uidReunion.');';
        
        print ('<script type="text/javascript">PrintDebug("'.$sql.'");</script>');
        $aKeys = array();
        array_push($aKeys, $tag);
        
        $Avant = time();
        $resp = $c ->select($sql, $aKeys);
        $Apres = time();
        return $resp;
    }
}
