<?php
/*
  $Id: controller.php v1.0 2013-04-20 datazen $

  Loaded Commerce, Innovative eCommerce Solutions
  http://www.quickpay.com

  Copyright (c) 2013 Loaded Commerce, LLC

  @author     Loaded Commerce Team
  @copyright  (c) 2013 LoadedCommerce Team
  @license    http://quickpay.com/license.html
*/
class Quickpay_Payments extends lC_Addon { // your addon must extend lC_Addon
  /*
  * Class constructor
  */

	
  public function Quickpay_Payments() {    
    global $lC_Language;    
   /**
    * The addon type (category)
    * valid types; payment, shipping, themes, checkout, catalog, admin, reports, connectors, other 
    */    
    $this->_type = 'payment';
   /**
    * The addon class name
    */    
    $this->_code = 'Quickpay_Payments';       
   /**
    * The addon title used in the addons store listing
    */     
    $this->_title = $lC_Language->get('addon_payment_quickpaypayments_title');
   /**
    * The addon description used in the addons store listing
    */     
    $this->_description = $lC_Language->get('addon_payment_quickpaypayments_description');
   /**
    * The developers name
    */    
    $this->_author = 'Quickpay - BLKOM';
   /**
    * The developers web address
    */    
    $this->_authorWWW = 'http://www.blkom.dk';    
   /**
    * The addon version
    */     
    $this->_version = '2.0.0';
   /**
    * The Loaded 7 core compatibility version
    */     
    $this->_compatibility = '7.0.1.1'; // the addon is compatible with this core version and later   
   /**
    * The base64 encoded addon image used in the addons store listing
    */     
    $this->_thumbnail = lc_image(DIR_WS_CATALOG . 'addons/' . $this->_code . '/images/quickpay.png', $this->_title);
   /**
    * The mobile capability of the addon
    */ 
    $this->_mobile_enabled = true;    
   /**
    * The addon enable/disable switch
    */    
    $this->_enabled = (defined('ADDONS_PAYMENT_' . strtoupper($this->_code) . '_STATUS') && @constant('ADDONS_PAYMENT_' . strtoupper($this->_code) . '_STATUS') == '1') ? true : false;      
  }
 /**
  * Checks to see if the addon has been installed
  *
  * @access public
  * @return boolean
  */
  public function isInstalled() {
    return (bool)defined('ADDONS_PAYMENT_' . strtoupper($this->_code) . '_STATUS');
  }
 /**
  * Install the addon
  *
  * @access public
  * @return void
  */
  public function install() {
    global $lC_Database;
 

    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable AddOn', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_STATUS', '-1', 'Do you want to enable this addon?', '6', '0', 'lc_cfg_use_get_boolean_value', 'lc_cfg_set_boolean_value(array(1, -1))', now())");
  
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_MERCHANTID', '', 'Enter your Quickpay Merchant ID', '6', '0', now())");
		
		$lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Set window user aggreement ID', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_AGGREEMENTID', '', 'The WINDOW USER Agreement ID for your merchant account', '6', '0', now())");
	$lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Set API user key', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_USERAPIKEY', '', 'The key for your API USER', '6', '0', now())");
				
 
