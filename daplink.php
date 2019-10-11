<?php

/*
 Tester per la verifica dell'applicativo daplink
 * aggiunta estevan
 */


// Legge i parametri dal file
// --------------------------
$path = dirname(__FILE__);
$params = file_get_contents(dirname(__FILE__) ."/params.json");

$params =  json_decode($params, true);

$url = $params['dl.url'] . $params['dl.usr'] . "/" . $params['dl.pwd'];

$token = "";


//  2.1. Inserimento di un ordine
$insertNewOrder = false; // non funziona  
//<b>Warning</b>:  Invalid argument supplied for foreach() in <b>/var/www/html/webapp/api/index.php</b> on line <b>1089</b><br />


//2.3. Interrogazione ultimo stato
$lastOrderStatus = false; // Da verificare con un ordine vero
$lastOrderStatusID = 100;
// {"status":"warning","message":"No data found.","data":[]}

// 2.4. Interrogazione storico GLS
$storicogls = false; // OK Sembra funzionare => da verifcare con un ID VERO


// 3.1. Interrogazione Stock prodotti
$getqtyproduct = false; // Login non autorizzata
$codicearticolo = "1";
// string(94) "{"status":"error","message":"getqtyproduct -> Login non autorizzata o funzione non abilitata"}"

// 4.1. Inserimento contatto
$creaContatto = true; // ERRORE
// <title>Page Not Found</title >



if(strlen($token) == 0){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    if (curl_errno($ch)) {
        echo curl_errno($ch);
        echo curl_error($ch);
        $status = array("status" => "error", "msg" => "Errore di Login");
        return false;
    } else {
        $result = curl_exec($ch);
        $response = json_decode($result);
        curl_close($ch);
        $token = $response->csrf_value;
    }
}

// -----------------------------------------------------------
// Creazione del contatto
// ------------------------------------------------------------
if($creaContatto == true){
    
    $url="http://62.97.45.44:443/webapp/api/index.php/insertLead";

    $data = array(
        'leadID' => '999',
        'customerFirstName' => 'estevan',
        'customerLastName' => 'civera',
        'destEmail' => 'lamail@mail.it',
        'productName' => 'nome prodotto',
        'totalAmount' => 100,
    );
    
    echo "Creazione del contatto" .PHP_EOL;
    print_r($data);

    $data = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: '.$token));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    if (curl_errno($ch)) {
        echo curl_errno($ch);
        echo curl_error($ch);
    } else {
        $result = curl_exec($ch);
        $response = json_decode($result);
        echo $result.PHP_EOL;
        echo $response.PHP_EOL;
        curl_close($ch);
    }
}


if ($getqtyproduct == true) {

    
    $data = array("item" => $codicearticolo);
    
    $data = json_encode($data);

    $url = "http://62.97.45.44:443/webapp/api/index.php/getqtyproduct";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: ' . $token));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    if (curl_errno($ch)) {
        echo curl_errno($ch);
        echo curl_error($ch);
        $status = array("status" => "error", "msg" => "Prodotto non trovato");
    } else {
        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result);
        $tt = array();
        $i = 1;
        echo $result;
    }
}



if ($storicogls == true) {

    $data = array('deliveryNote'=>$deliveryNote,"codvet"=>'GLS');
    $data = json_encode($data);
    
    $url = "http://62.97.45.44:443/webapp/api/index.php/getGLSstorico";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: ' . $token));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    if (curl_errno($ch)) {
        $status = array("status" => "error", "msg" => "Spedizione Non Trovata " . curl_error($ch));
        echo json_encode($status);
    } else {
        $result = curl_exec($ch);
        $response = json_decode($result, true);
        if ($response['status'] == 'success') {
            echo json_encode($response);
        } else {
            echo "Tracking non trovato";
        }

        curl_close($ch);
    }
}



if ($lastOrderStatus == true) {
    
    $url = "http://62.97.45.44:443/webapp/api/index.php/getlaststatus";
    $data = array('orderID' => $lastOrderStatusID);
    $data = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:  application/json', 'Authorization: ' . $token));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    if (curl_errno($ch)) {
        echo curl_errno($ch);
        echo curl_error($ch);
        $status = array("status" => "error", "msg" => "Spedizione non trovata");
    } else {
        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result);
        echo $result;
    }
}



if ($insertNewOrder == true) {

    $url = "http://62.97.45.44:443/webapp/api/index.php/insertorder";


    $data = '{"1":{"orderID":"XXXXX","customerName":"XXXXXX","destAddress":"XXXXX","destPostalCode":"XXXXX",'
            . '"destCity":"XXXXXX","destAreaCode":"XX","destCountryCode":"XX","destPhone":"XXXXXXXX", '
            . '"destEmail":"XXXXXXX","orderDate":"YYYY-MM-DD",'
            . '"deliveryInfo":"Lorem ipsum","cod":XX,"totalAmount":XX.XX,"orderRowID":"X","productID":"XXXX","productName":"XXXXX","productQty":X}';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: ' . $token));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    if (curl_errno($ch)) {
        echo curl_errno($ch);
        echo curl_error($ch);
    } else {
        $result = curl_exec($ch);
        $response = json_decode($result);
        echo $result;
        curl_close($ch);
    }
}

/*
    Nell ordine
        contactrecord :   id di vtiger del contatto (YxZ)
 
  
    
 */
