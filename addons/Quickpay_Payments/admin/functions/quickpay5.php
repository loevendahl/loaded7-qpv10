<?php
/*
  quickpay5.php

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 Jakob Høy Biegel
Protocol 7 version Copyright (c) 2012-14 Kim Løvendahl
  Released under the GNU General Public License
  
  Process online payment using Quickpay payment class. Using Try/Catch as this is available in php5
*/

  function get_quickpay_api_supported() {
    $qp = new quickpay;
    return $qp->init();
  }

  function get_quickpay_status($order_id) {
 global $transaction;

  try {
    $qp = new quickpay;
    // Set values
	$qp->set_protocol(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_PROTOCOL);
    $qp->set_msgtype('status');
    $qp->set_merchant(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_SHOPID);
    $qp->set_transaction($transaction);
    $qp->set_md5checkword(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MD5WORD);
    // Commit the status request
    $eval = $qp->status();
  } catch (Exception $e) {
      // Print error message
      echo "QuickPay Error: Caught an exception in 
          " . $e->getFile() . " at line 
          " . $e->getLine() . "<br />
          " . $e->getMessage() . "<br />";
  }
    return $eval;
  }  
  

  function get_quickpay_reverse($order_id) {
    global $lC_MessageStack, $transaction;


  try {
    $qp = new quickpay;
    // Set values
	$qp->set_protocol(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_PROTOCOL);
    $qp->set_msgtype('cancel');
    $qp->set_merchant(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_SHOPID);
    $qp->set_transaction($transaction);
    $qp->set_md5checkword(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MD5WORD);
    // Commit the reversal
    $eval = $qp->reverse();
  } catch (Exception $e) {
      // Print error message
      echo "QuickPay Error: Caught an exception in 
          " . $e->getFile() . " at line 
          " . $e->getLine() . "<br />
          " . $e->getMessage() . "<br />";
  }

    $result = 'QuickPay Reverse: ';
    if ($eval) {
      if ($eval['qpstat'] === '000') {
          // The reversal was completed
          $result .= 'Succes: ' . $eval['qpstatmsg'];
          $lC_MessageStack->output('success','',$result);
          return true;
      } else {
          // An error occured with the reversal
          $result .= 'Failure: ' . $eval['qpstatmsg'];
		  $lC_MessageStack->output('warning','',$result);
          return false;
      }
    }
    // Communication error
    $result .= 'Failed: Communication Error'; 
    $lC_MessageStack->output('warning','',$result);
    return false;
  }  

  
  function get_quickpay_capture($order_id, $amount) {
    global $lC_MessageStack, $transaction;





  try {
    $qp = new quickpay;
    // Set values
    $qp->set_msgtype('capture');
	$qp->set_protocol(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_PROTOCOL);
    $qp->set_md5checkword(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MD5WORD);
    $qp->set_merchant(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_SHOPID);
    $qp->set_transaction($transaction);
    $qp->set_amount($amount);
    // Commit the capture
    $eval = $qp->capture();
  } catch (Exception $e) {
      // Print error message
      echo "QuickPay Error: Caught an exception in 
          " . $e->getFile() . " at line 
          " . $e->getLine() . "<br />
          " . $e->getMessage() . "<br />";
  }

    $result = 'QuickPay Capture ';
    if ($eval) {
      if ($eval['qpstat'] === '000') {
          // The reversal was completed
          $result .= 'Succes: ' . $eval['qpstatmsg'];
          $lC_MessageStack->output('success','',$result);
          return true;
      } else {
          // An error occured with the reversal
          $result .= 'Failure: ' . $eval['qpstatmsg'];
          $lC_MessageStack->output('warning','',$result . ' : ' . $amount);
          return false;
      }
    }
    // Communication error
    $result .= 'Failed: Communication Error'; 
    $lC_MessageStack->output('warning','', $result);
    return false;
  }  

  
  function get_quickpay_credit($order_id, $amount) {
    global $lC_MessageStack, $transaction;



    try {
      $qp = new quickpay;
      // Set values
	  $qp->set_protocol(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_PROTOCOL);
      $qp->set_msgtype('refund');
      $qp->set_md5checkword(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MD5WORD);
      $qp->set_merchant(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_SHOPID);
      $qp->set_transaction($transaction);
      $qp->set_amount($amount);
      // Commit the capture
      $eval = $qp->credit();
    } catch (Exception $e) {
      // Print error message
      echo "Caught an exception in 
          " . $e->getFile() . " at line 
          " . $e->getLine() . "<br />
          " . $e->getMessage() . "<br />";
    }    
      $result = 'QuickPay Credit ';
      if ($eval) {
        if ($eval['qpstat'] === '000') {
            // The credit was completed
            $result .= 'Succes: ' . $eval['qpstatmsg'];
            $lC_MessageStack->output('success','',$result);
            return true;
        } else {
            // An error occured with the credit
            $result .= 'Failure: ' . $eval['qpstatmsg'];
            $lC_MessageStack->output('warning','',$result . ' : ' . $amount);
            return false;
        }
      }
      // Communication error
      $result .= 'Failed: Communication Error'; 
      $lC_MessageStack->output('warning','', $result);
      return false;
  }  
 
?>