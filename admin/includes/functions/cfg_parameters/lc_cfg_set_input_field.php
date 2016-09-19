<?php

/**

  @package    admin::functions

  @author     Loaded Commerce

  @copyright  Copyright 2003-2014 Loaded Commerce, LLC

  @copyright  Portions Copyright 2003 osCommerce

  @license    https://github.com/loadedcommerce/loaded7/blob/master/LICENSE.txt

  @version    $Id: lc_cfg_set_textarea_field.php v1.0 2013-08-08 datazen $

*/

function lc_cfg_set_input_field($default, $key = null) {



  $name = (!empty($key) ? 'configuration[' . $key . ']' : 'configuration_value');



  return '<input name="'.$name.'" id="'.$name.'" value="'.$default.'" class="input-unstyled" onfocus="this.select();" type="text">';

}

?>