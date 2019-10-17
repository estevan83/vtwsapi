<?php

// creo ordini di vendita su ims con diversi prodotti
// fare uno scheduler che legge per ogni prodotto di tutti gli ordini di vendita crea un array da inviare col curl
require_once('include/utils/utils.php');
require_once("modules/Emails/class.phpmailer.php");
require_once("modules/Emails/mail.php");
require_once('include/logging.php');
require_once("config.php");

include_once 'include/Webservices/Query.php';
include_once 'include/Webservices/Update.php';






class DapLinkClass {
    //put your code here
    
  //  public $curl = '';
    protected $token = '';
    protected $url; 
    protected $usr; 
    protected $pwd; 
    protected $current_user;
    protected $adb;
    
    
    
    public function __construct(){
        $this->current_user = Users::getActiveAdminUser();
        $this->adb  = PearDatabase::getInstance();
        
        //$this->adb = $adb;
        $params = file_get_contents(dirname(__FILE__) ."/params.json");
        $params =  json_decode($params, true);
        $this->url = $params['dl.url'];
        $this->usr = $params['dl.usr'];
        $this->pwd = $params['dl.pwd'];       
    }
    /*
    public function opencurl()
    {
        $this->writeLog("BEGIN::opencurl()");
        $this->curl = curl_init();
        $this->writeLog("END::opencurl()");
    }

    public function closecurl()
    {
        $this->writeLog("BEGIN::closecurl()");
        curl_close($this->curl);
        $this->writeLog("END::closecurl()");
    }
    */

    /**
     * Genera il token che serve per inviare aggiornamenti dei contatti e nuovi ordini
     * @return string token
     * @throws Exception
     */
    public function login(){
        $this->writeLog("BEGIN :: function login");
        $url = $this->url.$this->usr.'/'.$this->pwd . 'fillContact';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        } else {
            $result = curl_exec($ch);
            $response = json_decode($result);
            curl_close($ch);
            
            if($response['status'] ==='error'){
                $this->writeLog("function login ". $response['message']);
                throw new Exception($response['message']);
            }
            
            $this->token = $response->csrf_value;
        }
        
