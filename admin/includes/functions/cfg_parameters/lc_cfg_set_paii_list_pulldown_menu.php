<?php
/**
  @package    admin::functions
  @author     Loaded Commerce
  @copyright  Copyright 2003-2014 Loaded Commerce, LLC
  @copyright  Portions Copyright 2003 osCommerce
  @license    https://github.com/loadedcommerce/loaded7/blob/master/LICENSE.txt
  @version    $Id: lc_cfg_set_output_compression_pulldown_menu.php v1.0 2013-08-08 datazen $
*/
function lc_cfg_set_paii_list_pulldown_menu() {
  global $lC_Database;


$paiioptions = array(
							' '	   => ' ',
							'SC00' => 'Ringetoner, baggrundsbilleder m.v.',
							'SC01' => 'Videoklip og	tv',
							'SC02' => 'Erotik og voksenindhold',
							'SC03' => 'Musik, sange og albums',
							'SC04' => 'Lydbøger	og podcasts',
							'SC05' => 'Mobil spil',
							'SC06' => 'Chat	og dating',
							'SC07' => 'Afstemning og konkurrencer',
							'SC08' => 'Mobil betaling',
							'SC09' => 'Nyheder og information',
							'SC10' => 'Donationer',
							'SC11' => 'Telemetri og service sms',
							'SC12' => 'Diverse',
							'SC13' => 'Kiosker & små købmænd',
							'SC14' => 'Dagligvare, Fødevarer & non-food',
							'SC15' => 'Vin & tobak',
							'SC16' => 'Apoteker	og medikamenter',
							'SC17' => 'Tøj, sko og accessories',
							'SC18' => 'Hus, Have, Bolig og indretning',
							'SC19' => 'Bøger, papirvare	og kontorartikler',
							'SC20' => 'Elektronik, Computer & software',
							'SC21' => 'Øvrige forbrugsgoder',
							'SC22' => 'Hotel, ophold, restaurant, cafe & værtshuse,<br> Kantiner og catering',
							'SC24' => 'Kommunikation og konnektivitet, ikke via telefonregning',
							'SC25' => 'Kollektiv trafik',
							'SC26' => 'Individuel trafik (Taxikørsel)',
							'SC27' => 'Rejse (lufttrafik, rejser, rejser med ophold)',
							'SC28' => 'Kommunikation og konnektivitet, via telefonregning',
							'SC29' => 'Serviceydelser',
							'SC30' => 'Forlystelser og underholdning, ikke digital',
							'SC31' => 'Lotteri- og anden spillevirksomhed',
							'SC32' => 'Interesse- og hobby <br>(Motion, Sport, udendørsaktivitet,<br> foreninger, organisation)',
							'SC33' => 'Personlig pleje (Frisør, skønhed, sol og helse)',
							'SC34' => 'Erotik og voksenprodukter(fysiske produkter)',
						);
	$options = '';	
	$paiique = $lC_Database->query("select configuration_value  from :table_configuration WHERE configuration_key  =  'ADDONS_PAYMENT_QUICKPAY_PAII_CAT' ");
    $paiique->bindTable(':table_configuration', TABLE_CONFIGURATION);
	$paiique->execute();
    
	$selectedcat = $paiique->value('configuration_value');

	$option_array=array();	

foreach($paiioptions as $arrid => $val){
	 $selected ='';
	  if ($selectedcat == $arrid) {
        $selected = ' selected="selected"';
      }							
	 $options .= '<option value="'.$arrid.'" '.$selected.' >'.$val.'</option>';
    } 

    return '<select id="configuration[ADDONS_PAYMENT_QUICKPAY_PAYMENTS_PAII_CAT]" class="input with-small-padding" name="configuration[ADDONS_PAYMENT_QUICKPAY_PAYMENTS_PAII_CAT]" >
	'.$options.'	
	</select>';
	
}
?>