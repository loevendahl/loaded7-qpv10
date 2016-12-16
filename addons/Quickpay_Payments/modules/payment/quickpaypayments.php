<?php

//ini_set("display_errors","on");
//error_reporting(E_ALL);

//ini_set("log_errors", 1);
//ini_set("error_log", "/home/dan31362/public_html/loaded/php-error.log");
//error_log( "Hello, errors!" );

 include('addons/Quickpay_Payments/QuickpayApi.php');

/**
*  $Id: quickpaypayments.php v1.0 2013-01-01 datazen $
*
*  LoadedCommerce, Innovative eCommerce Solutions
*  http://www.loadedcommerce.com
*
*  Copyright (c) 2013 Loaded Commerce, LLC
*
*  @author     Loaded Commerce Team
*  @copyright  (c) 2013 Loaded Commerce Team
*  @license    http://loadedcommerce.com/license.html
*/
//include_once(DIR_FS_CATALOG . 'includes/classes/transport.php');

class lC_Payment_quickpaypayments extends lC_Payment {
 /**
  * The public title of the payment module
  *
  * @var string
  * @access protected
  */
  protected $_title;
 /**
  * The code of the payment module
  *
  * @var string
  * @access protected
  */
  protected $_code = 'quickpaypayments';
 /**
  * The status of the module
  *
  * @var boolean
  * @access protected
  */
  protected $_status = false;
 /**
  * The sort order of the module
  *
  * @var integer
  * @access protected
  */
  protected $_sort_order;
 /**
  * The allowed credit card types (pipe separated)
  *
  * @var string
  * @access protected
  */
  protected $_allowed_types;
 /**
  * The order id
  *
  * @var integer
  * @access protected
  */
  protected $_order_id;
 /**
  * The completed order status ID
  *
  * @var integer
  * @access protected
  */
  protected $_order_status_complete;
 /**
  * The credit card image string
  *
  * @var string
  * @access protected
  */
  protected $_card_images;
 /**
  * Constructor
  */
 
