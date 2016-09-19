<?php
// $Id: quickpay.phps,v 1.17 2005/12/14 19:16:58 ta Exp $


class quickpay {
  /**
     * @var protocol to QuickPay gateway
     */
	 
	var $protocol;
    /**
     * @var string URL to QuickPay gateway
     */
	 
	var $gatewayUrl = 'https://secure.quickpay.dk/api/';
   // var $gatewayUrl = 'https://secure.quickpay.dk/transaction.php';

	/**
     * @var string autocapture
     */
    var $autocapture='0';
    
		/**
     * @var string finalize split paument
     */
    var $finalize='';
	
	
	/**
     * @var string md5check to ensure data integrity
     */

    var $md5checkword;
    /**
     * @var string Message type
     */
    var $msgtype;
    /**
     * @var string Card number
     */
    var $cardnumber;
    /**
     * @var string Transaction amount
     */
    var $amount;
    /**
     * @var string Card expiration date (YYMM)
     */
    var $expirationdate;
    /**
     * @var string Posc (currently only take this value 'K00500K00130')
     */
    var $cardtype;
    /**
     * @var string Order number of the transaction (must be unique)
     */
    var $ordernum;
    /**
     * @var string 3 character ISO currency (Danish krone = 'DKK')  
     */
    var $currency;
    /**
     * @var string Card CVD number
     */
    var $cvd;
    /**
     * @var string QuickPay merchant id    
     */
    var $merchant;
    /**
     * @var string transaction id
     */
    var $transaction;
    /**
     * @var string Authorzation type [preauth, recurring]
     */
    var $authtype;
    /**
     * @var string Text for identifying a subscription payment
     */
    var $reference;
    /**
     * @var array Key/values in this array will be available in your QuickPay 
administration page
     */
    var $customVars = array();
    /**
     * @var array Used internaly for XML parsing
     */
    var $arr;




    /**
    * Constructor
    * @return void
    */
    public function __construct() {
    }

    function init() {
        //check for curl and xml
        if(!extension_loaded('curl')) {
            //die("The \"curl\" extension must be installed on your server<br />");
            return false;
        }
        if(!extension_loaded('SimpleXML')) {
            // die("The \"xml\" extension must be installed on your server<br />");
            return false;
        }
        return true;
    }

