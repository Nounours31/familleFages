<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cTypeSortie
 *
 * @author PFS
 */
class cTypeSortie {
    //put your code here
    /*
     * moucheplaisir_typesortie uid, type
     */
    
        function cUser() 
    {
    }

    function getHTMLSelect ()
    {
        $c = new cDB();
        $aKeys = array();
        array_push($aKeys, 'uid');
        array_push($aKeys, 'type');
        $res = $c ->select("select * from moucheplaisir_typesortie", $aKeys);
        
        $msg = '<select name="cTypeSortie_Select">';
        foreach ($res as $row) {
                $msg .= '<option value="'.$row['uid'].'">'.$row['type'].'     (id:'.$row['uid'].')</option>';
        }
        $msg .= '</select>';
        return $msg;
    }
}
