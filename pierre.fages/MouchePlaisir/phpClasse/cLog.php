<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cLog
 *
 * @author PFS
 */
class cLog {
    var $isLogOn = FALSE;
    
    function cUser() 
    {
    }
    
    function log($msg)
    {
        if ($this -> isLogOn == FALSE)
            return;
        
        $handle = fopen("E:\\WS\\Adele\\A_PagePerso\\src\\trace.txt", "a");
        fwrite($handle, date("H:i:s").'# '.$msg."\r\n");
        fflush($handle);
    }

    function logPerfo($msg, $from, $to)
    {
        if ($this -> isLogOn == FALSE)
            return;
        
        $this -> log ($msg.' [duree = '.($to - $from).']');
    }
    
    function xLog_r($array)
    {
        $tableau = '';
        $keys = array();
        $keys = array_keys ($array);
        
        $tableau .= "Tab {";
        foreach ($keys as $key)
        {
            $tableau .= "\tTab[".$key."] => {".$array[$key]."}";
        }
        $tableau .= "}";
        return $tableau;
    }

    function log_r_r($msg, $array)
    {
        if ($this -> isLogOn == FALSE)
            return;
        
        $tableau = '';
        $keys = array();
        $keys = array_keys ($array);
        
        $tableau .= "Tab {";
        foreach ($keys as $key)
        {
            $tableau .= "\tTab[".$key."] => {".  $this->xLog_r($array[$key])."}\n";
        }
        $tableau .= "}";
        $this -> log ($msg.$tableau);
    }

}
