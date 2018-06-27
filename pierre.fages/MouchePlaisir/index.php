<?php
include_once 'php/entete.php';
include_once 'phpClasse/cUser.php';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Autentification</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <link rel="stylesheet" href="css/autentification.css"/>
    </head>
    <body>
        <div class="autentification" id="main">
            Pour continuer il faut s'autentifier ... et un point c'est tout !
            liste des users possible:
            
            <form action="php/sortie.php" method="POST">
                <div class="autentification" id="table">
                    <table class="autentification">
                        <tr class="autentification">
                            <td class="autentification">User</td> 
                            <td class="autentification">
                                <?php
                                    $c = new cUser();
                                    print ($c ->getHTMLSelect());
                                ?>
                                <br/>
                                <input type="submit" name="Submit" value="Ok" >
                            </td>
                        </tr> 
                    </table>
                </div>
            </form>
        </div>
    </body>
</html>