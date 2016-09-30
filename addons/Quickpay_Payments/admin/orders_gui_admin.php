<?php
//ini_set("display_errors","on");
//error_reporting(E_ALL);
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// Only show quickpay transaction when we have an transacctionid from payment gateway
// Also check if we can access QuickPay API (only when Curl and Xml extentions are loaded)



if ($api->init()) {

		
  
  try {
    $api->mode = (ADDONS_PAYMENT_QUICKPAY_PAYMENTS_SUBSCRIPTION == "-1" ? "payments?order_id=" : "subscriptions?order_id=");
  $statusinfo = $api->status(ADDONS_PAYMENT_QUICKPAY_PAYMENTS_ORDERPREFIX.$oInfo->get('oID')); 

  $ostatus['amount'] = $statusinfo[0]["operations"][0]["amount"];
  $ostatus['balance'] = $statusinfo[0]["balance"];
  $ostatus['currency'] = $statusinfo[0]["currency"];
  
  //get the latest operation
  $operations= array_reverse($statusinfo[0]["operations"]);
  
  $amount = $operations[0]["amount"];
  $ostatus['qpstat'] = $operations[0]["qp_status_code"];

  $ostatus['type'] = $operations[0]["type"];
  $resttocap = $ostatus['amount'] - $ostatus['balance'];
  $resttorefund = $statusinfo[0]["balance"];
  $allowcapture = ($operations[0]["pending"] ? false : true);
  $allowcancel = true;
  $testmode = $statusinfo[0]["test_mode"];
  $type = $statusinfo[0]["type"];
  $id = $statusinfo[0]["id"];
 
  $qp_aq_status_code = $statusinfo[0]["aq_status_code"];
  $qp_aq_status_msg = $statusinfo[0]["aq_status_msg"];
  $qp_cardtype = $statusinfo[0]["metadata"]["brand"];
  $qp_cardhash_nr = $statusinfo[0]["metadata"]["hash"];
  $qp_status_msg = $statusinfo[0]["operations"][0]["qp_status_msg"]."\n"."Cardhash: ".$qp_cardhash_nr."\n";
  $qp_cardnumber = "xxxx-xxxxxx-".$statusinfo[0]["metadata"]["last4"];

 
 $plink = $statusinfo[0]["link"]["url"];
 
  //allow split payments and split refunds
  if(($ostatus['type'] == "capture" ) ){
				
					$allowcancel = false;


	  }
    if(($ostatus['type'] == "refund" ) ){
			         
					$resttocap = 0;
	             

	  }
	  
	
  $ostatus['time'] = $operations[0]["created_at"];
  $ostatus['qpstatmsg'] = $operations[0]["qp_status_msg"];
   //reset mode
    $api->mode = (ADDONS_PAYMENT_QUICKPAY_PAYMENTS_SUBSCRIPTION == "-1" ? "payments/" : "subscriptions/");
} catch (Exception $e) {
  $error = $e->getCode(); // The code is the http status code
  $error .= $e->getMessage(); // The message is json

}
        $statustext = array();
        $statustext["capture"] = $lC_Language->get('INFO_QUICKPAY_CAPTURED');
        $statustext["cancel"] = $lC_Language->get('INFO_QUICKPAY_REVERSED');
        $statustext["refund"] = $lC_Language->get('INFO_QUICKPAY_CREDITED');
		$images = "../addons/Quickpay_Payments/images/";	

     ?>

    <table class="eight-columns"><tr>
        <td><p class="white"><b><?php echo $lC_Language->get('ENTRY_QUICKPAY_TRANSACTION'); ?></b></p></td>
        <td><?php
		if ($ostatus['qpstat'] == 20000 && $api->mode == "subscriptions/" && !$error) {
	echo $lC_Language->get(SUBSCRIPTION_ADMIN);
}
    if ($ostatus['qpstat'] == 20000 && $api->mode == "payments/") {
 echo '<form name="dummy" action="" ></form>';
	 
	  $formatamount= explode(',',number_format($amount/100,2,',',''));
	    $amount_big = $formatamount[0];
        $amount_small = $formatamount[1];
	  
	  
	    switch ($ostatus['type']) {
		
            case 'authorize': // Authorized
			
			   
                echo '<form name="transaction_form" method="get" action="'.lc_href_link("index.php", lc_get_all_get_params() ).'" >';
                echo lc_draw_hidden_field('orders', $oInfo->get('oID')), lc_draw_hidden_field('qpaction', 'quickpay_capture'), lc_draw_hidden_field('action', 'save');
				echo lc_draw_hidden_field('oID', $oID), lc_draw_hidden_field('currency', $status['currency']);
                $amount_big = $formatamount[0];
                $amount_small = $formatamount[1];
               	echo '<div style="float:left;">';
				if($disablesplit){
			    echo $amount_big;
			    echo ',';
				echo $amount_small;	
				echo lc_draw_hidden_field('amount_big', $amount_big);
				echo lc_draw_hidden_field('amount_small', $amount_small);
				}else{
				echo lc_draw_input_field('amount_big', $amount_big, 'size="11" style="text-align:right" ', false, 'text', false);
			    echo ' , ';
                echo lc_draw_input_field('amount_small', $amount_small, 'size="3"  ', false, 'text', false) ;
					
				}
                echo ' ' . $ostatus['currency'] . '&nbsp;';
				echo '</div>';
		if($allowcapture){		
				echo '<a href="javascript:if (qp_check_capture(' . str_replace('.','',$amount_big) . ', ' . $amount_small . ')) document.transaction_form.submit();">' . lc_image($images.'icon_transaction_capture.png', $lC_Language->get('IMAGE_TRANSACTION_CAPTURE_INFO'),'','','style="float:left;margin-left:5px;"') . '</a>';
         
		    }else{
	echo $lC_Language->get(PENDING_STATUS);
	
}   
			   
			     
			    echo '</form>';
                echo '<form method="get" name="transaction_decline_form" action="'.lc_href_link("index.php", lc_get_all_get_params()).'"  >';
                echo lc_draw_hidden_field('orders', $oInfo->get('oID')), lc_draw_hidden_field('qpaction', 'quickpay_reverse'), lc_draw_hidden_field('action', 'save');
				echo lc_draw_hidden_field('oID', $oID), lc_draw_hidden_field('currency', $status['currency']);
			   	echo lc_draw_hidden_field('amount_big', $amount_big);
				echo lc_draw_hidden_field('amount_small', $amount_small);
				if($allowcancel){
			    echo '<a href="javascript:if (qp_check_confirm(\'' . $lC_Language->get('CONFIRM_REVERSE') . '\')) document.transaction_decline_form.submit();">' . lc_image($images.'icon_transaction_reverse.png', $lC_Language->get('IMAGE_TRANSACTION_REVERSE_INFO'),'','','style="float:left;"') . '</a>';
				}
                echo '</form>';
               /* $sevendayspast = date('Y-m-d', time() - (7 * 24 * 60 * 60));
                if ($sevendayspast == substr($status['time'], 0, 10)) {
                    echo lc_image($images.'icon_transaction_time_yellow.png', $lC_Language->get('IMAGE_TRANSACTION_TIME_INFO_YELLOW'),'','','style="float:left;"');
                } else if (strcmp($sevendayspast, substr($status['time'], 0, 10)) > 0) {
                    echo lc_image($images.'icon_transaction_time_red.png', $lC_Language->get('IMAGE_TRANSACTION_TIME_INFO_RED'),'','','style="float:left;"');
                } else {
                    echo lc_image($images.'icon_transaction_time_green.png', $lC_Language->get('IMAGE_TRANSACTION_TIME_INFO_GREEN'),'','','style="float:left;"');
                }
				*/
				//echo $splitpaynotice;
                break;
             case 'capture': // Captured or refunded
			case 'refund':
				if($resttocap > 0 ){
						$formatamount= explode(',',number_format($resttocap/100,2,',',''));
	    $amount_big = $formatamount[0];
        $amount_small = $formatamount[1];
		echo "<br><b>".$lC_Language->get(IMAGE_TRANSACTION_CAPTURE_INFO)."</b><br>";
                echo '<form  method="get" name="transaction_form" action="'.lc_href_link("index.php", lc_get_all_get_params() ).'" >';
               	echo lc_draw_hidden_field('orders', $oInfo->get('oID')), lc_draw_hidden_field('qpaction', 'quickpay_capture'), lc_draw_hidden_field('action', 'save');
			
			    echo lc_draw_hidden_field('oID', $oID), lc_draw_hidden_field('currency', $ostatus['currency']);
			
				echo '<div style="float:left;" >';
                echo lc_draw_input_field('amount_big', $amount_big, 'size="11" style="text-align:right" ', false, 'text', false);
                echo ' , ';
                echo lc_draw_input_field('amount_small', $amount_small, 'size="3" ', false, 'text', false) . ' ' . $status['currency'] . '&nbsp;&nbsp;';
			    echo '</div>';
						if($allowcapture){		
				echo '<a href="javascript:if (qp_check_capture(' . str_replace('.','',$amount_big) . ', ' . $amount_small . ')) document.transaction_form.submit();">' . lc_image($images.'icon_transaction_capture.png', $lC_Language->get('IMAGE_TRANSACTION_CAPTURE_INFO'),'','','style="float:left;margin-left:5px;"') . '</a>';
         
		    }else{
	echo PENDING_STATUS;
	
} 
				
			   // echo lc_image($images.'icon_transaction_capture_grey.png', $lC_Language->get('IMAGE_TRANSACTION_CAPTURE_INFO'),'','','style="float:left;margin-left:5px;"');
			  /* 
               if($allowcancel){
			    echo '<a href="javascript:if (qp_check_confirm(\'' . $lC_Language->get('CONFIRM_CREDIT') . '\')) document.credit_form.submit();">' . lc_image($images.'icon_transaction_credit.png', $lC_Language->get('IMAGE_TRANSACTION_CREDIT_INFO'),'','','style="float:left;"') . '</a>';
               	}else{
					     echo lc_image($images.'icon_transaction_reverse_grey.png', $lC_Language->get('IMAGE_TRANSACTION_REVERSE_INFO'),'','','style="float:left;"');
				}
				*/
			    echo '</form>';
               // echo lc_image($images.'icon_transaction_time_grey.png', '');
               // echo '&nbsp;&nbsp;(' . $statustext[$status['state']].')';
   
                echo "<br><br>";
				}
						$formatamount= explode(',',number_format($resttorefund/100,2,',',''));
	    $amount_big = $formatamount[0];
        $amount_small = $formatamount[1];	
			if($resttorefund > 0){
	
			echo "<b>".$lC_Language->get(IMAGE_TRANSACTION_CREDIT_INFO)."</b><br>";	
                              echo '<form  method="get" name="transaction_refundform" action="'.lc_href_link("index.php", lc_get_all_get_params() ).'" >';
			   
				echo lc_draw_hidden_field('orders', $oInfo->get('oID')), lc_draw_hidden_field('qpaction', 'quickpay_credit'), lc_draw_hidden_field('action', 'save');
               
			    echo lc_draw_hidden_field('oID', $oID), lc_draw_hidden_field('currency', $ostatus['currency']);
             
                echo lc_draw_input_field('amount_big', str_replace('.','',$amount_big), 'size="11" style="text-align:right" ', false, 'text', false);
                echo ' , ';
                echo lc_draw_input_field('amount_small', $amount_small, 'size="3" ', false, 'text', false) . ' ' . $ostatus['currency'] . ' ';
               
			   
			    echo lc_image($images. 'icon_transaction_capture_grey.png', $lC_Language->get(IMAGE_TRANSACTION_CAPTURE_INFO));
              
			  
			    lc_image($images . 'icon_transaction_reverse_grey.gif', $lC_Language->get(IMAGE_TRANSACTION_REVERSE_INFO));
                echo '<a href="javascript:if (qp_check_confirm(\'' . $lC_Language->get('CONFIRM_CREDIT') . '\')) document.transaction_refundform.submit();">' . lc_image($images. 'icon_transaction_credit.png', $lC_Language->get(IMAGE_TRANSACTION_CREDIT_INFO)) . '</a>';
                echo '</form>';
               // echo lc_image($images . 'icon_transaction_time_grey.gif', '');
              
			}else{
	
				
				        echo lc_draw_input_field('amount_big', str_replace('.','',$amount_big), 'size="11" style="text-align:right" disabled', false, 'text', false);
                echo ' , ';
                echo lc_draw_input_field('amount_small', $amount_small, 'size="3" disabled', false, 'text', false) . ' ' . $ostatus['currency'] . ' ';
				// echo ' (' . $statustext[$ostatus['type']].')';
			}
	echo '&nbsp;&nbsp;(' . $statustext[$ostatus['type']].')';
	            break;
          case 'cancel': // Reversed
		  
		  
                 $amount_big = $formatamount[0];
                $amount_small = $formatamount[1];
				echo '<div style="float:left;" >';
                echo lc_draw_input_field('amount_big', $amount_big, 'size="11" style="text-align:right" disabled', false, 'text', false);
                echo ' , ';
                echo lc_draw_input_field('amount_small', $amount_small, 'size="3" disabled', false, 'text', false) . ' ' . $status['currency'] . '&nbsp;&nbsp;';
                echo '</div>';
				echo lc_image($images.'icon_transaction_capture_grey.png', $lC_Language->get('IMAGE_TRANSACTION_CAPTURE_INFO'),'','','style="float:left;"');
                echo lc_image($images.'icon_transaction_reverse_grey.png', $lC_Language->get('IMAGE_TRANSACTION_REVERSE_INFO'),'','','style="float:left;"');
                //echo lc_image($images.'icon_transaction_time_grey.png', '');
                //echo '&nbsp;&nbsp;(' . $statustext[$status['state']] .')';
                break;
            default:
                echo '<font color="red">' .$statustext[$ostatus['type']].' ('. $ostatus['qpstatmsg'] . ')</font>';
                break;
        }
    }
	
	}//end payment tools
	
