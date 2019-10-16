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
//{"status":"error","message":"Duplicate orderID"}


//2.3. Interrogazione ultimo stato
$lastOrderStatus = false; // Da verificare con un ordine vero
$lastOrderStatusID = "testorder1";
// {"status":"warning","message":"No data found.","data":[]}

// 2.4. Interrogazione storico GLS
$storicogls = false; // OK Sembra funzionare => da verifcare con un ID VERO


// 3.1. Interrogazione Stock prodotti
$getqtyproduct = false; // Login non autorizzata
$codicearticolo = "1";
// string(94) "{"status":"error","message":"getqtyproduct -> Login non autorizzata o funzione non abilitata"}"

// 4.1. Inserimento contatto
$creaContatto = false; // ERRORE
// <title>Page Not Found</title >
$updateContatto = true;



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
    
        //https://vtiger.bigbeat.eu/index.php?module=SalesOrder&view=Detail&record=139&mode=showDetailViewByMode&requestMode=full&tab_label=Ordine%20di%20Vendita%20Details&app=INVENTORY
    $data = array();
    
    $data[1] = array(
        "orderID" => "SO5", // ti passo l'id del crm es 139 o il numero univoco ordin S05?
        "customerName" => "pippo", // mi devi dire cosa ti aspetti
        "destCity" =>"Barcelona",
        "destAddress" => "Via le mani da li",
        "destAreaCode" => "XX", // cosa intendi
        "destPostalCode" => "20200", // cap
        "destCountryCode" => "IT",
        "destPhone" => "123456789", // cellulare del contatto
        "destEmail" =>"",
        "orderDate" => "2017-12-13",
        "deliveryInfo"=>"a las mañana",
        "dateOfDelivery"=>"2018-01-07",
        "cod"=>0, // questo cosa indica
        "totalAmount" => 80.00, // totale dell'ordine
        
        "orderRowID" => "1",
        "productID" =>"01xmlt6a",
        "productName" => "CREMA XWZ",
        "productQty" => 2
    );


    $data = json_encode($data);
    
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


if($updateContatto == true){
    
    $leadID = 'AA123'; // Id contatto da aggiornare 
    $url="http://62.97.45.44:443/webapp/api/updateLead/".$leadID;
    $data = '{"customerFirstName":"Emanuele2","customerLastName":"Rizzo2","destPhone":"987654", '
            . '"destEmail":"testoooo@mail.it", "destAddress":"XXX", "destPostalCode":"XXX", '
            . '"destCity":"XXX", "destAreaCode":"XXX", "destCountryCode":"XXX", '
            . '"orderDate":"XXX", "deliveryInfo":"XXX"}';


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
        $response = json_decode( $result );
        echo $result.PHP_EOL;
        curl_close($ch);

    }

}
