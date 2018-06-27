<?php
class cDB 
{
    // const KO ? pb free ou php 4 ?
    var $hostname = 'localhost';//'sql.free.fr';
    var $username = 'root'; //"pierre.fages";
    var $password = ''; //"frifri";
    var $database = "pierre_fages";
    var $dbCnx;
    var $isLocalHost = FALSE;

    function cDB() 
    {
        $this -> dbCnx = NULL;
        if ($this -> isLocalHost == TRUE)
        {
            $this -> hostname = 'localhost';
            $this -> username = 'root'; 
            $this -> password = ''; 
            $this -> database = "pierre_fages";
        }
        else 
        {
            $this -> hostname = 'sql.free.fr';
            $this -> username = "pierre.fages";
            $this -> password = "frifri";
            $this -> database = "pierre_fages";
        }
    }

    function initDB() 
    {
        $this -> dbCnx = mysql_connect($this -> hostname, $this ->username, $this ->password);
        if (!$this->dbCnx) 
        {
            die('Connexion impossible : ' . mysql_error());
            return;
        }
    }

    function closeDB() 
    {
        if (!is_null($this ->dbCnx))
            mysql_close($this -> dbCnx);
        
        $this -> dbCnx = NULL;
    }

    function select ($sql, $aKeys) 
    {
        $this-> initDB();
        $result = mysql_db_query($this -> database, $sql);
        if (!$result) {
            $message  = 'Requête invalide : ' . mysql_error() . "<br/>";
            $message .= 'Requête complète : -->'.$sql.'<--';
            die($message);
        }

        $resp = array();
        $indice = 0;
        $hasvalue = 0;
        
        while ($row = mysql_fetch_assoc($result)) {
            $uneligne = array();
            
            foreach ($aKeys as $unChamps) {
                if (isset($row[$unChamps]))
                {
                    $uneligne[$unChamps] = $row[$unChamps];
                    $hasvalue = 1;
                }
            }
            
            if ($hasvalue)
            {
                $resp[$indice] = $uneligne;
                $indice++;
            }
        }
        
        mysql_free_result($result);
        
        $this-> closeDB();
        return $resp;
    }
    
    function insert ($sql) 
    {
        $this-> initDB();
        $result = mysql_db_query($this -> database, $sql);
        if (!$result) {
            $message  = 'Requête invalide : ' . mysql_error() . "<br/>";
            $message .= 'Requête complète : -->'.$sql.'<--';
            die($message);
        }
        $retour = mysql_affected_rows();
        $this-> closeDB();
        return 'Insert :'.$retour;
    }

    function insertId ($sql) 
    {
        $this-> initDB();
        $result = mysql_db_query($this -> database, $sql);
        if (!$result) {
            $message  = 'Requête invalide : ' . mysql_error() . "<br/>";
            $message .= 'Requête complète : -->'.$sql.'<--';
            die($message);
        }
        $retour = mysql_insert_id();
        $this-> closeDB();
        return $retour;
    }
}
?>
