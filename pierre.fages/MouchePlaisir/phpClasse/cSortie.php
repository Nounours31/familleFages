<?php
include_once 'cLog.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cSortie
 *
 * @author PFS
 */
class cSortie {
    //put your code here
    
    function cSortie() 
    {
        $this -> dbCnx = NULL;
    }
    
    function getAllSortieSortedByDate($aKeys)
    {
        $c = new cDB();
        $log = new cLog();

        $tableSortie        = 'moucheplaisir_sortie as so';
        $tablePersone       = 'moucheplaisir_personne as pe';
        $tableTypeSortie    = 'moucheplaisir_typesortie as ty';
        
        $sql = 'select';
        $sql .= ' so.uid, so.titre, so.description, so.date, so.organisateur as Xorganisateur, so.lieu, so.type as Xtype, so.prix';
        $sql .= ', concat(pe.nom, "<br/>", pe.tel) as organisateur ';
        $sql .= ', ty.type as type';
        $sql .= ' from '.$tableSortie.','.$tablePersone.','.$tableTypeSortie;
        $sql .= ' where ((so.organisateur = pe.uid) and (so.type = ty.uid)) order by so.date desc';
        
        $log ->log('The SQL ... '.$sql);
        $resp = $c ->select($sql, $aKeys);
        $log ->log_r_r('The SQL ... response ... ', $resp);
        return $resp;
    }
    
    function convertUidToName($resp)
    {
        $c = new cDB();
        for ($IndiceConvertUidToString = 0;  $IndiceConvertUidToString < count ($resp); $IndiceConvertUidToString++) 
        {
            $uidToConvertToString = $resp[$IndiceConvertUidToString]['type'];
            $sql = "SELECT type FROM  moucheplaisir_typesortie where (uid=".$uidToConvertToString.")";
            $aLocalKeyForConvertion = array();
            array_push($aLocalKeyForConvertion, 'type');
            $respTemp = $c ->select($sql, $aLocalKeyForConvertion);
            $resp[$IndiceConvertUidToString]['type'] = $respTemp[0]['type'];

            $uidToConvertToString = $resp[$IndiceConvertUidToString]['organisateur'];
            $sql = "SELECT nom, tel FROM  moucheplaisir_personne where (uid=".$uidToConvertToString.")";
            $aLocalKeyForConvertion = array();
            array_push($aLocalKeyForConvertion, 'nom');
            array_push($aLocalKeyForConvertion, 'tel');
            $respTemp = $c ->select($sql, $aLocalKeyForConvertion);
            $resp[$IndiceConvertUidToString]['organisateur'] = $respTemp[0]['nom'].' <br/>['.$respTemp[0]['tel'].']' ;
        }
        return $resp;
    }
}
