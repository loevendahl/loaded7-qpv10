<?php
/*
  quickpay10.php

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

Protocol 10 version Copyright (c) 2012 Kim Løvendahl
  Released under the GNU General Public License

*/
function json_message($input){
	
	$dec = json_decode($input,true);
	
	$message= $dec["message"];
	//get last error
	
	$text = $dec["errors"]["amount"][0];
	return $message. " amount ".$text;
	
	
}
 
 

  function get_quickpay_reverse($order_id) {
    global $messageStack;


  try {
	  $qp = new QuickpayApi;
    // Commit the reversal
    $eval = $qp->cancel(get_transactionid($order_id));
      $result = 'QuickPay Reverse: ';
    if ($eval) {
		$operations = array_reverse($eval["operations"]);
      if ($operations[0]["qp_status_code"] === '20000') {
          // The reversal was completed
          $result .= 'Succes: ' . $operations[0]["qp_status_msg"];
          $lC_MessageStack->add('payment', $result);
      }
    }
  
  
  } catch (Exception $e) {
      		// An error occured with the reversal
          $result .= 'Failure: ' . json_message($e->getMessage()) ;
         $lC_MessageStack->add('payment', $result . ' : ' . number_format($amount/100,2,',','.')." ".$eval["currency"]); 
  }



  }  

  
  function get_quickpay_capture($order_id, $amount) {
    global $messageStack;

  try {
    $qp = new QuickpayApi;  
    // Set values
    $id = get_transactionid($order_id);
    // Commit the capture
    $eval = $qp->capture($id,$amount);
      $result = 'QuickPay Capture ';
    
	if ($eval) {
		$operations= array_reverse($eval["operations"]);
      if ($operations[0]["qp_status_code"] == '20000') {
          // The reversal was completed
          $result .= 'Succes: ' . $operations[0]["qp_status_msg"];
          $lC_MessageStack->add('payment', $result . ' : ' . number_format($amount/100,2,',','.')." ".$eval["currency"]);
       
      }
    }
  
  
  } catch (Exception $e) {
      // Print error message
     
		         // An error occured with the capture
          $result .= 'Failure: ' . json_message($e->getMessage()) ;
         $lC_MessageStack->add('payment', $result . ' : ' . number_format($amount/100,2,',','.')." ".$eval["currency"]);
  }
 


  
  }  

  
  function get_quickpay_credit($order_id, $amount) {
    global $messageStack;
    try {
    $qp = new QuickpayApi;  
    // Set values
    $id = get_transactionid($order_id);
      // Commit the capture
      $eval = $qp->refund($id, $amount);
    $result = 'QuickPay Credit ';
      if ($eval) {
		$operations= array_reverse($eval["operations"]);
        if ($operations[0]["qp_status_code"] == '20000') {
            // The credit was completed
            $result .= 'Succes: ' . $operations[0]["qp_status_msg"];
  
			 $lC_MessageStack->add('payment', $result . ' : ' . number_format($amount/100,2,',','.')." ".$eval["currency"]);
          
        }
      }
   
    } catch (Exception $e) {
      // Print error message
	   // An error occured with the credit
            $result .= 'Failure: ' . json_message($e->getMessage());
            $lC_MessageStack->add('payment', $result);
     
    }    

  }  

  
  function get_transactionid($order_id) {
	  global $order;

            $apistatus= new QuickpayApi();
	        $apistatus->setOptions(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_USERAPIKEY);
			$apistatus->mode = (ADDONS_PAYMENT_QUICKPAY_PAYMENTS_SUBSCRIPTION == "-1" ? "payments?order_id=" : "subscriptions?order_id=");
    // Commit the status request, checking valid transaction id
	$qporder_id = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ORDERPREFIX.$order_id;
    $st = $apistatus->status($qporder_id);
     /*if(!$st[0]['id']){
		 
	 $st = $apistatus->createorder($qporder_id, $order->info['currency']);
	 }*/
	 
	$order->info['cc_transactionid'] = $st[0]['id'];
	 return $st[0]['id'];	
		
  }
?>