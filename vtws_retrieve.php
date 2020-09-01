<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

chdir("..");


include_once 'config.php';
include_once 'include/Webservices/Relation.php';

include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

include_once 'include/Webservices/Retrieve.php';
include_once 'include/Webservices/Utils.php';


$recordId = '26718';


$current_user = CRMEntity::getInstance('Users');
$current_user->retrieveCurrentUserInfoFromFile(1);


$record = array();
try {

        $wsid = vtws_getWebserviceEntityId('Contacts',$recordId); // Module_Webservice_ID x CRM_ID
               
        $record = vtws_retrieve($wsid, $current_user);
echo "<pre>";
        print_r($record);

        print_r(json_encode($record,JSON_PRETTY_PRINT));

} catch (WebServiceException $ex) {
        echo $ex->getMessage();
}