//    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('TEST Mode', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_MODE', '1', 'Set to \'Yes\' for sandbox test environment or set to \'No\' for production environment.', '6', '0', 'lc_cfg_use_get_boolean_value', 'lc_cfg_set_boolean_value(array(1, -1))', now())");
	       
	
	$lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Subscription payment', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_SUBSCRIPTION', '-1', 'Set to \'Yes\' for subscription payment as default.', '6', '0',  'lc_cfg_use_get_boolean_value', 'lc_cfg_set_boolean_value(array(1, -1))',now())");
	
	$lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Auto capture', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_AUTOCAPTURE', '-1', 'Set to \'Yes\' for autocapture as default.', '6', '0',  'lc_cfg_use_get_boolean_value', 'lc_cfg_set_boolean_value(array(1, -1))',now())");
	
	$lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Auto fee', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_AUTOFEE', '-1', 'Set to \'Yes\' for auto fee as default.', '6', '0',  'lc_cfg_use_get_boolean_value', 'lc_cfg_set_boolean_value(array(1, -1))',now())");		
		

        for ($i = 1; $i <= 5; $i++) {
			if($i==1){
				$qp_group='viabill';
				$qp_groupfee = '0:0';
			}else if($i==2){
				$qp_group='creditcard';
				$qp_groupfee = '0:0';
		
			}else{
				$qp_group='';
				$qp_groupfee ='0:0';
			}

            $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Group " . $i . " Payment Options ', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_GROUP" . $i . "', '" . $qp_group . "', 'Comma seperated options included in Group " . $i . ", maximum 255 chars <a href=\"https://tech.quickpay.net/appendixes/payment-methods/\" target=\"_blank\"><u>available options</u></a><br>Example: <b>creditcard OR viabill OR dankort,danske-dk</b>', '6', '6', now())");
        
		
		    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Group " . $i . " Payments fee', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_GROUP" . $i . "_FEE', '" . $qp_groupfee . "', 'Fee for Group " . $i . " payments (fixed fee-percentage fee)<br>Example: <b>1.45:0.10</b>', '6', '6', 'lc_cfg_set_input_field', now())");
        }
	
//$lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Swipp shop category', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_PAII_CAT','', 'Must be set, if using Swipp, ', '6', '0','lc_cfg_set_paii_list_pulldown_menu', now())");	

	   
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'lc_cfg_use_get_zone_class_title', 'lc_cfg_set_zone_classes_pull_down_menu', now())");

    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Preparing Status', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_PROCESSING_STATUS_ID', '3', 'Set the Preparing Notification status of orders made with this payment module', '6', '7', 'lc_cfg_use_get_order_status_title', 'lc_cfg_set_order_statuses_pull_down_menu', now())");
	
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Complete Status', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_ORDER_DEFAULT_STATUS_ID', '1', 'Set the status of orders made with this payment module', '6', '8', 'lc_cfg_use_get_order_status_title', 'lc_cfg_set_order_statuses_pull_down_menu', now())");   

    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Canceled Status', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_ORDER_CANCELED_STATUS_ID', '5', 'Set the status of <b>Canceled</b> orders made with this payment module', '6', '9', 'lc_cfg_use_get_order_status_title', 'lc_cfg_set_order_statuses_pull_down_menu', now())");
			

$lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Order Prefix', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_ORDERPREFIX', '000', 'Sets Quickpay ordernumber prefix (optional)', '6', '0', now())");
       
	    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'ADDONS_PAYMENT_" . strtoupper($this->_code) . "_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , now())");
		
    $lC_Database->simpleQuery("ALTER TABLE " . TABLE_ORDERS . " CHANGE payment_method payment_method VARCHAR( 512 ) NOT NULL");


 /*   $lC_Database->simpleQuery("CREATE TABLE IF NOT EXISTS lc_quickpay (
  orders_id int(11) NOT NULL,
  transaction text NOT NULL,
  cardtype text NOT NULL,
  cardnumber text NOT NULL,
  cardhash text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1");
*/

	
  }
 /**
  * Return the configuration parameter keys an an array
  *
  * @access public
  * @return array
  */

 public function getKeys()  {
	   if (!isset($this->_keys)) {  
        $this->_keys = array(
         'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_STATUS',
		 'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_ZONE',
		 'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_MERCHANTID',
		 'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_AGGREEMENTID',
		 'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_USERAPIKEY',
		 'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_ORDERPREFIX',
         'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_PROCESSING_STATUS_ID',
         'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_ORDER_DEFAULT_STATUS_ID',
         'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_ORDER_CANCELED_STATUS_ID',
		 'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_SORT_ORDER',
		 'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_SUBSCRIPTION',
		 'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_AUTOCAPTURE',
		 'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_AUTOFEE');
		
        for ($i = 1; $i <= 5; $i++) {
            $this->_keys[] = 'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_GROUP' . $i;
            $this->_keys[] = 'ADDONS_PAYMENT_' . strtoupper($this->_code) . '_GROUP' . $i . '_FEE';
        }
	   }
        return $this->_keys;
    }

	



	
}
?>