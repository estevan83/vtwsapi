<?php
include_once('include/utils/utils.php');
require_once("modules/Emails/class.phpmailer.php");
require_once("modules/Emails/mail.php");
require_once('include/logging.php');
require_once("config.php");
// include ('cron/modules/Algoma/invoiceCall.php');
include_once 'include/Webservices/Query.php';
include_once 'include/Webservices/Utils.php';
include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Retrieve.php';
include_once 'include/utils/utils.php';
include_once 'includes/Loader.php';
include_once 'includes/http/Request.php';
include_once 'includes/http/Response.php';
include_once 'includes/http/Session.php';
include_once 'includes/runtime/BaseModel.php';
include_once 'includes/runtime/Controller.php';
include_once 'includes/runtime/LanguageHandler.php';
include_once 'includes/runtime/Viewer.php';
include_once 'includes/runtime/Globals.php';

//We include this files else it does not work online
include_once 'modules/RestfulApi/RestfulApi.php';
include_once 'modules/RestfulApi/models/Rest.php';
require_once 'modules/RestfulApi/actions/Api.php';
include_once 'modules/RestfulApi/actions/Auth.php';
 


$user = new Users();
$user->retrieveCurrentUserInfoFromFile(22);

$select = "SELECT * from Products WHERE serial_no = 'KL4542XA'
            ORDER BY id ASC
            LIMIT 0, 20 ;";
$results = vtws_query($select, $user);


foreach ($results as $tmp){
    $appo = explode('x', $tmp['id']);

    $result[] = $appo[1];
    unset ($appo);
}