  public function lC_Payment_quickpaypayments() {
    global $lC_Language;

    $this->_title = $lC_Language->get('payment_quickpaypayments_title');
    $this->_method_title = $lC_Language->get('payment_quickpaypayments_method_title');
  $this->_status = (defined('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_STATUS') && (ADDONS_PAYMENT_QUICKPAY_PAYMENTS_STATUS == '1') ? true : false);
  
  
    $this->_sort_order = (defined('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_SORT_ORDER') ? ADDONS_PAYMENT_QUICKPAY_PAYMENTS_SORT_ORDER : null);
	$this->creditcardgroup = array();
	$this->email_footer = ($cardlock == "viabill" ? DENUNCIATION : '');

    // CUSTOMIZE THIS SETTING FOR THE NUMBER OF PAYMENT GROUPS NEEDED
        $this->num_groups = 5;

   if (defined('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_STATUS')) {
    $this->initialize();
   }
  }
 /**
  * Initialize the payment module
  *
  * @access public
  * @return void
  */
  public function initialize() {
    global $lC_Database, $lC_Language, $order;

    if ((int)ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ORDER_STATUS_ID > 0) {
      $this->order_status = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ORDER_STATUS_ID;
    }

    if ((int)ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ORDER_STATUS_COMPLETE_ID > 0) {
      $this->_order_status_complete = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ORDER_STATUS_COMPLETE_ID;
    }

 if (is_object($order)) $this->update_status();
        // Store online payment options in local variable
        for ($i = 1; $i <= $this->num_groups; $i++) {
            if (defined('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_GROUP' . $i) && constant('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_GROUP' . $i) != '') {
                if (!isset($this->creditcardgroup[constant('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_GROUP' . $i . '_FEE')])) {
                    $this->creditcardgroup[constant('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_GROUP' . $i . '_FEE')] = array();
                }
                $payment_options = preg_split('[\,\;]', constant('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_GROUP' . $i));
                foreach ($payment_options as $option) {
                   $msg .= $option;
                    $this->creditcardgroup[constant('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_GROUP' . $i . '_FEE')][] = $option;
                }
           
		   
            }
			
        }
   // if (defined('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_TESTMODE') && ADDONS_PAYMENT_QUICKPAY_PAYMENTS_TESTMODE == '1') {
   //   $this->iframe_relay_url = 'https://secure.quickpay.dk/form/';  // sandbox url
   // } else {
	 //    $this->form_action_url = lc_href_link(FILENAME_CHECKOUT, 'payment_template', 'SSL', true, true, true) ;
      //$this->iframe_relay_url = 'https://payment.quickpay.net/';  // production url
   // }
  $this->iframe_params = $this->_getIframeParams();
  $this->form_action_url = lc_href_link(FILENAME_CHECKOUT, 'confirmation', 'SSL', true, true, true) ;   
 //$this->form_action_url = '#';
  }
 /**
  * Disable module if zone selected does not match billing zone
  *
  * @access public
  * @return void
  */
  public function update_status() {
    global $lC_Database, $order, $qp_card, $quickpay_fee;

    if ( ($this->_status === true) && ((int)ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ZONE > 0) ) {
      $check_flag = false;

      $Qcheck = $lC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
      $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
      $Qcheck->bindInt(':geo_zone_id', ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ZONE);
      $Qcheck->bindInt(':zone_country_id', $order->billing['country']['id']);
      $Qcheck->execute();

      while ($Qcheck->next()) {
        if ($Qcheck->valueInt('zone_id') < 1) {
          $check_flag = true;
          break;
        } elseif ($Qcheck->valueInt('zone_id') == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
      }

      if ($check_flag == false) {
        $this->_status = false;
      }
    }
	        if (!isset($_SESSION['qp_card'])){ $_SESSION['qp_card'] = $qp_card;}
        if (isset($_POST['qp_card'])){ $_SESSION['qp_card'] = $_POST['qp_card'];}
         $_SESSION['quickpay_fee'] = $this->get_order_fee();

 
  }
 /**
  * Return the payment selections array
  *
  * @access public
  * @return array
  */
  public function selection() {

        global $lC_Language, $order, $lC_Currencies, $qp_card, $cardlock, $fee;
		if (isset($_SESSION['quickpay_fee'])){ unset($_SESSION['quickpay_fee']);}
        $qty_groups = 0;
		$imgpath = "addons/Quickpay_Payments/images/";
		$fees =array();
        for ($i = 1; $i <= $this->num_groups; $i++) {
            if (constant('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_GROUP' . $i) == '') {
                continue;
            }
			if (constant('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_GROUP' . $i. '_FEE') == '') {
                continue;
            }else{
			$fees[$i] = constant('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_GROUP' . $i . '_FEE');
			}
            $qty_groups++;
        }

 if($qty_groups>1) {
        $selection = array('id' => $this->_code,
	 'module' => $this->_method_title. lc_draw_hidden_field('cardlock', $cardlock ). lc_draw_hidden_field('qp_card', $fee ));
	 
	 }
		
		       $selection['fields'] = array();
               $msg = '';
			   $optscount=0;
	 for ($i = 1; $i <= $this->num_groups; $i++) {
               $options_text = '';
      if (defined('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_GROUP' . $i) && constant('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_GROUP' . $i) != '') {
                $payment_options = preg_split('[\,\;]', constant('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_GROUP' . $i));
			    foreach ($payment_options as $option) {
           
			  if($option=="creditcard"){
			  $optscount++;
				  //You can extend the following cards-array and upload corresponding titled images to images/icons
				  $cards= array('dankort','visa','american_express','jcb','mastercard');
				      foreach ($cards as $optionc) {
			 				$iconc ="";
$iconc = (file_exists($imgpath."cc_".$optionc.".png") ? $imgpath."cc_".$optionc.".png": $iconc);
$iconc = (file_exists($imgpath."cc_".$optionc.".jpg") ? $imgpath."cc_".$optionc.".jpg": $iconc);
$iconc = (file_exists($imgpath."cc_".$optionc.".gif") ? $imgpath."cc_".$optionc.".gif": $iconc);   
			//define payment icon width and height
			   $w= 35;
			   $h= 22;
			   $space = 5;		   
				   
				   $msg .= lc_image($iconc,$optionc,$w,$h,'style="position:relative;border:0px;float:left;margin:'.$space.'px;" ');
					 
					 
					  }
					  $options_text=$msg;
			
				
				    $cost = $this->calculate_order_fee($_SESSION['lC_ShoppingCart_data']['total_cost'], $fees[$i]);	
				
				
 if($qty_groups==1){
		 
			 $selection = array('id' =>  $this->_code,
         'module' => '<div class="moduleRow" style="display:table; border-radius: 6px; width:100%" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectQuickPayRowEffect(this, ' . ($optscount-1) . ',\''.$option.'\')">' .$options_text.($cost !=0 ? ' (+'.$currencies->format($cost, true, $order->info['currency'], $order->info['currency_value']).')' :'').'
                 </div>'.lc_draw_hidden_field('cardlock', $option).lc_draw_hidden_field('qp_card', (isset($fees[1])) ? $fees[1] : '0'));
		 
		 
	 }else{
				
					$selection['fields'][] = array('title' => '<div style="display:table; border-radius: 6px; width:100%" class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectQuickPayRowEffect(this, ' . ($optscount-1) . ',\''.$option.'\'); document.checkout_payment.qp_card.value = \''.$fees[$i].'\'; setQuickPay();">
                     ' . $options_text.'
                         </div>',
                    'field' => ($cost !=0 ? ' (+'.$lC_Currencies->format($cost).') ' :''));


	 }//end qty=1
			 
			  }
			  
			  if($option != "creditcard"){
				  //upload images to images/icons corresponding to your chosen cardlock groups in your payment module settings
				  //OPTIONAL image if different from cardlogo, add _payment to filename
			
			  $selectedopts = explode(",",$option);	
				$icon ="";
				foreach($selectedopts as $option){
				$optscount++;
				
$icon = (file_exists($imgpath."cc_".$option.".png") ? $imgpath."cc_".$option.".png": $icon);
$icon = (file_exists($imgpath."cc_".$option.".jpg") ? $imgpath."cc_".$option.".jpg": $icon);
$icon = (file_exists($imgpath."cc_".$option.".gif") ? $imgpath."cc_".$option.".gif": $icon);   
$icon = (file_exists($imgpath."cc_".$option."_payment.png") ? $imgpath."cc_".$option."_payment.png": $icon);
$icon = (file_exists($imgpath."cc_".$option."_payment.jpg") ? $imgpath."cc_".$option."_payment.jpg": $icon);
$icon = (file_exists($imgpath."cc_".$option."_payment.gif") ? $imgpath."cc_".$option."_payment.gif": $icon); 				   
		$space = 5;
		//define payment icon width
		if(strstr($icon, "_payment")){
			$w=120;
			$h= 27;
			if(strstr($icon, "3d")){
				$w=60;
			}
		}else{
			   $w= 35;
			   $h= 22;
			   
			
		}
		
		 $cost = $this->calculate_order_fee( $_SESSION['lC_ShoppingCart_data']['total_cost'], $fees[$i]);
		 $options_text = '<div style="float:left;">'.lc_image($icon,$this->get_payment_options_name($option),$w,$h,' style="position:relative;border:0px;float:left;margin:'.$space.'px;" ').'</div><div style="display:table-cell;white-space:nowrap;vertical-align:middle;font-size: 18px;font-color:#666;" >'.$this->get_payment_options_name($option).($cost !=0 ? ' <font size="2" >(+'.$lC_Currencies->format($cost).')</font>' :'').'</div>';
				

				
				   	
			
		 if($qty_groups==1){
		 
		 $selection = array('id' =>  $this->_code,
         'module' => '<div class="moduleRow" style="display:table; border-radius: 6px; width:100%" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectQuickPayRowEffect(this, ' . ($optscount-1) . ',\''.$option.'\')">' .$options_text.'</div>'.lc_draw_hidden_field('cardlock', $option).lc_draw_hidden_field('qp_card', (isset($fees[1])) ? $fees[1] : '0'));
		 

        } else {
					$selection['fields'][] = array('title' => '<div class="moduleRow" style="display:table; border-radius: 6px; width:100%"  onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectQuickPayRowEffect(this, ' . ($optscount-1) . ',\''.$option.'\'); document.checkout_payment.cardlock.value = \''.$option.'\'; document.checkout_payment.qp_card.value = \''.$fees[$i].'\'; setQuickPay();" >
                       ' . $options_text.'
                    </div>',
                    'field' => '');

				
	 }//end qty
				
				}
					}
				}
		    }
        }
			
	
	//end payment module class

//$selection['module'] .= '</div>';
    
   
            $js_function = '
        <script language="javascript"><!-- 
     
          function setQuickPay() {
			
			
          	var radioLength = document.checkout_payment.payment_method.length;
          	for(var i = 0; i < radioLength; i++) {
				
          		document.checkout_payment.payment_method[i].checked = false;
          		if(document.checkout_payment.payment_method[i].value == "quickpaypayments") {
				
          			document.checkout_payment.payment_method[i].checked = true;
					
          		}
          	}
          }
          function selectQuickPayRowEffect(object, buttonSelect, option) {
            if (!selected) {
              if (document.getElementById) {
                selected = document.getElementById("default-selected");
              } else {
                selected = document.all["default-selected"];
              }
            }
          
            if (selected) selected.className = "moduleRow";
            object.className = "module-row-selected";
            selected = object;
            document.checkout_payment.cardlock.value = option;
	
            setQuickPay();
          }
        //--></script>
        ';
            $selection['module'] .= $js_function;


        return $selection;
	
	
  }
  
 public function getJavascriptBlock(){
	 global  $lC_Language;
	 $cardlockcheck =  "\n" . ' 
	 
	 
	          	var radioLengthqp = document.checkout_payment.payment_method.length;
				var qpselected = false;
          	for(var i = 0; i < radioLengthqp; i++) {
				
          		
          		if(document.checkout_payment.payment_method[i].value == "quickpaypayments" && document.checkout_payment.payment_method[i].checked == true) {
				
          			var qpselected = true;
					
          		}
          	}
	 
	 
	 if (qpselected == true && document.checkout_payment.cardlock.value == "") {' . "\n" .

             '    error_message = error_message + "' . $lC_Language->get('addons_payment_quickpay_payments_js_no_cardlock_selected') . '\n";' . "\n" .

             '    error = 1;' . "\n" .

             '  }' . "\n\n"
		 
	;
	 
	 return $cardlockcheck;
	 
 }
 /**
  * Perform any pre-confirmation logic
  *
  * @access public
  * @return boolean
  */
  public function pre_confirmation_check() {
$this->update_status();	  
$_SESSION['quickpay_fee'] = $this->get_order_fee();


   // return false;
  }
 /**
  * Perform any post-confirmation logic
  *
  * @access public
  * @return integer
  */
  public function confirmation() {
    if($this->email_footer !='' ){
        return array('title' => $this->email_footer);
    }else{return false;}
	
	//return false;
  }
 /**
  * Return the confirmation button logic
  *
  * @access public
  * @return string
  */
  public function process_button() {

    global  $lC_Language, $lC_ShoppingCart, $lC_Currencies, $lC_Customer;  
	$_SESSION['cartSync']['paymentMethod'] = $this->_code;
    $orderid = lC_Order::insert(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_PROCESSING_STATUS_ID);
    // store the cartID info to match up on the return - to prevent multiple order IDs being created
	//Quickpay doesn't run in iframes as on development time, but anyway...
    $_SESSION['cartSync']['iFrameParams'] = $this->_iframe_params($orderid);
    $_SESSION['cartSync']['cartID'] = $_SESSION['cartID'];
    $_SESSION['cartSync']['prepOrderID'] = $_SESSION['prepOrderID'];  
    $_SESSION['cartSync']['orderCreated'] = TRUE;
	$_SESSION['cartSync']['orderID'] = $orderid;
 
 return $this->_iframe_params($orderid);

  }
 /**
  * Parse the response from the processor
  *
  * @access public
  * @return string
  */
  public function process() {
	 global $lC_Language, $lC_Database, $lC_MessageStack;
 //process returnvalues handled by [root]callback.php
     $this->_order_id = $_SESSION['cartSync']['orderID'];
    lC_Order::process($this->_order_id, $_SESSION["qpcallback"]["orderstatus"]);
    unset($_SESSION["qpcallback"]);
	unset($_SESSION['quickpay_fee']);
  }
 /**
  * Check the status of the pasyment module
  *
  * @access public
  * @return boolean
  */
  public function check() {
    if (!isset($this->_check)) {
      $this->_check = defined('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_STATUS');
    }

    return $this->_check;
  }
  
  
  private function _iframe_params($order_id) {
    global $lC_Language, $lC_ShoppingCart, $lC_Currencies, $lC_Customer;
	$process_button_string = '';
		$process_fields ='';
 if($_POST['callquickpay'] == "go") {
        $process_parameters = array();

       // $qp_protocol = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_PROTOCOL;
       // $qp_msgtype = 'authorize';
        $qp_merchant_id = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MERCHANTID;
		$qp_aggreement_id = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_AGGREEMENTID;
		$qp_apikey = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_USERAPIKEY;    
		
		$lang_split = explode("_",$_SESSION['language']);
		$qp_language = $lang_split[0];


        $qp_order_id = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ORDERPREFIX.$order_id;
		$qp_order_amount = 100 * $lC_Currencies->formatRaw($lC_ShoppingCart->getTotal(), $lC_Currencies->getCode());
        //$qp_vat_amount = ($order->info['tax'] ? $order->info['tax'] : "0.00");
		//	$qp_product_id = "P03";
		//	$qp_category = MODULE_PAYMENT_QUICKPAY_ADVANCED_PAII_CAT;
		//	$qp_reference_title = $qp_order_id;
		$qp_currency_code = $_SESSION['currency'];
       
	    $qp_continueurl = HTTP_SERVER.DIR_WS_HTTP_CATALOG.'checkout.php?process';
        $qp_cancelurl = HTTP_SERVER.DIR_WS_HTTP_CATALOG.'callback.php?qperror=cancel&qporder='.$order_id;
        $qp_callbackurl = HTTP_SERVER.DIR_WS_HTTP_CATALOG.'callback.php?qporder='.$qp_order_id;
        $qp_autofee = (ADDONS_PAYMENT_QUICKPAY_PAYMENTS_AUTOFEE == '1' ? '1' :'0');
		$qp_autocapture = (ADDONS_PAYMENT_QUICKPAY_PAYMENTS_AUTOCAPTURE == '1' ? '1' :'0');
        $qp_cardtypelock = $_POST['cardlock'];
		$qp_subscription = (ADDONS_PAYMENT_QUICKPAY_PAYMENTS_SUBSCRIPTION == '1' ? '1' :'0');

			            $qp_description = $qp_order_id;
						$qp_category = ADDONS_PAYMENT_QUICKPAY_PAYMENTS_PAII_CAT;
						$qp_product_id = "P03";
						$qp_vat_amount = ($_SESSION['lC_ShoppingCart_data']['tax'] > 0 ? $_SESSION['lC_ShoppingCart_data']['tax'] : "0.00");				
				


// register fields to hand over


		$process_parameters = array(
					'agreement_id'                 => $qp_aggreement_id,
					'amount'                       => $qp_order_amount,
					'autocapture'                  => $qp_autocapture,
					'autofee'                      => $qp_autofee,
					//'branding_id'                  => $qp_branding_id,
					'callbackurl'                  => $qp_callbackurl,
					'cancelurl'                    => $qp_cancelurl,
					'continueurl'                  => $qp_continueurl,
					'currency'                     => $qp_currency_code,
					'description'                  => $qp_description,
					//'google_analytics_client_id'   => $qp_google_analytics_client_id,
					//'google_analytics_tracking_id' => $analytics_tracking_id,
					'language'                     => $qp_language,
					'merchant_id'                  => $qp_merchant_id,
					'order_id'                     => $qp_order_id,
					'payment_methods'              => $qp_cardtypelock,
					//'product_id'                   => $qp_product_id,
					//'category'                     => $qp_category,
					//'reference_title'              => $qp_reference_title,
					//'vat_amount'                   => $qp_vat_amount,
					'subscription'                 => $qp_subscription,
					'version'                      => 'v10'
						);
						
     /*   reset($process_parameters);
        while (list ($key, $value) = each($process_parameters)) {
            $process_fields .= lc_draw_hidden_field($key, $value) . "\n";
        }
*/
      //custom vars
	   $customvar_array = array('variables[customers_id]' => $customer_id,
                    'variables[customers_name]' => $lC_ShoppingCart->getBillingAddress('firstname') . ' ' . $lC_ShoppingCart->getBillingAddress('lastname'),
                    'variables[customers_company]' => $lC_ShoppingCart->getBillingAddress('company'),
                    'variables[customers_street_address]' => $lC_ShoppingCart->getBillingAddress('street_address'),
                    'variables[customers_suburb]' => $lC_ShoppingCart->getBillingAddress('suburb'),
                    'variables[customers_city]' => $lC_ShoppingCart->getBillingAddress('city'),
                    'variables[customers_postcode]' => $lC_ShoppingCart->getBillingAddress('postcode'),
                    'variables[customers_state]' => $lC_ShoppingCart->getBillingAddress('state'),
                    'variables[customers_country]' => $lC_ShoppingCart->getBillingAddress('country_iso_code_3'),
                    'variables[customers_telephone]' => $lC_Customer->getTelephone(),
                    'variables[customers_email_address]' => $lC_Customer->getEmailAddress(),
                    'variables[date_purchased]' => date("d")."-".date("m")."-".date("Y"),
                    'variables[shopversion]' => 'Loaded Commerce 7'); 
                       
       /* while (list ($key, $value) = each($customvar_array)) {
            $process_fields .= lc_draw_hidden_field("CUSTOM_".$key, $value) . "\n";
        }
		*/

	$process_parameters = array_merge($process_parameters,$customvar_array);
	
       // $process_button_string .= $process_fields;

	   $apiorder= new QuickpayApi();
	$apiorder->setOptions(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_USERAPIKEY);
	
	//set status request mode
	$apiorder->mode = ($qp_subscription == "0" ? "payments/" : "subscriptions/");
	  	//been here before?
	$exists = $this->get_quickpay_order_status($qp_order_id, $apiorder->mode);
	
  $qid = $exists["qid"];
	//set to create/update mode
	
  if($exists["qid"] == null){

      //create new quickpay order	
  $storder = $apiorder->createorder($qp_order_id, $qp_currency_code, $process_parameters);
  $qid = $storder["id"];

    }else{
    $qid = $exists["qid"];
    }
			
		
		$storder = $apiorder->link($qid, $process_parameters);
	ob_end_clean();
		header("location: "	.$storder['url']);
	exit;
	/*$process_button_string .= json_encode($process_parameters)."<script>
     alert('qp ".$qp_order_id."-".$order_id."');
// window.location.replace('".$storder['url']."');
      </script>";
*/
	
	 
}else{
        $process_button_string .= lc_draw_hidden_field('qp_card', $_POST['qp_card']);
        $process_button_string .= lc_draw_hidden_field('cardlock', $_POST['cardlock']);
		$process_button_string .= lc_draw_hidden_field('callquickpay', 'go');
      
}
     
	 return $process_button_string;     
}
  
  
 /**
  * Determine the iFrame paramters depending on device params
  *
  * @access private
  * @return string
  */
  private function _getIframeParams() {
    
    // how many content columns
    $content_span = (isset($_SESSION['content_span']) && $_SESSION['content_span'] != NULL) ? $_SESSION['content_span'] : '6';
    
    $fHeight = '500px';
    $fScroll = 'no';    
    
    switch($content_span) {
      case '9':
        $fStyle = 'margin-left=10px';
        $fWidth = '500px';       
        break;
        
      case '12':
        $fStyle = 'margin-left=120px';
        $fWidth = '500px';       
        break;
        
      default :
        $fStyle = 'margin-left=-20px';
        $fWidth = '380px';      
      
    }
    
    $mediaType = (isset($_SESSION['mediaType']) && $_SESSION['mediaType'] != NULL) ? $_SESSION['mediaType'] : 'desktop';
    $mediaSize = (isset($_SESSION['mediaSize']) && $_SESSION['mediaSize'] != NULL) ? (int)$_SESSION['mediaSize'] : '500';
    
    $cWidth = ($mediaSize > 500) ? 500 : ($mediaSize * .90);
    $fWidth = (string)$cWidth . 'px';     
    
    switch($mediaType) {
      case 'mobile-portrait' :
        $fHeight = '510px';
        $fStyle = '';
        break;
      case 'mobile-landscape' :
        $fStyle = '';        
        break;
      case 'small-tablet-portrait' :
        break;   
      case 'small-tablet-landscape' :
        $fWidth = '320px';
        break;                                         
      case 'tablet-portrait' :
        $fWidth = '320px';
        break;  
      case 'tablet-landscape' :
        $fWidth = '445px';
        $fStyle = '';                
        break;                                                                 
      default : // desktop
    }    
    
    return 'width=' . $fWidth . '&height=' . $fHeight . '&scroll=' . $fScroll . '&' . $fStyle;
  }


//------------- Internal help functions-------------------------
// $order_total parameter must be total amount for current order including tax
// format of $fee parameter: "[fixed fee]:[percentage fee]"
 private function calculate_order_fee($order_total, $fee) {
       
	    list($fixed_fee, $percent_fee) = explode(':', $fee);
		
		
        return ((float) $fixed_fee + (float) $order_total * ($percent_fee / 100));
    }

  private function get_order_fee() {
        global $_POST, $order, $lC_Currencies, $quickpay_fee;
        $quickpay_fee = 0.0;
        if (isset($_POST['qp_card']) && strpos($_POST['qp_card'], ":")) {
            $quickpay_fee = $this->calculate_order_fee($_SESSION['lC_ShoppingCart_data']['total_cost'], $_POST['qp_card']);
        }
		return $quickpay_fee;
    }
private function get_quickpay_order_status($qp_order_id,$mode="") {

	$api= new QuickpayApi();
	

	$api->setOptions(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_USERAPIKEY);

  try {
	$api->mode = ($mode=="payments/" ? "payments?order_id=" : "subscriptions?order_id=");
	

    // Commit the status request, checking valid transaction id
    $st = $api->status($qp_order_id);
		$eval = array();
	if($st[0]["id"]){

    $eval["oid"] = str_replace(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ORDERPREFIX,"", $st[0]["order_id"]);
	$eval["qid"] = $st[0]["id"];
	}else{
	$eval["oid"] = null;
	$eval["qid"] = null;	
	}
  
  } catch (Exception $e) {
   $eval = 'QuickPay Status: ';
		  	// An error occured with the status request
          $eval .= 'Problem: ' . $e->getMessage() ;
	
  }

    return $eval;
  } 


    private function get_payment_options_name($payment_option) {
		global $lC_Language;
        switch ($payment_option) {
            case '3d-jcb': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_JCB_3D_TEXT');
            case '3d-maestro': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MAESTRO_3D_TEXT');
            case '3d-maestro-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MAESTRO_DK_3D_TEXT');
            case '3d-mastercard': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MASTERCARD_3D_TEXT');
            case '3d-mastercard-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ADVANCED_MASTERCARD_DK_3D_TEXT');
            case '3d-visa': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ADVANCED_VISA_3D_TEXT');
            case '3d-visa-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ADVANCED_VISA_DK_3D_TEXT');
            case '3d-visa-electron': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ADVANCED_VISA_ELECTRON_3D_TEXT');
            case '3d-visa-electron-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_VISA_ELECTRON_DK_3D_TEXT');
            case '3d-visa-debet': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_VISA_DEBET_3D_TEXT');
			case '3d-visa-debet-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_VISA_DEBET_DK_3D_TEXT');
			case '3d-creditcard': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_CREDITCARD_3D_TEXT');
            case 'american-express': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_AMERICAN_EXPRESS_TEXT');
            case 'american-express-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_AMERICAN_EXPRESS_DK_TEXT');
            case 'dankort': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_DANKORT_TEXT');
            case 'danske-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_DANSKE_DK_TEXT');
            case 'diners': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_DINERS_TEXT');
            case 'diners-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_DINERS_DK_TEXT');
            case 'edankort': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_EDANKORT_TEXT');
            case 'jcb': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_JCB_TEXT');
            case 'mastercard': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MASTERCARD_TEXT');
            case 'mastercard-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MASTERCARD_DK_TEXT');
			case 'mastercard-debet': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MASTERCARD_DEBET_TEXT');
            case 'mastercard-debet-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_MASTERCARD_DEBET_DK_TEXT');
            case 'nordea-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_NORDEA_DK_TEXT');
            case 'visa': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_VISA_TEXT');
            case 'visa-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_VISA_DK_TEXT');
            case 'visa-electron': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_VISA_ELECTRON_TEXT');
            case 'visa-electron-dk': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_VISA_ELECTRON_DK_TEXT');
		    case 'creditcard': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_CREDITCARD_TEXT');
			case 'ibill':  return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_IBILL_DESCRIPTION');
			case 'viabill':  return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_IBILL_DESCRIPTION');
            case 'fbg1886': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_FBG1886_TEXT');
            case 'paypal': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_PAYPAL_TEXT');
            case 'sofort': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_SOFORT_TEXT');
           // case 'paii': return $lC_Language->get('ADDONS_PAYMENT_QUICKPAY_PAYMENTS_PAII_TEXT');

        }
        return '';
    }
			
        }
	
?>