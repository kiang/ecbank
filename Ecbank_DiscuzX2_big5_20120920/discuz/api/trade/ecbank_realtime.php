<?php
define('IN_DISCUZ', true);  //webatm & paypal二合一
require_once '../../source/class/class_core.php';
require_once '../../config/config_global.php';
require_once '../../config/config_ucenter.php';
require_once '../../source/function/function_core.php';
$discuz = & discuz_core::instance();
$discuz->init_cron = false;
$discuz->init_session = false;
$discuz->init();
$mer_id = $_POST['mer_id'];
$enc_key = $_POST['enc_key'];
$od_sob = $_POST['od_sob'];
$amt = $_POST['amt'];
$return_url = $_POST['return_url'];
$payment_type = $_POST['payment_type'];
$item_name = "";
$cur_type = "";
$cancel_url = "";
$setbank = "";
$imer_id = $_POST['imer_id'];
$i_invoice = $_POST['i_invoice'];
switch($payment_type){
	case 'paypal':
		$item_name = $_POST['item_name'];
		$cur_type = 'TWD';
		$cancel_url = $_POST['cancel_url'];
		break;
	default: 
		$setbank = $_POST['setbank'];
		break;
}
include_once 'order_yes.php';
?>
<form action="https://ecbank.com.tw/gateway.php" method="post" >
<input type="hidden" name="mer_id" value="<?=$mer_id?>" />
<input type="hidden" name="enc_key" value="<?=$enc_key?>" />
<input type="hidden" name="payment_type" value="<?=$payment_type?>" />
<input type="hidden" name="od_sob" value="<?=$od_sob?>" />
<input type="hidden" name="amt" value="<?=$amt?>" />
<input type="hidden" name="return_url" value="<?=$return_url?>" />
<input type="hidden" name="setbank" value="<?=$setbank?>" />
<input type="hidden" name="item_name" value="<?=$item_name?>" />
<input type="hidden" name="cur_type" value="<?=$cur_type?>" />
<input type="hidden" name="cancel_url" value="<?=$cancel_url?>" />
<?php if($i_invoice == 'yes'){ ?>
<input type="hidden" name="inv_active" value="1"/>
<input type="hidden" name="inv_mer_id" value="<?php echo $imer_id ?>"/>
<input type="hidden" name="inv_amt" value="<?php echo $amt ?>"/>
<input type="hidden" name="inv_semail" value="test@test.net"/>
<input type="hidden" name="inv_delay" value="0"/>
<input type="hidden" name="prd_name[]" value="交易金額"/>
<input type="hidden" name="prd_qry[]" value="1"/>
<input type="hidden" name="prd_price[]" value="<?php echo $amt ?>"/>
<?php } ?>
<script type="text/javascript" language="javascript">
	function do_submit() { document.forms[0].submit(); }
	do_submit();
</script>