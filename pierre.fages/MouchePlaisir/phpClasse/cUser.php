<?php
include_once 'cDB.php';

class cUser 
{
    
    function cUser() 
    {
    }

    function getInfoFromUID ($uid)
    {
        $c = new cDB();
        $aKeys = array();
        array_push($aKeys, 'nom');
        $res = $c ->select("select * from moucheplaisir_personne where uid = ".$uid, $aKeys);
        return $res[0];
    }

    function getNomFromUID ($uid)
    {
        $c = new cDB();
        $aKeys = array();
        array_push($aKeys, 'nom');
        $res = $c ->select("select * from moucheplaisir_personne where (uid=".$uid.")", $aKeys);
        return $res[0]['nom'];
    }
    
    function getHTMLSelect ()
    {
        $c = new cDB();
        $aKeys = array();
        array_push($aKeys, 'uid');
        array_push($aKeys, 'nom');
        $res = $c ->select("select * from moucheplaisir_personne", $aKeys);
        
        $msg = '<select name="cUser_Select">';
        foreach ($res as $row) {
                $msg .= '<option value="'.$row['uid'].'">'.$row['nom'].'           (Id:'.$row['uid'].')</option>';
        }
        $msg .= '</select>';
        return $msg;
    }
}
?>
