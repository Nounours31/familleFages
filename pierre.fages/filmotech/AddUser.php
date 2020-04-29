<?php
include_once ($_SERVER['DOCUMENT_ROOT']."/bricolage/include/GlobalEnv.php");
include_once($_SERVER['DOCUMENT_ROOT']."/bricolage/include/fonctionsSQL.php");
include_once($_SERVER['DOCUMENT_ROOT']."/bricolage/include/Traces.php");
include_once($_SERVER['DOCUMENT_ROOT']."/bricolage/include/ComptefonctionsSQL.php");

$response = '<SBricoMessage><SBricoAddUserWsMsgOut>';
if(strtolower($_SERVER['REQUEST_METHOD']) == 'post')
{
    $xmlstr = file_get_contents ('php://input');
    $root =  $sxi = new SimpleXmlIterator($xmlstr, null, false);
    
    //--------------------------------------------------
    // <users>
    //     <user>
    //           <nom> ...
    //     </user>
    //     <user>
    //           <nom> ...
    //     </user>
    // </users>
    //--------------------------------------------------
//    foreach ($root -> children() as $BricoMessage) {
        foreach ($root -> children() as $BricoMessageIn) {
            foreach ($BricoMessageIn -> children() as $users) {
                $response .= "<users>";
                foreach ($users -> children() as $user) {

                    $response .= '<user nom="'.$user->nom.'"';
                    $response .= ' prenom="'.$user->prenom.'"';
                    $response .= ' matricule="'.$user -> matricule.'"';
                    $response .= ' entreprise="'.$user -> entreprise.'"';
                    $response .= ' email="'.$user -> email.'"';
                    $response .= ' message="'.$user -> message.'">';

                    //--------------------------------------------------
                    // check existance USER
                    //--------------------------------------------------
                    $sql = "select UID from user where Matricule = ".$user -> matricule;
                    $base = connectMaBase();
                    $db_messages = mysql_query($sql);
                    $num_rows = mysql_num_rows($db_messages);
                    mysql_close($base);

                    if ($num_rows > 0)
                    {
                        $response .=  "<error>E_EXIST</error>";
                        $response .=  "<status>User existe deja</status>";
                    }
                    else 
                    {
                        //-----------------------------------------------
                        // recherche de l'entreprise
                        //-----------------------------------------------
                        $sql = "select UID from entreprise where Nom = '".$user -> entreprise."'";
                        $base = connectMaBase();
                        $db_messages = mysql_query($sql);
                        mysql_close($base);
                        $fval = mysql_fetch_array($db_messages);

                        if (!isset($fval['UID']))
                        {
                            $response .=  "<sql>".$sql."</sql>";
                            $response .=  "<error>E_FAIL</error>";
                            $response .=  "<status>Entreprise n'existe pas: ".$user -> entreprise."</status>";
                        }
                        else
                        {
                            $UIDEntrepriseV2 = $fval["UID"];
                            //$sql = "insert into user (Nom, Prenom, email, email_perso, PasswdMD5, okreglement, UpdateReglement, Matricule, ce_user_status, UID_Entreprise, Remarque) values (";
                            $sql = "insert into user (Nom, Prenom, email, email_perso,              okreglement, UpdateReglement, Matricule, ce_user_status, UID_Entreprise, Remarque) values (";
                            $sql = $sql."'".$user -> nom."', ";
                            $sql = $sql."'".$user -> prenom."', ";
                            $sql = $sql."'".$user -> email."', ";
                            $sql = $sql."'', ";
                            // $sql = $sql."'c4ca4238a0b923820dcc509a6f75849b', "; // passwd = 1
                            $sql = $sql."0, "; // OKreg = 0
                            $sql = $sql."0, "; // update = 0
                            $sql = $sql.$user -> matricule.", ";
                            $sql = $sql."'valide', "; // ce_user_status
                            $sql = $sql.$UIDEntrepriseV2.", ";
                            $sql = $sql."'".$user -> message." - Creation user par HTTP en date du ".date("Y-m-d H:i:s")."'";
                            $sql = $sql. ")";
                            $response .=  "<sql>".$sql."</sql>";

                            $base = connectMaBase();
                            $insertRC = mysql_query($sql);
                            mysql_close($base);

                            if ($insertRC === true)
                            {
                                $base = connectMaBase();
                                $db_messages = mysql_query("select UID from user where Matricule = ".$user -> matricule);
                                mysql_close($base);
                                $info = mysql_fetch_array($db_messages);
                                CreationCompteFromId($info ['UID']);

                                $response .=  "<error>S_OK</error>";
                                $response .=  "<status>OK Creation user - compte initialise</status>";
                            }
                            else
                            {
                                $response .=  "<error>E_FAIL</error>";
                                $response .=  "<status>Insert user KO</status>";
                            }
                        }
                    }
                    $response .= "</user>";
                }
                $response .= "</users>";
            }
        }
    }
//}
$response .= "</SBricoAddUserWsMsgOut></SBricoMessage>";
header("Content-type: text/xml; charset=utf-8");
echo $response;
?>

