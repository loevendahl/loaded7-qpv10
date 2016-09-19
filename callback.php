<?php
require_once('includes/application_top.php');
require_once(DIR_FS_CATALOG . 'includes/classes/order.php');
require_once(DIR_FS_CATALOG . 'includes/classes/xml.php');
ini_set('log_errors', true);
ini_set('error_log', DIR_FS_WORK . 'logs/quickpay_errors.log');
$error = false;
$verified = false;
$paymentStatus = $_POST['qpstat'];
$response_array = array('root' => $_POST);
$order_id = (isset($_POST['ordernumber'])? $_POST['ordernumber'] : $_GET['qporder']); //get ordernumber if error. For statushandling
if (isset($_SESSION['cartSync']['orderCreated'])) unset($_SESSION['cartSync']['orderCreated']);
 
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
 
 		  if ($qp_md5check == $qp_md5check_valid) {
          $verified = true;

        }  

 if ($verified && $_POST['qpstatmsg'] == 'OK') {
  // update order status and get returnvalues
        $_order_status = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ORDER_DEFAULT_STATUS_ID;
		$transaction = $_POST['transaction'];
        $cardhash = $_POST['cardhash'];
        $cardnumber = $_POST['cardnumber'];
		$cardtype = $_POST['cardtype'];
 }

$lC_XML = new lC_XML($response_array);

$Qtransaction = $lC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
$Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
$Qtransaction->bindInt(':orders_id', $order_id);
$Qtransaction->bindInt(':transaction_code', 1);
$Qtransaction->bindValue(':transaction_return_value', $lC_XML->toXML());
$Qtransaction->bindInt(':transaction_return_status', ($_POST['qpstatmsg'] == 'OK') ? 1 : 0);
$Qtransaction->execute();  

if((isset($_GET["qperror"]) && $_GET["qperror"]=="cancel") || $_POST['qpstat']=="008"){
	   $_order_status = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ORDER_CANCELED_STATUS_ID;

	$Qupdate = $lC_Database->query('update :table_orders set orders_status = :orders_status where orders_id = :orders_id');
    $Qupdate->bindTable(':table_orders', TABLE_ORDERS);
    $Qupdate->bindInt(':orders_status', $_order_status);
    $Qupdate->bindInt(':orders_id', $order_id);
    $Qupdate->execute();
	    
	$Qstatus = $lC_Database->query('insert into :table_orders_status_history (orders_id, orders_status_id, date_added, customer_notified, comments) values (:orders_id, :orders_status_id, now(), :customer_notified, :comments)');
    $Qstatus->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
    $Qstatus->bindInt(':orders_id', $order_id);
    $Qstatus->bindInt(':orders_status_id', $_order_status);
    $Qstatus->bindInt(':customer_notified', '0');
    $Qstatus->bindValue(':comments', 'Quickpay Payment not processed');
    $Qstatus->execute();

	     $lC_MessageStack->add('payment', sprintf($lC_Language->get('payment_quickpaypayments_error_payment_problem'), $_GET['qperror']));
            $_SESSION['messageToStack'] = $lC_MessageStack->getAll(); 
            $error = true;

	
}
 if ($error) {
		   
		   lc_redirect(lc_href_link(FILENAME_CHECKOUT, 'payment', 'AUTO'));
	   
	   
}
  $_SESSION["qpcallback"]["orderstatus"] = $_order_status;
?>