    /**
    * authorize transaction
    * @return mixed    Return an array with statuscode if communcation with 
quickpay succeeded - otherwise false
    */
    public function authorize() {

        switch ($this->authtype) {
            case 'preauth':
            $this->checkVars('authPre');
            break;
            case 'recurring':
            $this->cardnumber = '';
            $this->expirationdate = '';
            $this->cvd = '';
            $this->checkVars('authRec');
            break;
            default:
            $this->checkVars('auth');
        }
        
        $postData = array();
		$postData[ 'protocol' ] = $this->protocol;
        $postData[ 'msgtype' ] = $this->msgtype;
        $postData[ 'merchant' ] = $this->merchant;
	    $postData[ 'ordernum' ] = $this->ordernum;
		$postData[ 'amount' ] = $this->amount;
		$postData[ 'currency' ] = $this->currency;
		$postData[ 'autocapture' ] = $this->autocapture;
		$postData[ 'cardnumber' ] = $this->cardnumber;
        $postData[ 'expirationdate' ] = $this->expirationdate;
        $postData[ 'cvd' ] = $this->cvd;
        $postData['authtype'] = $this->authtype;
        $postData['reference'] = $this->reference;
        $postData['transaction'] = $this->transaction;
        $postData[ 'md5check' ] = 
md5($this->protocol.$this->msgtype.$this->merchant.$this->ordernum.$this->amount.$this->currency.$this->autocapture.$this->cardnumber.$this->expirationdate.$this->cvd.$this->md5checkword);


        // Add Custom variables
        foreach ($this->customVars as $key => $val) {
            $postData[$key] = $val;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gatewayUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($ch);
        curl_close($ch);
        $xmlObj = simplexml_load_string($response);
        $responseArr = $this->objectsIntoArray($xmlObj);

        return $responseArr;
    }

    /**
    * Capture transaction
    * @return mixed    Return an array with statuscode if communcation with 
quickpay succeeded - otherwise false
    */
    public function capture(){


        $this->checkVars('cap');

        $postData = array();
		$postData[ 'protocol' ] = $this->protocol;
        $postData[ 'msgtype' ] = $this->msgtype;
        $postData[ 'amount' ] = $this->amount;
        $postData[ 'merchant' ] = $this->merchant;
        $postData[ 'transaction' ] = $this->transaction;
		$postData[ 'finalize' ] = $this->finalize;
        $postData[ 'md5check' ] = 
md5($this->protocol.$this->msgtype.$this->merchant.$this->amount.$this->finalize.$this->transaction.$this->md5checkword);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gatewayUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($ch);
        $xmlObj = simplexml_load_string($response);
        $responseArr = $this->objectsIntoArray($xmlObj);

        return $responseArr;
    }
    
    /**
    * Reverse  transaction
    * @return mixed    Return an array with statuscode if communcation with 
quickpay succeeded - otherwise false
    */
    public function reverse() {
        $this->checkVars('rev');
        $postData = array();
		$postData[ 'protocol' ] = $this->protocol;
        $postData[ 'msgtype' ] = $this->msgtype;
        $postData[ 'merchant' ] = $this->merchant;
        $postData[ 'transaction' ] = $this->transaction;
        $postData[ 'md5check' ] = 
        md5($this->protocol.$this->msgtype.$this->merchant.$this->transaction.$this->md5checkword);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gatewayUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($ch);
        $xmlObj = simplexml_load_string($response);
        $responseArr = $this->objectsIntoArray($xmlObj);

        return $responseArr;
    }

    /**
     *Status on transaction
    * @return mixed    Return an array with statuscode if communcation with 
quickpay succeeded - otherwise false
     */
    public function credit() {
   

        $this->checkVars('credit');
        
        $postData = array();
		 $postData[ 'protocol' ] = $this->protocol;
        $postData[ 'msgtype' ] = $this->msgtype;
        $postData[ 'merchant' ] = $this->merchant;
        $postData[ 'transaction' ] = $this->transaction;
        $postData['amount'] = $this->amount;
        $postData[ 'md5check' ] = 
        md5($this->protocol.$this->msgtype.$this->merchant.$this->amount.$this->transaction.$this->md5checkword);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gatewayUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($ch);
        $xmlObj = simplexml_load_string($response);
        $responseArr = $this->objectsIntoArray($xmlObj);

     return $responseArr;
    }

    /**
     * Status on transaction
     * @return mixed    Return an array with statuscode if communcation with 
quickpay succeeded - otherwise false
     */
    public function status() {
        $this->checkVars('stat');  
        $postData = array();
		$postData[ 'protocol' ] = $this->protocol;
        $postData[ 'msgtype' ] = $this->msgtype;
        $postData[ 'merchant' ] = $this->merchant;
        $postData[ 'transaction' ] = $this->transaction;
        $postData[ 'md5check' ] = 
md5($this->protocol.$this->msgtype.$this->merchant.$this->transaction.$this->md5checkword);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gatewayUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($ch);

        $xmlObj = simplexml_load_string($response);
        $responseArr = $this->objectsIntoArray($xmlObj);

        return $responseArr;
    }
    
    /**
     * Get status of clearing houses
     * @return mixed    Return an array with statuscode if communcation with 
quickpay succeeded - otherwise false
     */
    public function pbs_status() {
        $this->checkVars('pbs_stat');    

        $postData = array();
        $postData[ 'msgtype' ] = $this->msgtype;
        $postData[ 'merchant' ] = $this->merchant;
        $postData[ 'md5check' ] = 
md5($this->msgtype.$this->merchant.$this->md5checkword);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gatewayUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($ch);
        $xmlObj = simplexml_load_string($response);
        $responseArr = $this->objectsIntoArray($xmlObj);

        return $responseArr;
    }
    
    /**
    * Checks whether the correct values are set for the object
    * @param
    * @return
    */
    private function checkVars($type) {


        $authVars = 
'protocol,msgtype,merchant,ordernum,amount,currency,autocapture,cardnumber,expirationdate,cvd,md5checkword';
        $authVarsPre = 
'protocol,msgtype,merchant,ordernum,amount,currency,autocapture,cardnumber,expirationdate,cvd,md5checkword';
        $authVarsRec = 
'protocol,msgtype,merchant,ordernum,amount,currency,autocapture,cardnumber,expirationdate,cvd,md5checkword';

	    $capVars =     'protocol,msgtype,merchant,amount,transaction,md5checkword';
	    $revVars =     'protocol,msgtype,merchant,transaction,md5checkword';
	    $statVars =    'md5checkword,msgtype,merchant,transaction';    
		$creditVars =  'protocol,msgtype,merchant,amount,transaction,md5checkword';
        $pbsStatVars = 'md5checkword,msgtype,merchant';

        if ($type === 'auth') {
            $vars = explode(',', $authVars);
        }
        if ($type === 'authPre') {
            $vars = explode(',', $authVarsPre);
        }
        if ($type === 'authRec') {
            $vars = explode(',', $authVarsRec);
        }
        if ($type === 'cap') {
            $vars = explode(',', $capVars);
        }
        if ($type === 'rev') {
            $vars = explode(',', $revVars);
        }
        if ($type === 'credit') {
            $vars = explode(',', $creditVars);
        }
        if ($type === 'stat') {
            $vars = explode(',', $statVars);
        }
        if ($type === 'pbs_stat') {
            $vars = explode(',', $pbsStatVars);
        }
        
        if (is_array($vars)) {
            for ($i = 0; $i < count($vars); $i++) {
                if (!isset($this->$vars[$i])) {
                    throw new Exception("You need to set " . $vars[$i] . " - 
example: \$obj->set_" . $vars[$i] . "(&lt;value&gt;);");
                }
            }
        }
    }

public function objectsIntoArray($arrObjData, $arrSkipIndices = array())
 {
     $arrData = array();
     
    // if input is object, convert into array
     if (is_object($arrObjData)) {
         $arrObjData = get_object_vars($arrObjData);
     }
     
    if (is_array($arrObjData)) {
         foreach ($arrObjData as $index => $value) {
             if (is_object($value) || is_array($value)) {
                 $value = $this->objectsIntoArray($value, $arrSkipIndices); // recursive call
             }
             if (in_array($index, $arrSkipIndices)) {
                 continue;
             }
             $arrData[$index] = $value;
         }
     }
     return $arrData;
 }



    /**
    * Get the gatewayUrl
    * @return string gatewayUrl
    */
    public function get_gatewayUrl() {
        return (string) $this->gatewayUrl;
    }

    /**
    * Set the gatewayUrl
    * @param string    gatewayUrl
    * @return void
    */
    public function set_gatewayUrl($gatewayUrl) {
        $this->gatewayUrl = (string) $gatewayUrl;
    }

    /**
    * Get the md5 check word
    * @return string    md5 check word
    */
    public function get_md5checkword() {
        return (string) $this->md5checkword;
    }

    /**
    * Set the md5 check word
    * @param string    md5 check word
    * @return void
    */
    public function set_md5checkword($md5checkword) {
        $this->md5checkword = (string) $md5checkword;
    }


    /**
    * Get the transaction msgtype
    * @return string    Returns the transaction msgtype
    */
    public function get_msgtype() {
        return (string) $this->msgtype;
    }

    /**
    * Set the transaction msgtype
    * @param string    Transaction msgtype
    * @return void
    */
    public function set_msgtype($msgtype) {
        $this->msgtype = (string) $msgtype;
    }

    /**
    * Get the card number for the transaction
    * @return string    Card no.
    */
    public function get_cardnumber() {
        return (string) $this->cardnumber;
    }

    /**
    * Set the card number for the transaction
    * @param string    Card no.
    * @return void
    */
    public function set_cardnumber($cardnumber) {
        $this->cardnumber = (string) $cardnumber;
    }

    /**
    * Get the amount of the transaction
    * @return string    Amount
    */
    public function get_amount() {
        return (string) $this->amount;
    }

    /**
    * Set the amount of the transaction
    * @param string    Amount
    * @return void
    */
    public function set_amount($amount) {
        $this->amount = (string) $amount;
    }

    /**
    * Get the expirationdate of the transaction
    * @return string    Expiration date
    */
    public function get_expirationdate() {
        return (string) $this->expirationdate;
    }

    /**
    * Set the expirationdate of the transaction
    * @param string    Expiration date
    * @return void
    */
    public function set_expirationdate($expirationdate) {
        $this->expirationdate = (string) $expirationdate;
    }

    /**
    * Get the transaction type (cardtype)
    * @return string    Transaction type
    */
    public function get_cardtype() {
        return (string) $this->cardtype;
    }

    /**
    * Set the transaction type (cardtype)
    * @param string    Transaction type
    * @return void
    */
    public function set_cardtype($cardtype) {
        $this->cardtype = (string) $cardtype;
    }

    /**
    * Get the order no. the applies to the transaction
    * @return string    Order no.
    */
    public function get_ordernum() {
        return (string) $this->ordernum;
    }

    /**
    * Set the order no. the applies to the transaction
    * @param string    Order no.
    * @return void
    */
    public function set_ordernum($ordernum) {
        $this->ordernum = (string) $ordernum;
    }

    /**
    * Get the currency of the transaction
    * @return string    Currency
    */
    public function get_currency() {
        return (string) $this->currency;
    }

    /**
    * Set the currency of the transaction
    * @param string    Currency
    * @return void
    */
    public function set_currency($currency) {
        $this->currency = (string) $currency;
    }

    /**
    * Get the card verification digits 
    * @return string    CVD
    */
    public function get_cvd() {
        return (string) $this->cvd;
    }

    /**
    * Set the card verification digits
    * @param string    CVD
    * @return void
    */
    public function set_cvd($cvd) {
        $this->cvd = (string) $cvd;
    }

    /**
    * Get the merchant of transaction
    * @return string    Merchant
    */
    public function get_merchant() {
        return (string) $this->merchant;
    }

    /**
    * Set the merchant of the transaction
    * @param string    Merchant
    * @return void
    */
    public function set_merchant($merchant) {
        $this->merchant = (string) $merchant;
    }

    /**
    * Get the transaction authtype
    * @return string    Transaction authtype
    */
    public function get_authtype() {
        return (string) $this->authtype;
    }

    /**
    * Set the transaction authtype
    * @param string    Transaction authtype
    * @return void
    */
    public function set_authtype($authtype) {
        $this->authtype = (string) $authtype;
    }

    /**
    * Get the transaction reference
    * @return string    Transaction reference
    */
    public function get_reference() {
        return (string) $this->reference;
    }

    /**
    * Set the transaction reference
    * @param string    Transaction reference
    * @return void
    */
    public function set_reference($reference) {
        $this->reference = (string) $reference;
    }

    /**
    * Get the transaction id
    * @return string    Transaction id
    */
    public function get_transaction() {
        return (string) $this->transaction;
    }

    /**
    * Set the transaction id
    * @param string    Transaction id
    * @return void
    */
    public function set_transaction($transaction) {
        $this->transaction = (string) $transaction;
    }
       /**
    * Get the protocol
    * @return string    protocol
    */
    public function get_protocol() {
        return (string) $this->protocol;
    }
	    /**
    * Set the protocol
    * @param string    protocol
    * @return void
    */
    public function set_protocol($protocol) {
        $this->protocol = (string) $protocol;
    }
  
         /**
    * Get the splitpayment
    * @return string    splitpayment
    */
    public function get_splitpayment() {
        return (string) $this->splitpayment;
    }
	    /**
    * Set the splitpayment
    * @param string    splitpayment
    * @return void
    */
    public function set_splitpayment($splitpayment) {
	$qp_splitpayment = ($splitpayment == "Normal" ? '0' : '1');
		
        $this->splitpayment = (string) $qp_splitpayment;
    }

   
   
    /**
    * Add a custom variable
    * @param string     Name
    * @param string     Value
    * @return void
    */
    public function add_customVars($name, $value) {
        if (!empty($name)) {
            $this->customVars["CUSTOM_$name"] = $value;
        }
    }
}

?>