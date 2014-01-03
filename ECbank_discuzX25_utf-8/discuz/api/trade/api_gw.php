<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$arr = C::t('common_plugin')->fetch_by_identifier('gw');
$available = $arr['available'];
$pluginid = $arr['pluginid'];
foreach(C::t('common_pluginvar')->fetch_all_by_pluginid($pluginid) as $plugin) {
	$vars[$plugin['variable']] = $plugin['value'];
	$title[$plugin['variable']] = $plugin['title'];
}
$arr = C::t('common_setting')->fetch('ec_ratio');
$ec_ratio = $arr['svalue'];
$amount = $_POST['addfundamount'];
$price = ceil($amount / $ec_ratio);
$od_sob = time();
$discuz_uid = $_G['uid'];
$discuz_user = $_G['username'];
$email = $_G['member']['email'];
$ip = $_G['clientip'];
$home = strrpos($_SERVER['REQUEST_URI'],'/home.php');
$cur_url = "http://".$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, $home);
include template ('../../template/default/common/header');
if(0 == $available) {
	echo "<br>";
	echo "<div style='color:green;'>很抱歉，管理者尚未啟動此插件，請嘗試聯絡管理者!!</div>";
	echo "<br>";
} else {
?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/ext-core/3.1.0/ext-core.js"></script>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<center>
	<br>
	<div>
		<form method="post" id="ecbank_form" name="ecbank_form" action="api/trade/notify_gw.php">
			<span style="font-size: 14pt;">您這次所支付的金額是：NT$ <span id='price'><?php echo $price ?></span></span><br><br>
			<table id="_contanier">
			<tr>
				<td>
					請選擇您的繳款方式：<br><br>
				</td>
			</tr>
			<tr>
				<td align='left" style="font-size: 16px;'>
					<?php if($vars['vacc'] == 1){ echo '<input type="radio" name="payment_type" value="vacc" checked />' . $title['vacc'] . '<br>';} ?> 
					<?php if($vars['web_atm'] == 1){ echo '<input type="radio" name="payment_type" value="web_atm"/>' . $title['web_atm'] . '<br>';} ?>
					<?php if($vars['cvs'] == 1){ echo '<input type="radio" name="payment_type" value="cvs"/>' . $title['cvs'] . '<br>';} ?>
					<?php if($vars['barcode'] == 1){ echo '<input type="radio" name="payment_type" value="barcode"/>' . $title['barcode'] . '<br>';} ?>
					<?php if($vars['ibon'] == 1){ echo '<input type="radio" name="payment_type" value="ibon"/>' . $title['ibon'] . '<br>';} ?>
					<?php if($vars['paypal'] == 1){ echo '<input type="radio" name="payment_type" value="paypal"/>' . $title['paypal'] . '<br>';} ?>
					<?php if($vars['alipay'] == 1){ echo '<input type="radio" name="payment_type" value="alipay"/>' . $title['alipay'] . '<br>';} ?>
					<?php if($vars['tenpay'] == 1){ echo '<input type="radio" name="payment_type" value="tenpay"/>' . $title['tenpay'] . '<br>';} ?>
					<?php if($vars['stage0'] == 1){ echo '<input type="radio" name="payment_type" value="stage0"/>' . $title['stage0'] . '<br>';} ?>
					<?php if($vars['stage3'] == 1){ echo '<input type="radio" name="payment_type" value="stage3"/>' . $title['stage3'] . '　利率' . $vars['stage3_rate'] . '% <br>';} ?>
					<?php if($vars['stage6'] == 1){ echo '<input type="radio" name="payment_type" value="stage6"/>' . $title['stage6'] . '　利率' . $vars['stage6_rate'] . '% <br>';} ?>
					<?php if($vars['stage12'] == 1){ echo '<input type="radio" name="payment_type" value="stage12"/>' . $title['stage12'] . '　利率' . $vars['stage12_rate'] . '% <br>';} ?>
					<?php if($vars['allpay'] == 1){ echo '<input type="radio" name="payment_type" value="allpay"/>' . $title['allpay'] . '<br>';} ?>
				</td>
			</tr>
			<tr>
				<td>
					<br>
					<input type='button' value='我確定要繳款' onclick="check_amt();">
				</td>
			</tr>
			</table>
			<input type="hidden" name="price" value="<?php echo $price?>">
			<input type="hidden" name="amount" value="<?php echo $amount?>">
			<input type="hidden" name="uid" value="<?php echo $discuz_uid?>">
			<input type="hidden" name="username" value="<?php echo $discuz_user?>">
			<input type="hidden" name="email" value="<?php echo $email?>">
			<input type="hidden" name="ip" value="<?php echo $ip?>">
			<input type="hidden" name="url" value="<?php echo $cur_url?>">
		</form>
		
	</div>
	<div id="response"></div>
</center>
<?php
}
include template ('../../template/default/common/footer');
?>
<script type="text/javascript">
function check_amt() {
	var payment_type = document.getElementsByName("payment_type");
	for (var i = 0; i < payment_type.length; i++) { 
		if(payment_type[i].checked) {
			if(!err(payment_type[i].value, '<?php echo $price?>')) {
				var form = document.getElementById('ecbank_form');
				var url = form.action;
				var params = "";
				switch(payment_type[i].value) {
					case 'vacc' :
					case 'barcode' :
					case 'cvs' :
					case 'ibon' :
						params = {
							'payment_type' : payment_type[i].value,
							'price' : '<?php echo $price?>',
							'amount' : '<?php echo $amount?>',
							'uid' : '<?php echo $discuz_uid?>',
							'username' : '<?php echo $discuz_user?>',
							'email' : '<?php echo $email?>',
							'ip' : '<?php echo $ip?>',
							'url' : '<?php echo $cur_url?>'
						};
						Ext.Ajax.request({
							url : url,
							method: 'POST',
							params : params,
							success: function (response) {
								Ext.fly('_contanier').remove();
								Ext.fly('response').dom.innerHTML = response.responseText;
							}
						});
						break;
					case 'web_atm' :
					case 'paypal' :
					case 'alipay' :
					case 'tenpay' :
					case 'stage0' : 
					case 'stage3' : 
					case 'stage6' : 
					case 'stage12' :
					case 'allpay' : 
						form.submit();
						break;
				}
			}
			return false;
		}	
	};
}
function err(type, amt) {
	var re = false;
	switch(type) {
		case 'vacc' :
		case 'web_atm' :
			if(amt < 10 || amt > 2000000){
				alert('最小金額為 10元，繳費上限 2,000,000元');
				re = true;
			}
			break;
		case 'barcode' :
			if(amt < 25 || amt > 2000000){
				alert('最小金額為 25元，繳費上限為 2,000,000元');
				re = true;
			}
			break;
		case 'cvs' :
		case 'ibon' :
			if(amt < 10 || amt > 20000){
				alert('最小金額為 30元，繳費上限為 20,000元');
				re = true;
			}
			break;
		case 'paypal' :
			if(amt < 10 || amt > 2000000){
				alert('最小金額為 10元，繳費上限 2,000,000元');
				re = true;
			}
			break;
		case 'stage0' :
		case 'stage3' :
		case 'stage6' :
		case 'stage12' :
		case 'allpay' :
			if(amt < 1 || amt > 200000){
				alert('最小金額為 1元，繳費上限為 200,000元');
				re = true;
			}
			break;
	}
	return re;
}
</script>