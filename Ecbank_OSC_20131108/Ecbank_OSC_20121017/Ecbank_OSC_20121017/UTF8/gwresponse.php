<?php
include( 'includes/application_top.php');
$payment_type = $_REQUEST['payment_type'];

switch($payment_type){
case 'ibon':
$payment ='ecbank_ibon';
	break;

case 'barcode':
$payment ='ecbank_barcode';
	break;

case 'vacc':
$payment ='ecbank_vacc';
	break;

case 'web_atm':
$payment ='ecbank_webatm';
	break;	

case 'cvs':
$payment ='ecbank_pincode';
	break;

case 'paypal':
$payment ='ecbank_paypal';
	break;	

case 'alipay':
$payment ='ecbank_alipay';
	break;	
	
case 'tenpay':
$payment ='ecbank_tenpay';
	break;	
}

$payment_path = 'includes/modules/payment/' . $payment .'.php';
//$payment_path = 'includes/modules/payment/ecbank_ibon.php';
include_once($payment_path);
    if (file_exists($payment_path)){
		$pay = new $payment();
		//$pay = new ecbank_ibon();
        $pay->response();            
	}
?>