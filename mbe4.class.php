<?php

  /** 
  * mbe4 payment widget class
  */
class mbe4 {
  /** 
  *  __construct-function is called on object create. Defines some variables.
  *  
  *  @param string $username
  *	mbe4 username.
  *  @param string $password
  *	mbe4 password.
  *  @param int $clientid
  *	mbe4 clientid.
  *  @param int $serviceid
  *	mbe4 serviceid
  *  @param string $url
  *	mbe4-URL for the transaction.
  *
  */
  function __construct($username, $password, $clientid, $serviceid, $url="https://billing.mbe4.de/widget/singlepayment") {
      $this->username = $username;
      $this->password = $password;
      $this->clientid = $clientid;
      $this->serviceid = $serviceid;
      $this->url = $url;
  }

  /** 
  *  Create a array with all transaction-data needed.
  *  
  *  @param int $amount
  *	Amount for the transaction.
  *  @param int $contentclass
  *	Contentid for the transaction. contentclass-codes-list available in readme.md or at https://github.com/justmakeit/mbe4-php5-class/blob/master/README.md
  *
  *  @return array
  *	Liefert ein Key/Value-Array zurück, welches per GET an mbe4 übertragen werden muss.
  *
  */
  function create_transaction($id,$description="mbe4 payment", $amount, $contentclass=1, $returnurl,$urlencode=TRUE){  
  // Timestamp generieren
  $timestamp=date("Y-m-d")."T".date("H:i:s.000")."Z";
  // Hashbase definieren
  $hashbase=
      $this->password .
      $this->username .
      $this->clientid .
      $this->serviceid .
      $contentclass .
      $description .
      $id .
      $amount .
      $returnurl .
      $timestamp;      
  // hash erzeugen
  $hashparam=md5($hashbase);
  // Build the data array that will be translated into hidden form values.
  $data = array(
    // General parameters
    'username' =>$this->username,
    'clientid' => $this->clientid,
    'serviceid' => $this->serviceid,
    'contentclass' => $contentclass,
    'description' => $description,
    'clienttransactionid' => $id,
    'amount' => $amount, // mbe4 wants ct, no eur
    'callbackurl' => $returnurl,
    'timestamp' => $timestamp,
    'hash' => $hashparam,
  );
  // Sollen die Werte mit urlencode() codiert werden?
  if($urlencode==TRUE){
      foreach($data as $element){
	  $element= urlencode($element);
      }
  }
  return $data;
  }

  /** 
  *  Validate the result from the mbe4-redirect
  *  
  *  @param array $mbe4_params
  *	$_GET-Array from the mbe4-redirect.
  *  @param string $mbe4_password
  *	mbe4 password
  *
  *  @return int
  *	Returns the mbe4-responsecode
  *
  */
  function validate_transaction($mbe4_params, $mbe4_password) {
      // check hash Signierung
      $hashbase=
	  $mbe4_password . 
	  $mbe4_params["transactionid"] . 
	  $mbe4_params["clienttransactionid"] . 
	  $mbe4_params["responsecode"] . 
	  $mbe4_params["description"] . 
	  $mbe4_params["subscriberid"] . 
	  $mbe4_params["operatorid"] . 
	  $mbe4_params["timestamp"];
      if(md5($hashbase)!=$mbe4_params["hash"]){
	  return 999; // Parameter nicht korrekt oder manipuliert!
      }
      return $mbe4_params["responsecode"]; // Transaktionscode zurückgeben
  }
  
  /** 
  *  Check the response, if everything is fine, TRUE is returned
  *  
  *  @param int $amount
  *	Amount for the transaction.
  *  @param int $contentclass
  *	Contentid for the transaction. contentclass-codes-list available in readme.md or at https://github.com/justmakeit/mbe4-php5-class/blob/master/README.md
  *
  *  @return array
  *	Liefert ein Key/Value-Array zurück, welches per GET an mbe4 übertragen werden muss.
  *
  */
  function is_valid_responsecode($responsecode) {
    if($responsecode==0)
	return TRUE;
    else
	return FALSE;
  }

  /** 
  *  Get Response-Message from Responsecode
  *  
  *  @param int $responsecode
  *	responsecode from the transaction.
  *
  *  @return string
  *	Returns Responsemessage.
  *
  */
  function get_responsemsg_by_responsecode($responsecode){
    switch($responsecode){
	case 0: return('OK');
	case 1: return('NOT FINAL – request was processed successfully but the answer is not final. (e.g. a TAN was sent to the subscriber and tan received must be called)');
	case 2: return('authorization failed');
	case 3: return('capture failed');
	case 4: return('terminate failed');
	case 5: return('refund failed');
	case 6: return('prepair failed');
	case 7: return('transaction failed');
	case 8: return('subscription terminate failed');
	case 101: return('invalid parameter');
	case 109: return('transaction in wrong status');
	case 110: return('wrong PIN');
	case 111: return('too many PIN attempts - transaction closed');
	case 112: return('subscriber aborted transaction');
	case 113: return('no route to operator');
	case 121: return('subscriberid unascertainable');
	case 126: return('sending TAN SMS failed');
	case 150: return('subscriptionid unknown');
	case 151: return('subscriptionid not unique');
	case 152: return('subscription terminated');
	case 200: return('internal server error');
	case 999: return('hash cant be verified');
	case 201: return('system currently unavailable');
	default: return(900);
    }
  }
}
?>
