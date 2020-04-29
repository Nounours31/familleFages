<?php
include_once($_SERVER['DOCUMENT_ROOT']."/bricolage/include/ServeurFonctions.php");
include_once($_SERVER['DOCUMENT_ROOT']."/bricolage/include/SectionfonctionsSQL.php");
include_once($_SERVER['DOCUMENT_ROOT']."/bricolage/include/Traces.php");

function bricosendmail ($from, $to, $sujet, $message)
{
	ini_set("SMTP", GetSMTP()); 
	ini_set("sendmail_from", GetMailFrom()); 
		
	$rc=bricosendHTMLmail($to, $sujet, $message); 
	
     if ($rc)
     	     DebugTrace ("bricosendmail rc = OK");
     else
	     DebugTrace ("bricosendmail rc = KO");
	
	return $rc;
}

function bricosectionsendmail ($to, $sujet, $message)
{
	ini_set("SMTP", GetSMTP()); 
	ini_set("sendmail_from", GetMailFrom()); 
	
	$to = $to.", ".GetEmailMemberSection();
	
	DebugTrace ("Mail function to".$to);
	DebugTrace ("Mail function sujet".$sujet);
	DebugTrace ("Mail function message".$message);

	$rc=bricosendHTMLmail($to, $sujet, $message); 
	return $rc;
}

function bricorootsendmail ($to, $sujet, $message)
{
	ini_set("SMTP", GetSMTP()); 
	ini_set("sendmail_from", GetMailFrom()); 
		
	$to = $to.", ".GetEmailMemberSectionRoot();
	
	DebugTrace ("Mail function to".$to);
	DebugTrace ("Mail function sujet".$sujet);
	DebugTrace ("Mail function message".$message);
	$rc=bricosendHTMLmail($to, $sujet, $message); 
	return $rc;
}

function bricosendHTMLmail ($to, $sujet, $iMessage)
{
	ini_set("SMTP", GetSMTP()); 
	ini_set("sendmail_from", GetMailFrom()); 
	
     $message = "<html>
      <head>
       <title>Mail de la section bricolage</title>
      </head>
      <body>".$iMessage."
      </body>
     </html>";

     // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
     $headers  = "MIME-Version: 1.0" . "\r\n";
     $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";

     // En-têtes additionnels
     //$headers .= "To: ".$to."\r\n";
     //$headers .= "Cc: ".$to."\r\n";
     //$headers .= "Bcc: ".$to."\r\n";
     $headers .= "From: The section bricolage <".GetMailFrom().">"."\r\n";

     
     // Envoi
     $Debug = getDebugMailStatus();
     if ($Debug == 1)
     {
     	 $message = "Debug -- Destinataires initiaux = ".$to."<br/>".$message;
     	 $to = "pfs@3ds.com";
     }

     $rc=mail($to, $sujet, $message, $headers);
     return $rc;
}

function bricosendTXTmail ($to, $sujet, $message)
{
     $rc=bricosendTXTmail2($to, $sujet, $message, "");
     return $rc;
}

function bricosendTXTmail2 ($to, $sujet, $message, $headers)
{
	ini_set("SMTP", GetSMTP()); 
	ini_set("sendmail_from", GetMailFrom()); 
	
	
	// Envoi
    $Debug = getDebugMailStatus();
	if ($Debug == 1)
    {
     	 $message = "Debug -- Destinataires initiaux = ".$to."<br/>".$message;
     	 $to = "pfs@3ds.com";
    }
	DebugTrace ("bricosendTXTmail2 - To :".$to);
	DebugTrace ("bricosendTXTmail2 - Sujet :".$sujet);
	DebugTrace ("bricosendTXTmail2 - Message :".$message);
	DebugTrace ("bricosendTXTmail2 - Header :".$headers);
    $rc=mail($to, $sujet, $message, $headers);
    return $rc;
}


function bricosendHTMLmailWithFile ($email, $Sujet, $messageContenu, $NomFichier, $typemime, $ContenuFichierTxt) 
{

	ini_set("SMTP", GetSMTP()); 
	ini_set("sendmail_from", GetMailFrom()); 
	
	$from_name = "The section bricolage";
	$from_address = GetMailFrom();
	$subject = $Sujet;	
		
	//Create Mime Boundry
	$mime_boundary = "----DumpFile----".md5(time());
		
	//Create Email Headers
	$headers = "From: ".$from_name." <".$from_address.">\n";
	//$headers .= "Reply-To: ".$from_name." <".$from_address.">\n";
	
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: multipart/mixed; boundary=\"$mime_boundary\";\n";
	
	//Create Email Body (HTML)
	$Debug = getDebugMailStatus();
	if ($Debug == 1)
     {
     	 $messageContenu = "Debug -- Destinataires initiaux = ".$email."<br/>".$messageContenu;
     	 $email = "pfs@3ds.com";
     	}
	
    $message = $headers."--$mime_boundary\n";
	$message .= "Content-Type: text/html; charset=UTF-8\n";
	$message .= "Content-Transfer-Encoding: 8bit\n\n";
	$message .= "<html>\n";
	$message .= "<body>\n";
	$message .= $messageContenu;    
	$message .= "</body>\n";
	$message .= "</html>\n";
	
	$message .= "--$mime_boundary\n";
	$message .="Content-Type: ".$typemime."; name=\"".$NomFichier."\"\r\n";
	$message .="Content-Disposition: attachment; filename=\"".$NomFichier."\"\r\n\n";
	$message .=$ContenuFichierTxt;
	$message .= "--".$mime_boundary."\n";
	

	$mail_sent = @mail( $email, $subject, $message, $headers );	
	return $mail_sent;
}

?>

