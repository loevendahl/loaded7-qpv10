<?php

/**

  @package    admin::modules

  @author     Loaded Commerce

  @copyright  Copyright 2003-2014 Loaded Commerce, LLC

  @copyright  Portions Copyright 2003 osCommerce

  @license    https://github.com/loadedcommerce/loaded7/blob/master/LICENSE.txt

  @version    $Id: low_order_fee.php v1.0 2013-08-08 datazen $

 @copyright  Quickpay payment fee Copyright 2014 Kim Løvendahl

*/

class lC_OrderTotal_payment_fee extends lC_Modules_order_total_Admin {

  var $_title,

      $_code = 'payment_fee',

      $_author_name = 'Quickpay',

      $_author_www = 'http://www.quickpay.net',

      $_status = false,

      $_sort_order;

 var $_group = 'order_total';

  public function lC_OrderTotal_payment_fee() {

    global $lC_Language;

    $this->_title = $lC_Language->get('order_total_paymentfee_title');

    $this->_description = $lC_Language->get('order_total_paymentfee_description');

    $this->_status = (defined('MODULE_ORDER_TOTAL_PAYMENTFEE_STATUS') && (MODULE_ORDER_TOTAL_PAYMENTFEE_STATUS == 'true') ? true : false);

    $this->_sort_order = (defined('MODULE_ORDER_TOTAL_PAYMENTFEE_SORT_ORDER') ? MODULE_ORDER_TOTAL_PAYMENTFEE_SORT_ORDER : null);

  }



  public function isInstalled() {

    return (bool)defined('MODULE_ORDER_TOTAL_PAYMENTFEE_STATUS');

  }



  public function install() {
   global $lC_Database, $lC_Language;

    $lC_Database->simpleQuery("insert ignore into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Sub-Total', 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'true', 'Do you want to display the order sub-total cost?', '6', '1', 'lc_cfg_set_boolean_value(array(\'true\', \'false\'))', now())");
    $lC_Database->simpleQuery("insert ignore into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER', '100', 'Sort order of display.', '6', '2', now())");
	
    global $lC_Database;

    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display payment Fee', 'MODULE_ORDER_TOTAL_PAYMENTFEE_STATUS', 'true', 'Do you want to display and calculate the payment fee (if applied to a Quickpay payment)?', '6', '1', 'lc_cfg_set_boolean_value(array(\'true\', \'false\'))', now())");

    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_PAYMENTFEE_SORT_ORDER', '450', 'Sort order of display.', '6', '2', now())");
  



    $Qinstall = $lC_Database->query('insert into :table_templates_boxes (title, code, author_name, author_www, modules_group) values (:title, :code, :author_name, :author_www, :modules_group)');

    $Qinstall->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);

    $Qinstall->bindValue(':title', $this->_title);

    $Qinstall->bindValue(':code', $this->_code);

    $Qinstall->bindValue(':author_name', $this->_author_name);

    $Qinstall->bindValue(':author_www', $this->_author_www);

    $Qinstall->bindValue(':modules_group', $this->_group);

    $Qinstall->execute();



    foreach ($lC_Language->getAll() as $key => $value) {

      if (file_exists('../includes/languages/' . $key . '/modules/' . $this->_group . '/' . $this->_code . '.xml')) {

        foreach ($lC_Language->extractDefinitions($key . '/modules/' . $this->_group . '/' . $this->_code . '.xml') as $def) {

          $Qcheck = $lC_Database->query('select id from :table_languages_definitions where definition_key = :definition_key and content_group = :content_group and languages_id = :languages_id limit 1');

          $Qcheck->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);

          $Qcheck->bindValue(':definition_key', $def['key']);

          $Qcheck->bindValue(':content_group', $def['group']);

          $Qcheck->bindInt(':languages_id', $value['id']);

          $Qcheck->execute();



          if ($Qcheck->numberOfRows() === 1) {

            $Qdef = $lC_Database->query('update :table_languages_definitions set definition_value = :definition_value where definition_key = :definition_key and content_group = :content_group and languages_id = :languages_id');

          } else {

            $Qdef = $lC_Database->query('insert into :table_languages_definitions (languages_id, content_group, definition_key, definition_value) values (:languages_id, :content_group, :definition_key, :definition_value)');

          }

          $Qdef->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);

          $Qdef->bindInt(':languages_id', $value['id']);

          $Qdef->bindValue(':content_group', $def['group']);

          $Qdef->bindValue(':definition_key', $def['key']);

          $Qdef->bindValue(':definition_value', $def['value']);

          $Qdef->execute();

        }

      }

    }



    lC_Cache::clear('languages');

  }



 public function remove() {

    global $lC_Database;



    $lC_Database->simpleQuery("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->getKeys()) . "')");

  }



  public function getKeys() {

    if (!isset($this->_keys)) {

      $this->_keys = array('MODULE_ORDER_TOTAL_PAYMENTFEE_STATUS',

                           'MODULE_ORDER_TOTAL_PAYMENTFEE_SORT_ORDER');

    }



    return $this->_keys;

  }

}

?>