if($ostatus['type']){
    ?>
    <tr>
        <td ><b><?php echo $lC_Language->get('ENTRY_QUICKPAY_TRANSACTION_ID'); ?></b></td>
        <td ><?php echo $id.($testmode== true ? '<font color="red"> TEST MODE</font>' : ''); ?></b></td>
    </tr>

    <tr>
        <td ><b><?php echo $lC_Language->get('ENTRY_QUICKPAY_CARDTYPE'); ?></b></td>
        <td  ><?php echo $qp_cardtype; ?></b></td>
    </tr>
       <tr>
        <td ><b><?php echo $lC_Language->get('ENTRY_QUICKPAY_CARDNUMBER'); ?></b></td>
        <td ><?php echo $qp_cardnumber; ?></b></td>
    </tr>
       <tr>
        <td ><b><?php echo $lC_Language->get('ENTRY_QUICKPAY_CARDHASH'); ?></b></td>
        <td ><?php echo $qp_cardhash_nr; ?></b></td>
    </tr>
    <?php if($plink){?>
           <tr>
        <td class="main"><b><?php echo "Payment link:"; ?></b></td>
        <td class="main"><?php echo "<a target='_blank' href='".$plink."' >".$plink."</a>"; ?></td>
    </tr>
    <?php } ?>
   <tr>
        <td class="main" valign="top"><b><?php echo "Gateway status:"; ?></b></td>
        <td class="main"><?php echo $api->log_operations($operations, $ostatus['currency']); ?></td>
    </tr>
  
    <?php
}
?>
   <tr>
        <td >&nbsp;</td>
        <td >&nbsp;</td>
    </tr>
    </table>