        $this->writeLog("function login generated token->".$this->token);
        $this->writeLog("END :: function login");
        return $this->token;
    }

    
    /**
     * @param array $row Ordine di vendita 1:1 con prodotto
     * @param int $rowNum Numero riga per progressivo ordine
     * @return array
     */
    public function fillOrder($row, $rowNum)
    {
        $this->writeLog("BEGIN :: function sendOrder");
        // $salesOrder=array();
        $salesOrder= array(
            "orderID"           =>  $row['orderid'],
            "customerID"        =>  $row['customerid'],
            "destCity"          =>  $row['destcity'],
            "destAddress"       =>  $row['destaddress'],
            "destAreaCode"      =>  $row['destareacode'],
            "destPostalCode"    =>  $row['destpostalcode'],
            "destCountryCode"   =>  $row['destcountrycode'],
            "destPhone"         =>  $row['destphone'],
            "destEmail"         =>  $row['destemail'],
            "orderDate"         =>  "2017-12-13",   //$row['orderdate'],//@todo review
            "deliveryInfo"      =>  $row['deliveryinfo'],
            "dateOfDelivery"    =>  "2018-01-07",   //@todo review
            "cod"               =>  "0",            //@todo review
            "totalAmount"       =>  $row['totalamount'],
            "orderRowID"        =>  $rowNum, 
            "productID"         =>  $row['productid'],
            "productName"       =>  $row['productname'],
            "productQty"        =>  $row['productqty'],
            "customerName"      =>  $row['customerfirstname'].$row['customerlastname'],
        );
        return $salesOrder;
    }

    /**
     * @param array $row Contatto per ogni ordine di vendita da aggiornare
     * @return array
     */
    public function fillContact($row)       
    {
        $this->writeLog("BEGIN :: function updateContact");
        $contact = array(
            "customerFirstName"     => $row['customerfirstname'],
            "customerLastName"      => $row['customerlastname'],
            "destPhone"             => $row['destphone'],
            "destEmail"             => $row['destemail'],
            "destAddress"           => $row['destaddress'],
            "destPostalCode"        => $row['destpostalcode'],
            "destCity"              => $row['destcity'],
            "destAreaCode"          => $row['destareacode'],
            "destCountryCode"       => $row['destcountrycode'],
            "orderDate"             => $row['orderdate'],
            "deliveryInfo"          => $row['deliveryinfo'],
            "customerID"            => $row['customerid'],
        );
        
        return $contact;
    }  
      
    /**
     * 
     * @param array $row Contatto da aggiornare
     * @param int $id ID del contatto da aggiornare
     */
    public function updateContact($row, $id=null){

        $jsonContact = json_encode($row);

        if($id==null)
            $leadID = $row['customerid'];
        
        $url="http://62.97.45.44:443/webapp/api/updateLead/".$leadID; //@todo da portare fuori
   
        
        $response=$this->send($jsonContact, $url);
        
        $this->daplinkLog($jsonContact, print_r($response,true), $row['contactid'], $leadID);
        $this->writeLog("END :: function updateContact");

    }       
    
    /**
     * 
     * @param array $row Ordine di vendita completo da inviare
     * @param int $id ID dell'ordine di vendita da aggiornare
     */
    public function insertOrder($salesOrder, $id)
    {
        $this->writeLog("BEGIN :: sendOrder params->".print_r($salesOrder,true));
       //  return;
        $jsonSalesOrder = json_encode($salesOrder);
        $url = "http://62.97.45.44:443/webapp/api/index.php/insertorder";//@todo review

        $response=$this->send($jsonSalesOrder,  $url );
        if ($response['status']=='success')
        {
            $this->setSalesOrder($id);
        }

        $this->daplinkLog($jsonSalesOrder, print_r($response,true), $id);
        $this->writeLog("END :: function sendOrder");
    }   
    
    
    
    
    /**
     * 
     * @param type $tmpID
     * @param type $sostatus
     * @return type
     */
    private function setSalesOrder($id, $sostatus = 'DaplinkAccepted'){
        
        
        $wsid = vtws_getWebserviceEntityId('SalesOrder', $id); // Module_Webservice_ID x CRM_ID
       // $data = array('sostatus' => $sostatus, 'id' => $wsid, 'hdnTaxType' => 'group');

        $so = vtws_retrieve($wsid, $this->current_user);
        $so['invoicestatus'] = 'AutoCreated';
        $so['sostatus'] = 'DaplinkAccepted';
        $so['id'] = $wsid;

        $data = vtws_update($so, $this->current_user);
        return $data;
    }


    
    
    
    
    
    /**
     * 
     * @param string $json Stringa json contenente i dati da inviare
     * @param string $url URL a cui inviare i dati
     * @return array $response risposta del curl
     * @throws Exception
     */
    public function send($json,  $url){
        $this->writeLog("BEGIN :: function send");
        $this->writeLog("Params:  Json Code->".$json."     token->".$this->token."     URL->".$url);
        $ch = curl_init(); // $ch = $this->curl;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: ' . $this->token));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        if (curl_errno($ch)) {
          /*  echo curl_errno($ch);
            echo curl_error($ch);*/
            $this->writeLog("Errore Curl: ".curl_error($ch)."    ".curl_errno($ch));
            throw new Exception(curl_error($ch), curl_errno($ch));
        } else {
            
            $result = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($result,true);
            // todo in base alla rssposta lanciare un eccezzione se success = warning /error
            $this->writeLog("Risposta Curl: ".print_r($response,true));
            
        }
        $this->writeLog("END :: function send");
        return $response;
    }   
    
    
    
    
    
    
    /**
     * //@todo review
     * @param string $query Codice SQL da elaborare
     * @param array $params array contenente i parametri per la query MySQL
     * @return array risultati della query
     */
    public function runQuery($query, $params = array())
    {
        $this->writeLog("BEGIN :: function runQuery");
        $this->adb;
        $result = $this->adb->pquery($query,$params);
        $this->handleException($result);
        while($row = $this->adb->fetchByAssoc($result)){
            $rows[] = $row;
            $this->writeLog("Ordine di vendita: ".print_r($row,true));
        }
        $this->writeLog("END :: function runQuery");
        return $rows;
    }
    
    
    
    /**
     * Gestione dell'errore per le query di MySQL
     * @param type $result
     * @throws Exception
     */
    private function handleException($result)
    {
        $this->writeLog("BEGIN :: function handleException");
        if(!$result){
           throw new Exception($this->adb->database->ErrorMsg(), $this->adb->database->ErrorNo());
           $this->writeLog("END :: function handleException -> Exception throwed");
        }
        else
        {
            $this->writeLog("END :: function handleException -> No Exception throwed");
        }
    }


    /**
     * 
     * @param string $msg Stringa da Loggare
     * @throws Exception
     */
    static function writeLog($msg){
        $file = 'logs/'.date('d').'DapLink.log';
        $now = date("Y-m-d H:i:s");
        $res = file_put_contents($file, "$now |  => $msg" . PHP_EOL, FILE_APPEND | LOCK_EX);
        if ($res == false){
                throw new Exception("cannot write log file");
        }
    }


    /**
     * 
     * @param string $request Json della richiesta
     * @param string $response print_r(Response del curl)
     * @param string $entityid ID del crm
     * @param string $daplinkid ID di daplink
     */
    public function daplinkLog($request, $response, $entityid, $daplinkid = '' )
    {
        $date = date('Y-m-d H:i:s');

        $query="INSERT INTO daplink_log (dt, request, response, entityid, daplinkid) VALUES (?,?,?,?,?)";
        $result = $this->adb->pquery($query, array($date, $request, $response, $entityid, $daplinkid));
        $this->handleException($result);
    }

    
    /**
     * Cancella file contenente i log
     * svuota la tabella dei log
     */
    public function clearLog()
    {
        $file = 'logs/'.date('d').'DapLink.log';
        unlink($file);
        $this->writeLog('NUOVA IMPORTAZIONE INIZIATA');
        $clear="TRUNCATE TABLE daplink_log";
        $result = $this->adb->pquery($clear, array());
        $this->handleException($result);
    }
    
}