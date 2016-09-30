<?php
ini_set('log_errors', true);
ini_set('error_log', DIR_FS_WORK . 'logs/quickpay_errors.log');
require_once('includes/application_top.php');
require_once(DIR_FS_CATALOG . 'includes/classes/order.php');
require_once(DIR_FS_CATALOG . 'addons/Quickpay_Payments/QuickpayApi.php');
//require_once(DIR_FS_CATALOG . 'includes/classes/xml.php');

$error = false;
$verified = false;
//$paymentStatus = $_POST['qpstat'];
//$response_array = array('root' => $_POST);
$order_id = $_GET['qporder']; //get ordernumber if error. For statushandling
$internal_order_id = $_SESSION['cartSync']['orderID'];
if (isset($_SESSION['cartSync']['orderCreated'])) unset($_SESSION['cartSync']['orderCreated']);
/* 
if (!isset($_POST['md5check'])) {
   //just keep
   //exit();
   

}

$qp_md5word = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MD5WORD;
$qp_md5check = $_POST['md5check'];

$qp_md5check_valid = md5(
        $_POST['msgtype'] .
        $_POST['ordernumber'] .
        $_POST['amount'] .
        $_POST['currency'] .
        $_POST['time'] .
        $_POST['state'] .
        $_POST['qpstat'] .
        $_POST['qpstatmsg'] .
        $_POST['chstat'] .
        $_POST['chstatmsg'] .
        $_POST['merchant'] .
        $_POST['merchantemail'] .
        $_POST['transaction'] .
        $_POST['cardtype'] .
        $_POST['cardnumber'] .
		$_POST['cardhash'].
        $_POST['cardexpire'] .
		$_POST['acquirer'] .
        $_POST['splitpayment'] .
        $_POST['fraudprobability'] .
        $_POST['fraudremarks'] .
        $_POST['fraudreport'] .
        $_POST['fee'] .
        $qp_md5word
);

*/
// ------

/*
 * 	status codes
  000 	Approved.
  001 	Rejected by acquirer. See field 'chstat' and 'chstatmsg' for further explanation.
  002 	Communication error.
  003 	Card expired.
  004 	Transition is not allowed for transaction current state.
  005 	Authorization is expired.
  006 	Error reported by acquirer.
  007 	Error reported by QuickPay.
  008 	Error in request data.
  009 	Payment aborted by shopper
 */  
 $qp = new QuickpayApi;

		$qp->setOptions( ADDONS_PAYMENT_QUICKPAY_PAYMENTS_USERAPIKEY);
		if(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_SUBSCRIPTION != "-1"){
    	$qp->mode = 'subscriptions?order_id=';
		}else{
		$qp->mode = 'payments?order_id=';
		}
    // Commit the status request, checking valid transaction id
     $str = $qp->status($oid);
	 $str["operations"][0] = array_reverse($str["operations"][0]);
 
 $qp_status = $str[0]["operations"][0]["qp_status_code"];
 $qp_type = strtolower($str[0]["type"]);
 $qp_operations_type = $str[0]["operations"][0]["type"];
 $qp_capture = $str[0]["autocapture"];
 $qp_vars = $str[0]["variables"];
 $qp_id = $str[0]["id"];
 $qp_aq_status_code = $str[0]["aq_status_code"];
 $qp_aq_status_msg = $str[0]["aq_status_msg"];
  $qp_cardtype = $str[0]["metadata"]["brand"];
  $qp_cardhash_nr = $str[0]["metadata"]["hash"];
  $qp_status_msg = $str[0]["operations"][0]["qp_status_msg"]."\n"."Cardhash: ".$qp_cardhash_nr."\n";
  $qp_cardnumber = "xxxx-xxxxxx-".$str[0]["metadata"]["last4"];
  $qp_amount = $str[0]["operations"][0]["amount"];
  $qp_currency = $str[0]["currency"];
  $qp_pending = ($str[0]["pending"] == "true" ? " - pending ": "");
  $qp_expire = $str[0]["metadata"]["exp_month"]."-".$str[0]["metadata"]["exp_year"];
  $qp_cardhash = $str[0]["operations"][0]["type"].(strstr($str[0]["description"],'Subscription') ? " Subscription" : "");



 if ($str[0]["id"] && $qp_status == 20000) {
  // update order status and get returnvalues
        $_order_status = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ORDER_DEFAULT_STATUS_ID;
	/*	$transaction = $_POST['transaction'];
        $cardhash = $_POST['cardhash'];
        $cardnumber = $_POST['cardnumber'];
		$cardtype = $_POST['cardtype'];
		*/
 }
/*
$lC_XML = new lC_XML($response_array);

$Qtransaction = $lC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
$Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
$Qtransaction->bindInt(':orders_id', $order_id);
$Qtransaction->bindInt(':transaction_code', 1);
$Qtransaction->bindValue(':transaction_return_value', $lC_XML->toXML());
$Qtransaction->bindInt(':transaction_return_status', ($_POST['qpstatmsg'] == 'OK') ? 1 : 0);
$Qtransaction->execute();  
*/

//subscription handling - capture first
if($qp_type == "subscription"){
	
		    $apiorder= new QuickpayApi();
	$apiorder->setOptions(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_USERAPIKEY);

	$apiorder->mode = "subscriptions/";
	$addlink = $qp_id."/recurring/";
	
	  //create new quickpay order
	  $process_parameters["amount"]= $qp_amount;
	  $process_parameters["order_id"]= $qp_order_id."-".$qp_id;
	  $process_parameters["auto_capture"]= "1";	
      $storder = $apiorder->createorder($qp_order_id, $qp_currency_code, $process_parameters, $addlink);
	
}




if((isset($_GET["qperror"]) && $_GET["qperror"]=="cancel") || !$qp_status == 20000){
	   $_order_status = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ORDER_CANCELED_STATUS_ID;

	$Qupdate = $lC_Database->query('update :table_orders set orders_status = :orders_status where orders_id = :orders_id');
    $Qupdate->bindTable(':table_orders', TABLE_ORDERS);
    $Qupdate->bindInt(':orders_status', $_order_status);
    $Qupdate->bindInt(':orders_id', $internal_order_id);
    $Qupdate->execute();
/*	    
	$Qstatus = $lC_Database->query('insert into :table_orders_status_history (orders_id, orders_status_id, date_added, customer_notified, comments) values (:orders_id, :orders_status_id, now(), :customer_notified, :comments)');
    $Qstatus->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
    $Qstatus->bindInt(':orders_id', $order_id);
    $Qstatus->bindInt(':orders_status_id', $_order_status);
    $Qstatus->bindInt(':customer_notified', '0');
    $Qstatus->bindValue(':comments', 'Quickpay Payment not processed');
    $Qstatus->execute();
*/
	     $lC_MessageStack->add('payment', sprintf($lC_Language->get('payment_quickpaypayments_error_payment_problem'), $_GET['qperror']));
            $_SESSION['messageToStack'] = $lC_MessageStack->getAll(); 
            $error = true;

	
}
 if ($error) {
		   
		   lc_redirect(lc_href_link(FILENAME_CHECKOUT, 'payment', 'AUTO'));
	   
	   
}
  $_SESSION["qpcallback"]["orderstatus"] = $_order_status;
?>