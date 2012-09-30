<?php

class mbe4 {
  function __construct($username, $password, $clientid, $serviceid, $url="https://billing.mbe4.de/widget/singlepayment") {
      $this->username = $username;
      $this->password = $password;
      $this->clientid = $clientid;
      $this->serviceid = $serviceid;
      $this->url = $url;
  }

  /*
  *  Senden der Daten an mbe4.
  *  params: 
  *   $amount: Die Transaktionssumme in EUR
  *   $contentid: Die Art des zu Buchenden Contents.
  *		1: News/Info
  *		2. Chat/Flirt
  *		3. Game
  *		4. Klingelton
  *		5. Bild/Logo
  *		6. Videoclip
  *		7. Musikdatei
  *		8. Lokalisierung
  *		9. Voting
  *		10. Gewinnspiel
  *		11. Portal Zugang
  *		12. Software
  *		13. Dokument
  *		14. Ticket
  *		15. Horoskop
  *		16. Freizeit
  *		17. Unterwegs
  *		18. Finanzen
  *		19. Shopping
  *		20. E-Mail
  *		21. Spende
  *  return:
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
  
/*	validate_transaction($mbe4_params)
*	Validierung der Zahlung. Dafür werden die von mbe4 übergebenen Parameter 
*	plus das mbe4-Password hintereinander gehängt und eine MD5-Summe aus dem String erstellt.
*	params:
*		$mbe4_params: Key-Value-Array aus dem per GET übergebenen Parametern
*	return:
*		Response-Code, 0 bedeutet erfolgreiche Zahlung
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
  
  function is_valid_responsecode($responsecode) {
    if($responsecode==0)
	return TRUE;
    else
	return FALSE;
  }
  
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
