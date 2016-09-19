<?php
/*
  ot_quickpay_advanced.php

  payment module re-development and maintainance by MailBeez.com

  Copyright (c) 2008 Jakob Høy Biegel
*/

  class lC_OrderTotal_payment_fee extends lC_OrderTotal {
  var $output;



  var $_title,

      $_code = 'payment_fee',

      $_status = false,

      $_sort_order;

    function lC_OrderTotal_payment_fee(){
       global $lC_Language;
	  $this->output = array();
      $this->_title = $lC_Language->get('order_total_paymentfee_title');
      $this->_description = $lC_Language->get('order_total_paymentfee_description');
     $this->_status = (defined('MODULE_ORDER_TOTAL_PAYMENTFEE_STATUS') && (MODULE_ORDER_TOTAL_PAYMENTFEE_STATUS == 'true') ? true : false);
      $this->_sort_order = (defined('MODULE_ORDER_TOTAL_PAYMENTFEE_SORT_ORDER') ? MODULE_ORDER_TOTAL_PAYMENTFEE_SORT_ORDER : null);

    }

  function process() {
  global $lC_ShoppingCart, $lC_Currencies;
  $cost = $_SESSION['quickpay_fee'];// -set in pre-confirmation check

       if ($cost > 0) {
          // Don't add tax to card fee, as the fee is taxfree according to Nets
          $lC_ShoppingCart->addToTotal($cost); 
          $this->output[] = array('title' => $this->_title . ':',
                                  'text' => $lC_Currencies->format($cost),
                                  'value' => $cost);
             
      }

    }


  }
?>
