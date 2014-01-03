<?php
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
<form action="https://ecbank.com.tw/gateway.php" method="post">
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
<script type="text/javascript" language="javascript">
	function do_submit() { document.forms[0].submit(); }
	do_submit();
</script>