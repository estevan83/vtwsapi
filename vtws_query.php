<?php
// non va da sistemare
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

chdir("..");


include_once 'config.php';
include_once 'include/Webservices/Relation.php';

include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

include_once 'include/Webservices/Query.php';
include_once 'include/Webservices/Utils.php';


$user = new Users();
$user->retrieveCurrentUserInfoFromFile(1);

try {
        $q = "SELECT * FROM Contacts limit 0,1";
        $q = $q . ';'; // NOTE: Make sure to terminate query with ;
        $records = vtws_query($q, $user);
        print_r($records);
        echo "record count :" . count($records);

} catch (WebServiceException $ex) {
        echo $ex->getMessage();
}