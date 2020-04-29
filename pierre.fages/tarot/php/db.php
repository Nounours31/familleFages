<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/tarot/php/BRILogger.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/tarot/php/BRIDBAccess.php';


/*
 {
    type: GetDBInfo,
    message: [ toto, titi, tata, ... ],
}
response:
[ 
    { nom: toto, score: 45.1, created: 0/1 // non - oui //}, 
    { nom: titi, score: 45.1, created: 0}, 
    ... 
]


{
    type: putDBInfo,
    message: [ 
        { nom: toto, score: 45.1}, 
        { nom: titi, score: 45.1}, 
        ... ],
}
response:
response:
[ 
    { nom: toto, status: OK/KO, created: yes/no}, 
    { nom: titi, status:OK/KO, created: yes/no}, 
    ... 
]

*/


//Make sure that it is a POST request.
if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
    throw new Exception('Request method must be POST!');
}

//Make sure that the content type of the POST request has been set to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if(strncmp($contentType, 'application/json', strlen('application/json')) != 0){
    throw new Exception('Content type must be: application/json. Contains : '.$contentType);
}

//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));


//Attempt to decode the incoming RAW post data from JSON.
$decoded = json_decode($content, true);

//If json_decode failed, the JSON is invalid.
if(!is_array($decoded)){
    throw new Exception('Received content contained invalid JSON!');
}

$dbaccess = new BRIDBAccess();

$retour=array();
if (strcmp ($decoded["type"], "GetDBInfo") == 0) {
    $arrayUserToGet = $decoded["message"];
    foreach ($arrayUserToGet as $key => $value) {
        $retourUnUser=array();
        $retourUnUser["nom"]=$value;
        $iscreated = false;
        $sql = "select * from tarot_users where qui='".$value."'";
        $rc = $dbaccess -> selectAsRest ($sql);
        if (count ($rc) == 0) {
            $sql = "insert into `pierre_fages`.`tarot_users` (`qui`, `score`) VALUES ('".$value."', '0.0');";
            $rc = $dbaccess -> insertAsRest ($sql);
            $retourUnUser["score"] = 0.0;
            $retourUnUser["created"] = 1;
        }
        else {
            $retourUnUser["score"] = $rc[0]["score"];
            $retourUnUser["created"] = 0;
        }
        array_push ($retour, $retourUnUser);
    }
}
elseif (strcmp ($decoded["type"], "putDBInfo") == 0) {
    $arrayUserToGet = $decoded["message"];
    foreach ($arrayUserToGet as $key => $value) {
        $retourUnUser=array();
        $retourUnUser["nom"]=$value;

        $iscreated = false;
        $sql = "select * from tarot_users where qui='".$value["nom"]."'";
        $rc = $dbaccess -> selectAsRest ($sql);
        if (count ($rc) == 0) {
            $sql = "insert into `pierre_fages`.`tarot_users` (`qui`, `score`) VALUES ('".$value["nom"]."', '".$value["score"]."')";
            $rc = $dbaccess -> insertAsRest ($sql);
            if ($rc > 0) {
                $retourUnUser["status"] = 'OK';
                $retourUnUser["created"] = 1;
            }
            else {
                $retourUnUser["status"] = 'KO';
                $retourUnUser["created"] = 0;
            }
        }
        else {
            $sql = "update `pierre_fages`.`tarot_users` set `score` = '".$value["score"]."' WHERE `tarot_users`.`qui`= '".$value["nom"]."'";
            $rc = $dbaccess -> insertAsRest ($sql);
            $retourUnUser["status"] = 'OK';
            if ($rc) {
                $retourUnUser["status"] = 'KO';
            }
            $retourUnUser["created"] = 0;
        }
        array_push ($retour, $retourUnUser);
    }
}
elseif (strcmp ($decoded["type"], "addDBInfo") == 0) {
    $arrayUserToGet = $decoded["message"];
    foreach ($arrayUserToGet as $key => $value) {
        $retourUnUser=array();
        $retourUnUser["nom"]=$value;

        $iscreated = false;
        $sql = "select * from tarot_users where qui='".$value["nom"]."'";
        print $sql;
        $rc = $dbaccess -> selectAsRest ($sql);
        if (count ($rc) == 0) {
            $sql = "insert into `pierre_fages`.`tarot_users` (`qui`, `score`) VALUES ('".$value["nom"]."', '".$value["score"]."')";
            print $sql;
            $rc = $dbaccess -> insertAsRest ($sql);
            if ($rc > 0) {
                $retourUnUser["status"] = 'OK';
                $retourUnUser["created"] = 1;
            }
            else {
                $retourUnUser["status"] = 'KO';
                $retourUnUser["created"] = 0;
            }
        }
        else {
            $sql = "update `pierre_fages`.`tarot_users` set score = score + '".$value["score"]."' WHERE `tarot_users`.`qui`= '".$value["nom"]."'";
            print $sql;
            $rc = $dbaccess -> insertAsRest ($sql);
            $retourUnUser["status"] = 'OK';
            if ($rc) {
                $retourUnUser["status"] = 'KO';
            }
            $retourUnUser["created"] = 0;
        }
        array_push ($retour, $retourUnUser);
    }
}
else {
     throw new Exception('invalid JSON as input no action associated!');
}

print (json_encode ($retour));