<?php


// Legge i parametri dal file
$path = dirname(__FILE__);
$params = file_get_contents(dirname(__FILE__) ."/params.json");

$params =  json_decode($params, true);

$usr = $params['usr'];
$ack = $params['ack'];
$url = $params['url'];

// Flag per testare le funzioni
$createContact = false;
$createOrder = false;
$updateOrder = true;

// Codice di gestione degli esempi

include_once('vtwsclib/Vtiger/WSClient.php');

$client = new Vtiger_WSClient($url);
$login = $client->doLogin($usr, $ack);
if(!$login) 
    echo 'Login Failed';
else 
    echo 'Login Successful';


/*
 * [idPrefix] => 
 * 11 Accounts
 * 14 Produtcts
 * 12 Contacts
 * 6 SalesOrder
 */
/*
print_r($client->doDescribe('SalesOrder'));

die();
*/
//----------------------------------
// Creazione del contatto in Vtiger
//----------------------------------
if($createContact == true){

    $data = array (
                'lastname' => 'C0gnome',
                'firstname'=> 'Nome',
                'company'  => 'Algoma',
                'phone'  => '1234567890',
               // 'assigned_user_id' => '19x7' , // 19=Users Module ID, 1=First user Entity ID
    );

    $module = 'Contacts';
    $record = $client->doCreate($module,$data);
    
    if($record) {
        $recordid = $client->getRecordId($record['id']);
        /*
            The server returns record['id'] in the format <moduleid>'x'<recordid>.
            Use method getRecordId method can be used to retrieve only the record id
            part from the returned record id from the server.
         */
    }
    else{
        $error = $client->lastError();
        throw new Exception($error['code']. ' ' .$error['message']);
    }
}

//----------------------------------
// Creo ordine in vtiger
//----------------------------------
if($createOrder == true){
    $data = array(
            'subject' =>  'Ordine NUmero',
            'sostatus' => 'Created',
            'invoicedate' => date("Y-m-d"),
            'invoicestatus' => 'Created',
            'account_id' => '11x65',
            'contact_id' => '12x107',
           // 'assigned_user_id' => '19x1',
            'bill_street' => 'bill_street',
            'ship_street' =>'ship_street',
            'bill_city' => 'bill_city',
            'ship_city' => 'ship_city',
            'bill_state' => 'bill_state',
            'ship_state' => 'ship_state',
            'bill_code' => 'bill_code',
            'ship_code' => 'ship_code',
            'bill_country' => 'bill_country',
            'ship_country' => 'ship_country',
            'bill_pobox' => 'bill_pobox',
            'ship_pobox' => 'ship_pobox',
			
            'terms_conditions' =>'terms_conditions',
            'description' => 'description',
             
           // 'currency_id' => vtws_getWebserviceEntityId('Currency','1'),
            'hdnTaxType' => 'group',
            'productid' => '14x56',
            'hdnDiscountAmount' => '0',
            'hdnS_H_Amount' => 0,
            'hdnS_H_Percent' => 0,
            'LineItems' => array(
				
				array(
					'productid' => '14x56',
					'quantity' => 10,
					'listprice' => 59,
					'comment' => 'testo che voglio inserire'
				),
				
				array(
					'productid' => '14x56',
					'quantity' => 6,
					'listprice' => 11,
					'comment' => 'testo che voglio inserire AAAAAA'
				),
					
			),
        );
    
    $module = 'SalesOrder';
    $record = $client->doCreate($module,$data);
    
    if($record) {
        print_r($record);
    }
    else{
        $error = $client->lastError();
        throw new Exception($error['code']. ' ' .$error['message']);
    }
}


//----------------------------------
// Aggiornamento stato ordine
//----------------------------------

if($updateOrder == true){
    

    $module = 'SalesOrder';
    $wsId = '6x142';
    $data = $client->doRetrieve($wsId);
    
    
    
    $data = array(
                    'sostatus' => 'Delivered',
                    'invoicestatus' => 'Autocreated',
                    'id' => '6x142'
    );
    
    $record = $client->doRevise($module, $data);
    
    if($record) {
        print_r($record);
    }
    else{
        $error = $client->lastError();
        throw new Exception($error['code']. ' ' .$error['message']);
    }
    
}
    
   
    
           