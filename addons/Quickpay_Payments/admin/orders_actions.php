<?php
$qpaction= $_GET['qpaction'];
if (isset($qpaction)) {
    switch ($qpaction) {

        case 'quickpay_reverse':

            $result = get_quickpay_reverse($oInfo->get('oID'));

            break;
        case 'quickpay_capture':
            if (!isset($_GET['amount_big']) || $_GET['amount_big'] == '' || $_GET['amount_big'] == 0) {
               lc_redirect("index.php?orders=".$oInfo->get('oID')."&amp;action=save");
            }
            // convert amount from local currency-format to required quickpay format 
            $amount = $_GET['amount_big'] . sprintf('%02d', $_GET['amount_small']);
            $result = get_quickpay_capture($oInfo->get('oID'), $amount);

            break;
        case 'quickpay_credit':
           
            // convert amount from local currency-format to required quickpay format 
            $amount = $_GET['amount_big'] . sprintf('%02d', $_GET['amount_small']);
            $result = get_quickpay_credit($oInfo->get('oID'), $amount);

            break;

    }
}
?>