<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

chdir("..");


include_once 'config.php';
include_once 'include/Webservices/Relation.php';

include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Utils.php';

$current_user = CRMEntity::getInstance('Users');
$current_user->retrieveCurrentUserInfoFromFile(1);


try {
        $data = array (
                'lastname' => 'LNAME',
                'firstname'=> 'FNAME',
                'company'  => 'CNAME',
                'assigned_user_id' => '19x1', // 19=Users Module ID, 1=First user Entity ID
        );
        $lead = vtws_create('Contacts', $data, $current_user);

        print_r($lead);

} catch (WebServiceException $ex) {
        echo $ex->getMessage();
}