<?php
include_once 'entete.php';
include_once '../phpClasse/cDB.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
header("Content-Type: text/plain"); // Utilisation d'un header pour spécifier le type de contenu de la page. Ici, il s'agit juste de texte brut (text/plain). 

$IdSortie = (isset($_POST["uid_sortie"])) ? $_POST["uid_sortie"] : NULL;
$IdPersonne = (isset($_POST["uid_persone"])) ? $_POST["uid_persone"] : NULL;

if (isset($_POST["uid_sortie"]) && isset($_POST["uid_persone"])) 
{
    $c = new cDB();
    $sql = sprintf("select uid from moucheplaisir_participant where ((uid_sortie=%s) and (persone=%s))", $IdSortie, $IdPersonne);
    $keys = array();
    array_push($keys, 'uid');
    $InfoSurToutesLesReunions = $c -> select($sql, $keys);
    if (count ($InfoSurToutesLesReunions) == 0)
    {
        $sql = sprintf("INSERT INTO moucheplaisir_participant (uid_sortie, persone, date) VALUES (%s, %s, '%s')",
             $IdSortie, $IdPersonne, date('Y-m-d H:i:s'));

        $InfoSurToutesLesReunions = $c ->insert($sql);// Faire quelque chose...
        echo '200: OK '.$InfoSurToutesLesReunions;
    }
    else
    {
        echo '400: Deja inscrit';
    }
} 
else {
	echo '400: Insert KO [debug['.$IdSortie.']['.$IdPersonne.']]';
}
?>
