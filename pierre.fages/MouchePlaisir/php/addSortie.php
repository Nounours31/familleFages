<?php
ob_start();
include_once '../phpClasse/cDB.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    $test = isset ($_POST);
    if ($test !== true)
    {
        ob_end_clean();
        header ('location: ./sortie.php?cUser_Select=0');
        exit;
    }
    
    $inpouts = array();
    array_push($inpouts, 'Titre');
    array_push($inpouts, 'Description');
    array_push($inpouts, 'Date');
    array_push($inpouts, 'Organiseur');
    array_push($inpouts, 'Lieu');
    array_push($inpouts, 'cTypeSortie_Select');
    array_push($inpouts, 'Prix');
    foreach ($inpouts as $val)
        $test = $test && isset ($_POST[$val]);
    
    if ($test !== true)
    {
        ob_end_clean();
        header ('location: ./sortie.php?cUser_Select='.$_POST['Organiseur']);
        exit;
    }
    
    $c = new cDB();    
    $sql = "INSERT INTO moucheplaisir_sortie (titre, description, date, organisateur, lieu, type, prix) ";
    $sql = sprintf($sql." VALUES ('%s', '%s', '%s', %s, '%s', %s, %s)",
            $_POST['Titre'], 
            $_POST['Description'], 
            date('Y-m-d H:i:s', strtotime($_POST['Date'])), 
            $_POST['Organiseur'], 
            $_POST['Lieu'], 
            $_POST['cTypeSortie_Select'], 
            $_POST['Prix']);    
    $IdCnx = $c ->insertId($sql);// Faire quelque chose...
    
    $sql = "INSERT INTO moucheplaisir_participant (uid_sortie, persone, date) ";
    $sql = sprintf($sql." VALUES (%s, %s, '%s')",
            $IdCnx, 
            $_POST['Organiseur'], 
            date('Y-m-d H:i:s'));    
    $InfoSurToutesLesReunions = $c ->insert($sql);// Faire quelque chose...

    ob_end_clean();
    header ('location: ./sortie.php?cUser_Select='.$_POST['Organiseur']);
    exit;
?>
