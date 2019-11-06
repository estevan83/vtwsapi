<?php


// Legge i parametri dal file
$path = dirname(__FILE__);
$params = file_get_contents(dirname(__FILE__) ."/params.json");

$params =  json_decode($params, true);

$usr = "webservice";
$ack = "MgLFMbfsr9Jt2O8j";
$url = "https://vtiger.bigbeat.eu";


include_once('vtwsclib/Vtiger/WSClient.php');

$client = new Vtiger_WSClient($url);
$login = $client->doLogin($usr, $ack);
if(!$login) 
    echo 'Login Failed';
else 
    echo 'Login Successful';


$module = 'Users';

$values = array(
    'user_name'=>'r', 
    'roleid'=>'H3',
    'user_password'=>'123456',  
    'confirm_password'=>'123456', 
    
    'last_name'=>'Gudarzi',    
    'first_name' => 'Cognome nome',
    'email1'=>'[hidden email]'
    
);
$record = $client->doCreate($module,$values);
if($record) {
    $recordid = $client->getRecordId($record['id']);
}
echo $recordid;