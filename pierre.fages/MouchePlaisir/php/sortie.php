<?php
include_once 'entete.php';
include_once '../phpClasse/cDB.php';
include_once '../phpClasse/cUser.php';
include_once '../phpClasse/cTypeSortie.php';
include_once '../phpClasse/cSortie.php';
include_once '../phpClasse/cParticipants.php';
include_once '../phpClasse/cLog.php';

$IndentifiedUser = 0;
if (!isset($_GET) || !isset($_GET['cUser_Select']) || !isset($_GET['cUser_Select']))
{
    $IndentifiedUser = -1;
}
 else 
{
    $IndentifiedUser = $_GET['cUser_Select'];
}
if (($IndentifiedUser == -1) && (!isset($_POST) || !isset($_POST['cUser_Select']) || !isset($_POST['cUser_Select'])))
{
        print ('Invalid User <br/>');
        print_r($_POST);
        exit();
}
 else if ($IndentifiedUser == -1) {
    $IndentifiedUser = $_POST['cUser_Select'];
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Test agendas</title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        
        <link rel="stylesheet" href="../css/debug.css"/>
        <link rel="stylesheet" href="../css/W3cSchoolTable.css"/>    
        
        <script type="text/javascript" src="../js/ajax_XMLHttpRequest_Tools.js"></script>
        <script type="text/javascript" src="../js/ajax_inscription.js"></script>
        <script type="text/javascript" src="../js/debug.js"></script>
        
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
        <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
        <script src="http://code.jquery.com/ui/1.11.2/jquery-ui.js"></script>            
        <script>
            $(function() {
                $( "#datepicker" ).datepicker( {
                            dateFormat: "yy-mm-dd"
                        });
            });
        </script>
        
    </head>
    <body class="W3CSchool">
        <div id="div_Frame_debug">
            <fieldset><legend> DEBUG </legend>
                <div class="debug" id="div_debug">
                    <?php 
                        $myLog = new cLog();
                        $myLog ->log("********************************************************************** Entree sortie.php");
                        print ("Start Debug<br/>");
                        print_r ($_POST);
                        print ("<br/>");
                        print ("End Debug<br/>");
                    ?>
                </div>
            </fieldset>
        </div>
        <div>
            <br/>
            <br/>
            Bonjour:
            <?php
                $user = new cUser();
                print ($user -> getNomFromUID($IndentifiedUser));
            ?>
            <br/>
            <hr class="W3CSchool"/>
            <br/>
            <font class="W3CSchool">Reunions planifi&eacute;es</font><br>
            <div class="W3CSchool">
                <table class="W3CSchool">
                    <?php  
                        $myLog = new cLog();
                        $TagStartDebugAllAffichage = time();
                        $ALIAS_PARTICIPANT = 'Participants';
                        
                        //------------------------------------------------------
                        // recherche info base
                        //------------------------------------------------------
                        $TagStartDebug = time();
                        $sorties = new cSortie();
                        $aKeys = array();
                        array_push($aKeys, 'uid');
                        array_push($aKeys, 'titre');
                        array_push($aKeys, 'description');
                        array_push($aKeys, 'date');
                        array_push($aKeys, 'organisateur');
                        array_push($aKeys, 'lieu');
                        array_push($aKeys, 'type');
                        array_push($aKeys, 'prix');
                        $myLog ->log('Avant getAllSortieSortedByDate');
                        $InfoSurToutesLesReunions = $sorties -> getAllSortieSortedByDate($aKeys);
                        $myLog ->log('Apres getAllSortieSortedByDate');

                        
                        //------------------------------------------------------
                        // je remplace dans resp les info organisateur (uid vers une persone) et type (uid vers un type de reu
                        //------------------------------------------------------
                        $myLog ->log('Avant convertUidToName');
                        //$InfoSurToutesLesReunions = $sorties -> convertUidToName($InfoSurToutesLesReunions);
                        $myLog ->log('Apres convertUidToName');
                        $NbReunion = count ($InfoSurToutesLesReunions);
                        $TagEndDebug = time();
                        $myLog ->logPerfo("sortie.php [Fin de la requete getAllSortieSortedByDate] ", $TagStartDebug, $TagEndDebug);

                        
                        //------------------------------------------------------
                        // mise en place du tableau a afficher
                        //------------------------------------------------------
                        $TableauAAfficher = array();
                        
                        //------------------------------------------------------
                        // Premiere colone = les titres des info a afficher
                        //------------------------------------------------------
                        $indiceLigne = 0;
                        foreach ($aKeys as $val) {
                            $TableauAAfficher[$indiceLigne][0] = $val;
                            $indiceLigne++;
                        }
                        $TableauAAfficher[$indiceLigne++][0] = $ALIAS_PARTICIPANT;
                        
                        
                        //------------------------------------------------------
                        // recherche de la reunion ou il y a le plus de participant
                        //------------------------------------------------------
                        $TagStartDebug = time();
                        $NbMaxParticipants = 0;
                        $tableParticipants = new cParticipants();
                        foreach ($InfoSurToutesLesReunions as $row) 
                        { 
                            $uidReunion = $row['uid'];
                            $NbParticipants = $tableParticipants -> getNbParticipant($uidReunion);
                            if ($NbParticipants > $NbMaxParticipants)
                                $NbMaxParticipants = $NbParticipants;
                        }
                        $TagEndDebug = time();
                        $myLog ->logPerfo("sortie.php [Fin de la recheche du max Participant] ", $TagStartDebug, $TagEndDebug);

                        
                        
                        //------------------------------------------------------
                        // dans les autre colone copie des info de la DB transposees
                        //------------------------------------------------------
                        $indiceLigne = 0;
                        $IndiceColone = 0; 
                        $TagStartDebug = time();
                        foreach ($InfoSurToutesLesReunions as $value) 
                        { 
                            $indiceLigne = 0;
                            $IndiceColone++;
                            
                            // copie des info de reunion
                            foreach ($value as $val) {
                                $TableauAAfficher[$indiceLigne][$IndiceColone] = $val;
                                $indiceLigne++;
                            }
                            
                            // ajout des participants sur la meme colone (meme reunion) mais la ligne en dessous 
                            $uidReunion = $value['uid'];
                            $allParticipants = $tableParticipants -> getAllParticipants($uidReunion, 'NomParticipant');
                            
                            $i = 0;
                            foreach ($allParticipants as $UnParticipant) 
                                $TableauAAfficher[$indiceLigne + $i++][$IndiceColone] =  $UnParticipant['NomParticipant'];

                            while ($i < $NbMaxParticipants)
                                $TableauAAfficher[$indiceLigne + $i++][$IndiceColone] = '---';
                        }
                        $indiceLigne = $indiceLigne + $i;
                        $TagEndDebug = time();
                        $myLog ->logPerfo("sortie.php [Fin de la transposition de TableauAAfficher] ", $TagStartDebug, $TagEndDebug);

                        
                        //------------------------------------------------------
                        // En bas je propose les inscriptions
                        //------------------------------------------------------
                        $TagStartDebug = time();
                        $TableauAAfficher[$indiceLigne][0] = 'Inscriptions';
                        for ($IndiceColone = 1; $IndiceColone < $NbReunion + 1; $IndiceColone++)
                        {
                            $RowContent = "";
                            
                            $ajax_uidSortie = $TableauAAfficher[0][$IndiceColone];
                            $ajax_divAModifier = 'output_'.$ajax_uidSortie;
                            $ajax_uidUserAInscrire = $IndentifiedUser;
                            $ajax_URL = 'ajax_inscription.php';

                            $RowContent .= '<div id="'.$ajax_divAModifier.'">';	
                            $RowContent .= '<button onclick="request(callbackInscription,\''.$ajax_divAModifier.'\',\''.$ajax_URL.'\', \''.$ajax_uidSortie.'\', \''.$ajax_uidUserAInscrire.'\');">';
                            $RowContent .= "Je m'inscris</button>";
                            $RowContent .= '</div>';	
                            
                            $TableauAAfficher[$indiceLigne][$IndiceColone] = $RowContent;
                        }
                        $NbTotalDeLignes = $indiceLigne;
                        $TagEndDebug = time();
                        $myLog ->logPerfo("sortie.php [Fin de affichage des bouttons]", $TagStartDebug, $TagEndDebug);

                        
                        //------------------------------------------------------
                        // La matrice est pleine - Affichage a l'aide d'unetable html
                        //------------------------------------------------------
                        $TagStartDebug = time();
                        $RowSpanActive = 0;
                        $PremiereLigne = 1;
                        for ($i = 1; $i < $NbTotalDeLignes + 1; $i++) // $i demarre a 1 pour zapper les UID
                        {
                            if (($PremiereLigne != 1) && ($i & 1))
                                print ('<tr class="W3CSchool">');
                            else 
                            {
                                print ('<tr class="W3CSchool_alt">');                                
                            }
                            $j = 0;
                            if ($RowSpanActive > 0) // je suis dans un rowspan je dois zapper le premier element du tab
                            {
                                $j = 1;
                                $RowSpanActive++;
                                if ($RowSpanActive >= $NbMaxParticipants) // j'ai fini le rowspan
                                    $RowSpanActive = 0;
                            }
                            for (; $j < $NbReunion + 1; $j++)
                            {
                                $DerniereLigne = 0;
                                if ($i == ($indiceLigne - 1))
                                    $DerniereLigne = 1;
                                
                                $RowContent = "";
                                if ($PremiereLigne == 1) {
                                    $RowContent = '<th class="W3CSchool" ';
                                }
                                else {
                                    if ($j == 0)
                                        $RowContent = '<td class="W3CSchool" id="PremiereColone" ';
                                    else
                                        $RowContent = '<td class="W3CSchool" ';
                                }
                                
                                if (strcmp($TableauAAfficher[$i][$j],$ALIAS_PARTICIPANT) == 0)
                                {
                                    $RowContent .= 'rowspan="'.$NbMaxParticipants.'">';
                                    $RowSpanActive = 1;
                                }
                                else
                                    $RowContent .= '>';
                                
                                $RowContent .= $TableauAAfficher[$i][$j].'</td>';
                                print ($RowContent);
                            }
                            print ('</tr>');
                            $PremiereLigne = 0;
                        }     
                        $TagEndDebug = time();
                        $myLog ->logPerfo("sortie.php [Fin de affichage de la table]", $TagStartDebug, $TagEndDebug);

                        $TagEndDebugAllAffichage = time();
                        $myLog ->logPerfo("sortie.php [Fin Total PHP]", $TagStartDebugAllAffichage, $TagEndDebugAllAffichage);

                        $myLog ->log("*********************** fin partie 1 de  sortie.php");
                    ?>
                </table>
            </div>
            <br><br>
        </div>
        <div>
            <br/>
            <hr class="W3CSchool"/>
            <br/>
            <font class="W3CSchool">Proposer une r&eacute;union</font>
            <br/>
            <div>
                <form action="addSortie.php" method="post">
                    <table class="W3CSchool">
                        <tr>
                            <td>Titre</td><td><input type="text" name="Titre"/></td>
                        </tr>
                        <tr>
                            <td>description detaill&eacute;e</td><td><input type="text" name="Description"/></td>
                        </tr>
                        <tr>
                            <td>date</td><td><input type="datetime" name="Date" id="datepicker"/></td>
                        </tr>
                        <tr>
                            <td>Organisateur</td><td>            
                                                    <?php
                                                        $user = new cUser();
                                                        print ($user -> getNomFromUID($IndentifiedUser));
                                                        print ('<input type="hidden" name="Organiseur" value="'.$IndentifiedUser.'"/>');
                                                    ?>
                                                </td>
                        </tr>
                        <tr>
                            <td>Lieu</td><td><input type="text" name="Lieu"/></td>
                        </tr>
                        <tr>
                            <td>Type</td><td><?php $x = new cTypeSortie(); print ($x ->getHTMLSelect()); ?></td>
                        </tr>
                        <tr>
                            <td>Prix</td><td><input type="number" name="Prix">&euro;</input></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td><td> <input type="submit" name="Proposer" value="OK" /> </td>
                        </tr>
                    </table>
                </form>
                <?php
                    $myLog ->log("####################################################################### Entree seconde partie sortie.php");
                ?>
            </div>
        </div>
    </body>
</html>



