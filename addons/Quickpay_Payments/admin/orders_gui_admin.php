<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// Only show quickpay transaction when we have an transacctionid from payment gateway
// Also check if we can access QuickPay API (only when Curl and Xml extentions are loaded)



if (isset($transaction) && get_quickpay_api_supported()) {
    ?>

    <table class="eight-columns"><tr>
        <td><p class="white"><b><?php echo $lC_Language->get('ENTRY_QUICKPAY_TRANSACTION'); ?></b></p></td>
        <td><?php
    $status = get_quickpay_status($oID);

	$splitpaynotice = "";
	$disablesplit = "";
	$images = "../addons/Quickpay_Payments/images/";
if ($status['splitpayment'] != '1'){
		
		$splitpaynotice = "<br><span style='clear:both;' ><font color='red' > NB: No split payment possible for paymenttype ".$status['cardtype'].".</font></span>";
		$disablesplit = " disabled";
	}

    if ($status && $status['qpstat'] === '000' && $status['msgtype'] == "status") {
        $statustext = array();
        $statustext[3] = $lC_Language->get('INFO_QUICKPAY_CAPTURED');
        $statustext[5] = $lC_Language->get('INFO_QUICKPAY_REVERSED');
        $statustext[7] = $lC_Language->get('INFO_QUICKPAY_CREDITED');
		 
	   //calculate splitpayments balance value
			
        $historylen= count($status['history'][0]['id']);
			
		if($historylen ==1 ){
			  
			$status['amount'] = $status["history"][0]["amount"]-($status['splitpayment'] == '1' ? $status['balance'] : 0);
		 
		   }
	   	
	   	$formatamount= explode(',',number_format($status['amount']/100,2,',','.'));
 //loaded Commerce reserves form[0] for other purposes...;
 echo '<form name="dummy" action="" ></form>';
	    switch ($status['state']) {
		
            case '1': // Authorized
			   
                echo '<form name="transaction_form" method="get" action="'.lc_href_link("index.php", lc_get_all_get_params() ).'" >';
                echo lc_draw_hidden_field('orders', $oInfo->get('oID')), lc_draw_hidden_field('qpaction', 'quickpay_capture'), lc_draw_hidden_field('action', 'save');
				echo lc_draw_hidden_field('oID', $oID), lc_draw_hidden_field('currency', $status['currency']);
                $amount_big = $formatamount[0];
                $amount_small = $formatamount[1];
               	echo '<div style="claer:right;" >';
				if($disablesplit){
			    echo $amount_big;
			    echo ',';
				echo $amount_small;	
				echo lc_draw_hidden_field('amount_big', $amount_big);
				echo lc_draw_hidden_field('amount_small', $amount_small);
				}else{
				echo lc_draw_input_field('amount_big', $amount_big, 'size="11" style="text-align:right" ', false, 'text', false);
			    echo ' , ';
                echo lc_draw_input_field('amount_small', $amount_small, 'size="3" style="float:left;" ', false, 'text', false) ;
					
				}
                echo ' ' . $status['currency'] . '&nbsp;</div>';
				echo '<a href="javascript:if (qp_check_capture(' . str_replace('.','',$amount_big) . ', ' . $amount_small . ')) document.transaction_form.submit();">' . lc_image($images.'icon_transaction_capture.png', $lC_Language->get('IMAGE_TRANSACTION_CAPTURE_INFO'),'','','style="float:left;margin-left:5px;"') . '</a>';
                echo '</form>';
                echo '<form method="get" name="transaction_decline_form" action="'.lc_href_link("index.php", lc_get_all_get_params()).'"  >';
                echo lc_draw_hidden_field('orders', $oInfo->get('oID')), lc_draw_hidden_field('qpaction', 'quickpay_reverse'), lc_draw_hidden_field('action', 'save');
				echo lc_draw_hidden_field('oID', $oID), lc_draw_hidden_field('currency', $status['currency']);
			   	echo lc_draw_hidden_field('amount_big', $amount_big);
				echo lc_draw_hidden_field('amount_small', $amount_small);
			    echo '<a href="javascript:if (qp_check_confirm(\'' . $lC_Language->get('CONFIRM_REVERSE') . '\')) document.transaction_decline_form.submit();">' . lc_image($images.'icon_transaction_reverse.png', $lC_Language->get('IMAGE_TRANSACTION_REVERSE_INFO'),'','','style="float:left;"') . '</a>';
                echo '</form>';
                $sevendayspast = date('Y-m-d', time() - (7 * 24 * 60 * 60));
                if ($sevendayspast == substr($status['time'], 0, 10)) {
                    echo lc_image($images.'icon_transaction_time_yellow.png', $lC_Language->get('IMAGE_TRANSACTION_TIME_INFO_YELLOW'),'','','style="float:left;"');
                } else if (strcmp($sevendayspast, substr($status['time'], 0, 10)) > 0) {
                    echo lc_image($images.'icon_transaction_time_red.png', $lC_Language->get('IMAGE_TRANSACTION_TIME_INFO_RED'),'','','style="float:left;"');
                } else {
                    echo lc_image($images.'icon_transaction_time_green.png', $lC_Language->get('IMAGE_TRANSACTION_TIME_INFO_GREEN'),'','','style="float:left;"');
                }
				echo $splitpaynotice;
                break;
            case '3': // Captured
                echo '<form  method="get" name="credit_form" action="'.lc_href_link("index.php", lc_get_all_get_params() ).'" >';
               	echo lc_draw_hidden_field('orders', $oInfo->get('oID')), lc_draw_hidden_field('qpaction', 'quickpay_credit'), lc_draw_hidden_field('action', 'save');
			    echo lc_draw_hidden_field('oID', $oID), lc_draw_hidden_field('currency', $status['currency']);
			
               $amount_big = $formatamount[0];
                $amount_small = $formatamount[1];
				echo '<div style="clear:right;" >';
                echo lc_draw_input_field('amount_big', $amount_big, 'size="11" style="text-align:right" '. $disablesplit, false, 'text', false);
                echo ' , ';
                echo lc_draw_input_field('amount_small', $amount_small, 'size="3" '. $disablesplit, false, 'text', false) . ' ' . $status['currency'] . '&nbsp;&nbsp;';
			    echo '</div>';
			    echo lc_image($images.'icon_transaction_capture_grey.png', $lC_Language->get('IMAGE_TRANSACTION_CAPTURE_INFO'),'','','style="float:left;margin-left:5px;"');
			    echo lc_image($images.'icon_transaction_reverse_grey.png', $lC_Language->get('IMAGE_TRANSACTION_REVERSE_INFO'),'','','style="float:left;"');
                echo '<a href="javascript:if (qp_check_confirm(\'' . $lC_Language->get('CONFIRM_CREDIT') . '\')) document.credit_form.submit();">' . lc_image($images.'icon_transaction_credit.png', $lC_Language->get('IMAGE_TRANSACTION_CREDIT_INFO'),'','','style="float:left;"') . '</a>';
                echo '</form>';
                echo lc_image($images.'icon_transaction_time_grey.png', '');
                echo '&nbsp;&nbsp;(' . $statustext[$status['state']].')';
                break;
            case '5': // Reversed
            case '7': // Credited
                 $amount_big = $formatamount[0];
                $amount_small = $formatamount[1];
				echo '<div style="clear:right;" >';
                echo lc_draw_input_field('amount_big', $amount_big, 'size="11" style="text-align:right" disabled', false, 'text', false);
                echo ' , ';
                echo lc_draw_input_field('amount_small', $amount_small, 'size="3" disabled', false, 'text', false) . ' ' . $status['currency'] . '&nbsp;&nbsp;';
                echo '</div>';
				echo lc_image($images.'icon_transaction_capture_grey.png', $lC_Language->get('IMAGE_TRANSACTION_CAPTURE_INFO'),'','','style="float:left;"');
                echo lc_image($images.'icon_transaction_reverse_grey.png', $lC_Language->get('IMAGE_TRANSACTION_REVERSE_INFO'),'','','style="float:left;"');
                echo lc_image($images.'icon_transaction_time_grey.png', '');
                echo '&nbsp;&nbsp;(' . $statustext[$status['state']] .')';
                break;
            default:
                echo '<font color="red">' . $status['qpstatmsg'] . '</font>';
                break;
        }
    } else {
        echo '<font color="red">' . $status['qpstatmsg'] . '</font>';
    }
    ?>
    <tr>
        <td ><b><?php echo $lC_Language->get('ENTRY_QUICKPAY_TRANSACTION_ID'); ?></b></td>
        <td ><?php echo $transaction; ?></b></td>
    </tr>

    <tr>
        <td ><b><?php echo $lC_Language->get('ENTRY_QUICKPAY_CARDTYPE'); ?></b></td>
        <td  ><?php echo $cardtype; ?></b></td>
    </tr>
       <tr>
        <td ><b><?php echo $lC_Language->get('ENTRY_QUICKPAY_CARDNUMBER'); ?></b></td>
        <td ><?php echo $cardnumber; ?></b></td>
    </tr>
       <tr>
        <td ><b><?php echo $lC_Language->get('ENTRY_QUICKPAY_CARDHASH'); ?></b></td>
        <td ><?php echo $cardhash; ?></b></td>
    </tr>
     <tr>
        <td >&nbsp;</td>
        <td >&nbsp;</td>
    </tr>
    </table>
    <?php
}
?>