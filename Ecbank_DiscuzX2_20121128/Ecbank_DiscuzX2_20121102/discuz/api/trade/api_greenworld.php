<?php
define('IN_DISCUZ', true);
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$q = strrpos($_SERVER['REQUEST_URI'],'?');
$cur_url = substr($_SERVER['REQUEST_URI'],0,$q-8);
$query = DB::query("SELECT pluginid,available FROM ".DB::table('common_plugin')." WHERE identifier = 'greenworld' ");
while($setting = DB::fetch($query)) {
	$available = $setting['available'];
	$pluginid = $setting['pluginid'];
}
$vars = array();
$query = DB::query("SELECT * FROM ".DB::table('common_pluginvar')." WHERE pluginid=$pluginid ");
while($plugin = DB::fetch($query)) {
	$vars[$plugin['variable']] = $plugin['value'];
}
$mid = $vars['ecbank_id'];
$pkey = $vars['ecbank_pkey'];
$pid = $vars['ecpay_id'];
$pchk = $vars['ecpay_chk'];
$pid2 = $vars['ecpay_id2'];
$pchk2 = $vars['ecpay_chk2'];
$num3 = $vars['ecpay_3num'];
$num6 = $vars['ecpay_6num'];
$num12 = $vars['ecpay_12num'];
$imer_id = $vars['imer_id'];
$i_invoice = $vars['i_invoice']; //電子發票 
$discuz_uid = $_G['uid'];
$discuz_user = $_G['username'];
$email = $_G['member']['email'];
$ip = $_G['clientip'];
$order_id = $discuz_uid."_".time();
$prd_desc = $_G['setting']['bbname'];
$param_str = "?uid=".$discuz_uid."&buyer=".$discuz_user."&price=".$price."&amount=".$amount."&orderid=".$order_id."&submitdate=".time()."&email=".$email."&ip=".$ip;
//$param_str = "?uid=".$discuz_uid."&buyer=".$discuz_user."&price=".$price."&amount=".$amount."&orderid=".$order_id."&submitdate=".time()."&ip=".$ip;
$ecbank_reply = "http://".$_SERVER['HTTP_HOST'].$cur_url."api/trade/ecbank_reply.php".$param_str;
$ecpay_reply = "http://".$_SERVER['HTTP_HOST'].$cur_url."api/trade/ecpay_reply.php".$param_str;
?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/ext-core/3.1.0/ext-core.js"></script>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<style>
	body {
		font-size: 16px;
		color: green;
	}
</style>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <HEAD>
  <TITLE> 綠界金流 </TITLE>
  <META NAME="Generator" CONTENT="greenworld Inc.">
  <META NAME="Author" CONTENT="greenworld Inc. kenny">
  <META NAME="Keywords" CONTENT="greenworld Inc.">
  <META NAME="Description" CONTENT="greenworld Inc.">
 </HEAD>
 <BODY>
	<div id="header">
		<div class="wrap s_clear">
			<h2><center><a href="http://www.ecbank.com.tw/"><img src="static/image/common/greenworld_banner.png" alt="綠界ECBANK" border="0" /></a></center></h2>
			</div>
	</div>
	<CENTER>
	<div id="radio_options" style="background-color:white;width:500px;">
<?php
if(1 == $available){
	$options = explode(",",$vars['ec_option']);
	if(count($options) == 13){
		$total = 0;
?>
		您這次所支付的金額是：NT$ <?php echo $price ?><BR>
		<span id="ecpay_total"></span>
		<div>
			<span id="radio">
				<table>
				<tr>
					<td>
						請選擇您的繳款方式：
					</td>
				</tr>
				<tr>
					<td align="left" style="font-size: 16px;">
						<?php if($options[0] == 1){ echo '<input type="radio" name="g" value="1" checked onchange="green_change(this);"/>虛擬帳戶繳款<BR>';} ?>
						<?php if($options[1] == 1){ echo '<input type="radio" name="g" value="2" onchange="green_change(this);"/>webATM繳款<BR>';} ?>
						<?php if($options[2] == 1){ echo '<input type="radio" name="g" value="3" onchange="green_change(this);"/>超商條碼繳款<BR>';} ?>
						<?php if($options[3] == 1){ echo '<input type="radio" name="g" value="4" onchange="green_change(this);"/>超商代碼繳款(全家、萊爾富、OK)<BR>';} ?>
						<?php if($options[4] == 1){ echo '<input type="radio" name="g" value="5" onchange="green_change(this);"/>超商ibon繳款(統一超商 7-11)<BR>';} ?>
						<?php if($options[5] == 1){ echo '<input type="radio" name="g" value="6" onchange="green_change(this);"/>PayPal&nbsp;&nbsp;繳款<BR>';} ?>
						<?php if($options[6] == 1){ echo '<input type="radio" name="g" value="7" onchange="green_change(this);"/>ECPay線上刷卡繳款<BR>';} ?>
						<?php if($options[7] == 1){ echo '<input type="radio" name="g" value="8" onchange="green_change(this);"/>ECPay線上刷卡3期繳款<BR>';} ?>
						<?php if($options[8] == 1){ echo '<input type="radio" name="g" value="9" onchange="green_change(this);"/>ECPay線上刷卡6期繳款<BR>';} ?>
						<?php if($options[9] == 1){ echo '<input type="radio" name="g" value="10" onchange="green_change(this);"/>ECPay線上刷卡12期繳款<BR>';} ?>
                                                <?php if($options[10] == 1){ echo '<input type="radio" name="g" value="11" onchange="green_change(this);"/>支付寶Alipay繳款<BR>';} ?>
                                                <?php if($options[11] == 1){ echo '<input type="radio" name="g" value="12" onchange="green_change(this);"/>歐付寶Allpay繳款<BR>';} ?>
                                                <?php if($options[12] == 1){ echo '<input type="radio" name="g" value="13" onchange="green_change(this);"/>銀聯卡unionpay繳款<BR>';} ?>
					</td>
				</tr>
				</table>
			</span>
		</div>
		<BR>
		<div id="vacc">
			<form id="vacc_form" name="vacc_form" method="post" action="api/trade/ecbank_vacc.php" target="show_div">
				<input type='hidden' name='mer_id' value='<?php echo $mid?>'>
				<input type='hidden' name='payment_type' value='vacc'>
				<input type='hidden' name='setbank' value='ESUN'>
				<input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
				<input type='hidden' name='price' value='<?php echo $price?>'>
				<input type='hidden' name='ok_url' value='<?php echo $ecbank_reply?>'>
				<input type='hidden' name='enc_key' value='<?php echo $pkey ?>'>
				<input type='hidden' name='expire_day' value='3'>
                                <input type='hidden' name='i_invoice' value='<?php echo $i_invoice ?>'>
                                <input type='hidden' name='imer_id' value='<?php echo $imer_id ?>'>
				<span>綠界 ECBank 虛擬帳戶繳款</span>　<input type='button' value='我確定要繳款' onclick="hide_options('vacc');">
			</form>
		</div>
		<div id="webatm" style="display:none">
			<form id="webatm_form" name="webatm_form" method="post" action="api/trade/ecbank_realtime.php">
				<input type='hidden' name='mer_id' value='<?php echo $mid?>'>
				<input type='hidden' name='payment_type' value='web_atm'>
				<input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
				<input type='hidden' name='amt' value='<?php echo $price?>'>
				<input type='hidden' name='return_url' value='<?php echo $ecbank_reply?>'>
                                <input type='hidden' name='i_invoice' value='<?php echo $i_invoice ?>'>
                                <input type='hidden' name='imer_id' value='<?php echo $imer_id ?>'>
				<span>綠界 ECBank WebATM 線上繳款</span>　<input type='button' value='我確定要繳款' onclick="hide_options('webatm');">
			</form>
		</div>
		<div id="barcode" style="display:none">
			<form id="barcode_form" name="barcode_form" method="post" action="api/trade/ecbank_barcode.php" target="show_div">
				<input type='hidden' name='mer_id' value='<?php echo $mid?>'>
				<input type='hidden' name='payment_type' value='barcode'>
				<input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
				<input type='hidden' name='price' value='<?php echo $price?>'>
				<input type='hidden' name='ok_url' value='<?php echo $ecbank_reply?>'>
				<input type='hidden' name='expire_day' value='3'>
				<input type='hidden' name='enc_key' value='<?php echo $pkey ?>'>
                                <input type='hidden' name='i_invoice' value='<?php echo $i_invoice ?>'>
                                <input type='hidden' name='imer_id' value='<?php echo $imer_id ?>'>
				<span>綠界 ECBank 超商條碼繳款</span>　<input type='button' value='我確定要繳款' onclick="hide_options('barcode');">
			</form>
		</div>
		<div id="cvs" style="display:none">
			<form id="cvs_form" name="cvs_form" method="post" action="api/trade/ecbank_cvs.php" target="show_div">
				<input type='hidden' name='mer_id' value='<?php echo $mid?>'>
				<input type='hidden' name='payment_type' value='cvs'>
				<input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
				<input type='hidden' name='price' value='<?php echo $price?>'>
				<input type='hidden' name='ok_url' value='<?php echo $ecbank_reply?>'>
				<input type='hidden' name='enc_key' value='<?php echo $pkey ?>'>
				<input type='hidden' name='prd_desc' value='<?php echo $prd_desc?>'>
                                <input type='hidden' name='i_invoice' value='<?php echo $i_invoice ?>'>
                                <input type='hidden' name='imer_id' value='<?php echo $imer_id ?>'>
				<span>綠界 ECBank 超商代碼繳款(全家 family、萊爾富 hilife、OK OKGO)</span>　<input type='button' value='我確定要繳款' onclick="hide_options('cvs');">
			</form>
		</div>
		<div id="ibon" style="display:none">
			<form id="ibon_form" name="ibon_form" method="post" action="api/trade/ecbank_ibon.php" target="show_div">
				<input type='hidden' name='mer_id' value='<?php echo $mid?>'>
				<input type='hidden' name='payment_type' value='ibon'>
				<input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
				<input type='hidden' name='price' value='<?php echo $price?>'>
				<input type='hidden' name='ok_url' value='<?php echo $ecbank_reply?>'>
				<input type='hidden' name='enc_key' value='<?php echo $pkey ?>'>
				<input type='hidden' name='prd_desc' value='<?php echo $prd_desc?>'>
                                <input type='hidden' name='i_invoice' value='<?php echo $i_invoice ?>'>
                                <input type='hidden' name='imer_id' value='<?php echo $imer_id ?>'>
				<span>綠界 ECBank 超商代碼繳款(7-11 ibon)</span>　<input type='button' value='我確定要繳款' onclick="hide_options('ibon');">
			</form>
		</div>
		<div id="paypal" style="display:none">
			<form id="paypal_form" name="paypal_form" method="post" action="api/trade/ecbank_realtime.php">
				<input type='hidden' name='mer_id' value='<?php echo $mid?>'>
				<input type='hidden' name='payment_type' value='paypal'>
				<input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
				<input type='hidden' name='amt' value='<?php echo $price?>'>
				<input type='hidden' name='return_url' value='<?php echo $ecbank_reply?>'>
				<input type='hidden' name='enc_key' value='<?php echo $pkey ?>'>
				<input type='hidden' name='item_name' value='<?php echo $prd_desc?>'>
				<input type='hidden' name='cur_type' value='TWD'>
				<input type='hidden' name='cancel_url' value='http://<?php echo $_SERVER['HTTP_HOST'].$cur_url?>'>
                                <input type='hidden' name='i_invoice' value='<?php echo $i_invoice ?>'>
                                <input type='hidden' name='imer_id' value='<?php echo $imer_id ?>'>
				<span>綠界 ECBank PayPal線上繳款</span>　<input type='button' value='我確定要繳款' onclick="hide_options('paypal');">
			</form>
		</div>
		<div id="ecpay_0" style="display:none">
			<form id="ecpay_0_form" name="ecpay_0_form" method="post" action="https://ecpay.com.tw/form_Sc_to5.php" target="show_div">
				<input type='hidden' name='client' value='<?php echo $pid?>'>
				<input type='hidden' name='act' value='auth'>
				<input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
				<input type='hidden' name='amount' value='<?php echo $price?>'>
				<input type='hidden' name='roturl' value='<?php echo $ecpay_reply?>'>
                                <?php if($i_invoice == 'yes'){ ?>
                                    <input type='hidden' name='inv_active' value='1'>
                                    <input type='hidden' name='inv_mer_id' value='<?php echo $imer_id?>'>
                                    <input type='hidden' name='inv_amt' value='<?php echo $price?>'>
                                    <input type='hidden' name='inv_semail' value='<?php echo $email?>'>
                                    <input type='hidden' name='prd_name[]' value='交易金額'>
                                    <input type='hidden' name='prd_qry[]' value='1'>
                                    <input type='hidden' name='prd_price[]' value='<?php echo $price?>'>
                                <?php  }?>
				<span>綠界 ECPay信用卡線上繳款</span>　<input type='button' value='我確定要繳款' onclick="hide_options('ecpay_0');">
			</form>
		</div>
		<div id="ecpay_3" style="display:none">
			<form id="ecpay_3_form" name="ecpay_3_form" method="post" action="https://ecpay.com.tw/form_Sc_to5_fn.php" target="show_div">
				<input type='hidden' name='client' value='<?php echo $pid2?>'>
				<input type='hidden' name='act' value='auth'>
				<input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
				<input type='hidden' name='amount' value='<?php echo $total?>' class="amount">
				<input type='hidden' name='stage' value='3'>
				<input type='hidden' name='roturl' value='<?php echo $ecpay_reply?>'>
                                <?php if($i_invoice == 'yes'){ ?>
                                    <input type='hidden' name='inv_active' value='1'>
                                    <input type='hidden' name='inv_mer_id' value='<?php echo $imer_id?>'>
                                    <input type='hidden' name='inv_amt' value='<?php echo $price?>'>
                                    <input type='hidden' name='inv_semail' value='<?php echo $email?>'>
                                    <input type='hidden' name='prd_name[]' value='交易金額'>
                                    <input type='hidden' name='prd_qry[]' value='1'>
                                    <input type='hidden' name='prd_price[]' value='<?php echo $price?>'>
                                <?php  }?>
				<span>綠界 ECPay信用卡分3期線上繳款</span>　<input type='button' value='我確定要繳款' onclick="hide_options('ecpay_3');">
			</form>
		</div>
		<div id="ecpay_6" style="display:none">
			<form id="ecpay_6_form" name="ecpay_6_form" method="post" action="https://ecpay.com.tw/form_Sc_to5_fn.php" target="show_div">
				<input type='hidden' name='client' value='<?php echo $pid2?>'>
				<input type='hidden' name='act' value='auth'>
				<input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
				<input type='hidden' name='amount' value='<?php echo $total?>' class="amount">
				<input type='hidden' name='stage' value='6'>
				<input type='hidden' name='roturl' value='<?php echo $ecpay_reply?>'>
                                <?php if($i_invoice == 'yes'){ ?>
                                    <input type='hidden' name='inv_active' value='1'>
                                    <input type='hidden' name='inv_mer_id' value='<?php echo $imer_id?>'>
                                    <input type='hidden' name='inv_amt' value='<?php echo $price?>'>
                                    <input type='hidden' name='inv_semail' value='<?php echo $email?>'>
                                    <input type='hidden' name='prd_name[]' value='交易金額'>
                                    <input type='hidden' name='prd_qry[]' value='1'>
                                    <input type='hidden' name='prd_price[]' value='<?php echo $price?>'>
                                <?php  }?>
				<span>綠界 ECPay信用卡分6期線上繳款</span>　<input type='button' value='我確定要繳款' onclick="hide_options(ecpay_6);">
			</form>
		</div>
		<div id="ecpay_12" style="display:none">
			<form id="ecpay_12_form" name="ecpay_12_form" method="post" action="https://ecpay.com.tw/form_Sc_to5_fn.php" target="show_div">
				<input type='hidden' name='client' value='<?php echo $pid2?>'>
				<input type='hidden' name='act' value='auth'>
				<input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
				<input type='hidden' name='amount' value='<?php echo $total?>' class="amount">
				<input type='hidden' name='stage' value='12'>
				<input type='hidden' name='roturl' value='<?php echo $ecpay_reply?>'>
                                <?php if($i_invoice == 'yes'){ ?>
                                    <input type='hidden' name='inv_active' value='1'>
                                    <input type='hidden' name='inv_mer_id' value='<?php echo $imer_id?>'>
                                    <input type='hidden' name='inv_amt' value='<?php echo $price?>'>
                                    <input type='hidden' name='inv_semail' value='<?php echo $email?>'>
                                    <input type='hidden' name='prd_name[]' value='交易金額'>
                                    <input type='hidden' name='prd_qry[]' value='1'>
                                    <input type='hidden' name='prd_price[]' value='<?php echo $price?>'>
                                <?php  }?>
				<span>綠界 ECPay信用卡分12期線上繳款</span>　<input type='button' value='我確定要繳款' onclick="hide_all('ecpay_12');">
			</form>
		</div>
		<div id="alipay" style="display:none">
			<form id="alipay_form" name="alipay_form" method="post" action="api/trade/ecbank_alipay.php" target="show_div">
				<input type='hidden' name='mer_id' value='<?php echo $mid?>'>
                                <input type='hidden' name='payment_type' value='alipay'>
                                <input type='hidden' name='enc_key' value='<?php echo $pkey ?>'>
                                <input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
                                <input type='hidden' name='amt' value='<?php echo $price?>'>
                                <input type='hidden' name='ok_url' value='<?php echo $ecbank_reply?>'>
                                <input type='hidden' name='i_invoice' value='<?php echo $i_invoice ?>'>
                                <input type='hidden' name='imer_id' value='<?php echo $imer_id ?>'>
				<span>綠界 ECBank 支付寶繳款</span>　<input type='button' value='我確定要繳款' onclick="hide_options('alipay');">
			</form>
		</div>
		<div id="allpay" style="display:none">
			<form id="allpay_form" name="allpay_form" method="post" action="https://credit.allpay.com.tw/form_Sc_to5.php" target="show_div">
				<input type='hidden' name='client' value='<?php echo $pid?>'>
                                <input type='hidden' name='act' value='auth'>
                                <input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
                                <input type='hidden' name='email' value='<?php echo $email?>'>
                                <input type='hidden' name='amount' value='<?php echo $price?>'>
                                <input type='hidden' name='roturl' value='<?php echo $ecpay_reply?>'>
                                <?php if($i_invoice == 'yes'){ ?>
                                    <input type='hidden' name='inv_active' value='1'>
                                    <input type='hidden' name='inv_mer_id' value='<?php echo $imer_id?>'>
                                    <input type='hidden' name='inv_amt' value='<?php echo $price?>'>
                                    <input type='hidden' name='inv_semail' value='<?php echo $email?>'>
                                    <input type='hidden' name='prd_name[]' value='交易金額'>
                                    <input type='hidden' name='prd_qry[]' value='1'>
                                    <input type='hidden' name='prd_price[]' value='<?php echo $price?>'>
                                <?php  }?>
				<span>綠界 ECPay 歐付寶繳款</span>　<input type='button' value='我確定要繳款' onclick="hide_options('allpay');">
			</form>
		</div>
		<div id="unionpay" style="display:none">
			<form id="unionpay_form" name="unionpay_form" method="post" action="https://ecpay.com.tw/form_Sc_to5.php">
				<input type='hidden' name='client' value='<?php echo $pid?>'>
                                <input type='hidden' name='act' value='auth'>
                                <input type='hidden' name='od_sob' value='<?php echo $order_id?>'>
                                <input type='hidden' name='email' value='<?php echo $email?>'>
                                <input type='hidden' name='amount' value='<?php echo $price?>'>
                                <input type='hidden' name='roturl' value='<?php echo $ecpay_reply?>'>
                                <input type='hidden' name='bk_posturl' value='<?php echo $ecpay_reply?>'>
                                <input type='hidden' name='mallurl' value='<?php echo $ecpay_reply?>'>
                                <?php if($i_invoice == 'yes'){ ?>
                                    <input type='hidden' name='inv_active' value='1'>
                                    <input type='hidden' name='inv_mer_id' value='<?php echo $imer_id?>'>
                                    <input type='hidden' name='inv_amt' value='<?php echo $price?>'>
                                    <input type='hidden' name='inv_semail' value='<?php echo $email?>'>
                                    <input type='hidden' name='prd_name[]' value='交易金額'>
                                    <input type='hidden' name='prd_qry[]' value='1'>
                                    <input type='hidden' name='prd_price[]' value='<?php echo $price?>'>
                                <?php  }?>
				<span>綠界 ECPay unionpay銀聯卡 繳款</span>　<input type='button' value='我確定要繳款' onclick="hide_options('unionpay');">
			</form>
		</div>                   
                
	</div>
	<div id="index" style="background-color:white;width:500px;">
		<a href="index.php">回首頁</a>　　<img src="static/image/common/greenworld_logo.jpg" alt="綠界金流" border="0" />
	</div>
	</CENTER>
	<iframe id="show_div" name="show_div" width="100%" height="100%" scrolling="yes" frameborder="no"></iframe>
</BODY>
</HTML>
<SCRIPT type="text/javascript">
	google.load('ext-core', '3.1');
	green_change(Ext.get('radio').select('input:nth-child(1)', true).elements[0]);
	function green_change(t){
		hide_all();
		if(Ext.fly('err-div')){
			Ext.fly('err-div').remove();
		}
		var amt = <?php echo $price ?>,
			dom;
		switch(t.value){
			case "1":
				Ext.getDom('vacc').style.display = "inline";
				if(amt < 10 || amt > 2000000){
					Ext.getDom('vacc').style.display = "none";
					err('vacc','最小金額為 10元，最大金額依發卡銀行而定，但不能超過 2,000,000元');
				}
				break;
			case "2":
				Ext.getDom('webatm').style.display = "inline";
				if(amt < 10 || amt > 2000000){
					Ext.getDom('webatm').style.display = "none";
					err('webatm','每筆交易最小金額為 10元，最大金額 2,000,000元');
				}
				break;
			case "3":
				Ext.getDom('barcode').style.display = "inline";
				if(amt < 10 || amt > 2000000){
					Ext.getDom('barcode').style.display = "none";
					err('barcode','最小金額為 10元，最大金額依發卡銀行而定，但不能超過 2,000,000元');
				}
				break;
			case "4":
				Ext.getDom('cvs').style.display = "inline";
				if(amt < 30 || amt > 20000){
					Ext.getDom('cvs').style.display = "none";
					err('cvs','最小金額為 30元，繳費上限為 20,000元');
				}
				break;
			case "5":
				Ext.getDom('ibon').style.display = "inline";
				if(amt < 30 || amt > 20000){
					Ext.getDom('ibon').style.display = "none";
					err('ibon','最小金額為 30元，繳費上限為 20,000元');
				}
				break;
			case "6":
				Ext.getDom('paypal').style.display = "inline";
				if(amt < 1 || amt > 200000){
					Ext.getDom('paypal').style.display = "none";
					err('paypal','最小金額為 1元，繳費上限為 200,000元');
				}
				break;
			case "7":
				Ext.getDom('ecpay_0').style.display = "inline";
				if(amt < 1 || amt > 200000){
					Ext.getDom('ecpay_0').style.display = "none";
					err('ecpay_0','最小金額為 1元，繳費上限為 200,000元');
				}
				break;
			case "8":
				Ext.getDom('ecpay_3').style.display = "inline";
				if(amt < 1 || amt > 200000){
					Ext.getDom('ecpay_3').style.display = "none";
					err('ecpay_3','最小金額為 1元，繳費上限為 200,000元');
					return;
				}
				<?php $total = round($price*(1+$num3)); ?>
				Ext.fly('ecpay_total').update("您的分期利率為<?php echo ($num3*100).'% 共要支付NT$ '.$total.'元'?><br>");
				Ext.fly('ecpay_3_form').select('input.amount').elements[0].value = <?php echo $total ?>;
				break;
			case "9":
				Ext.getDom('ecpay_6').style.display = "inline";
				if(amt < 1 || amt > 200000){
					Ext.getDom('ecpay_6').style.display = "none";
					err('ecpay_6','最小金額為 1元，繳費上限為 200,000元');
					return;
				}
				<?php $total = round($price*(1+$num6)); ?>
				Ext.fly('ecpay_total').update("您的分期利率為<?php echo ($num6*100).'% 共要支付NT$ '.$total.'元'?><br>");
				Ext.fly('ecpay_6_form').select('input.amount').elements[0].value = <?php echo $total ?>;
				break;
			case "10":
				Ext.getDom('ecpay_12').style.display = "inline";
				if(amt < 1 || amt > 200000){
					Ext.getDom('ecpay_12').style.display = "none";
					err('ecpay_12','最小金額為 1元，繳費上限為 200,000元');
					return;
				}
				<?php $total = round($price*(1+$num12)); ?>
				Ext.fly('ecpay_total').update("您的分期利率為<?php echo ($num12*100).'% 共要支付NT$ '.$total.'元'?><br>");
				Ext.fly('ecpay_12_form').select('input.amount').elements[0].value = <?php echo $total ?>;
				break;
			case "11":
				Ext.getDom('alipay').style.display = "inline";
				if(amt < 1 || amt > 200000){
					Ext.getDom('alipay').style.display = "none";
					err('alipay','最小金額為 1元，繳費上限為 200,000元');
					return;
				}
				break;          
			case "12":
				Ext.getDom('allpay').style.display = "inline";
				if(amt < 1 || amt > 200000){
					Ext.getDom('allpay').style.display = "none";
					err('alipay','最小金額為 1元，繳費上限為 200,000元');
					return;
				}
				break;       
			case "13":
				Ext.getDom('unionpay').style.display = "inline";
				if(amt < 1 || amt > 200000){
					Ext.getDom('unionpay').style.display = "none";
					err('alipay','最小金額為 1元，繳費上限為 200,000元');
					return;
				}
				break;                                  
			default:
				break;
		}
	}
	function err(div,msg){
		Ext.DomHelper.append(Ext.fly('radio'), {
			id: 'err-div',
			cn: [{
				tag: 'span',
				style : 'background-color: white;color: red;',
				html: msg+"<br>請回上一頁更改"
			}]
		});
	}
	function hide_all(){
		Ext.getDom('vacc').style.display = "none";
		Ext.getDom('webatm').style.display = "none";
		Ext.getDom('barcode').style.display = "none";
		Ext.getDom('cvs').style.display = "none";
		Ext.getDom('ibon').style.display = "none";
		Ext.getDom('paypal').style.display = "none";
		Ext.getDom('ecpay_0').style.display = "none";
		Ext.getDom('ecpay_3').style.display = "none";
		Ext.getDom('ecpay_6').style.display = "none";
		Ext.getDom('ecpay_12').style.display = "none";
                Ext.getDom('alipay').style.display = "none";
                Ext.getDom('allpay').style.display = "none";
                Ext.getDom('unionpay').style.display = "none";
		Ext.get('ecpay_total').update('');
	}
	function hide_options(f){
		var msg = "很抱歉，管理者設定此插件有誤，請嘗試聯絡管理者-1-!!";
		switch (f){
		case 'vacc':
		case 'webatm':
		case 'barcode':
		case 'cvs':
		case 'ibon':
		case 'paypal':
                case 'alipay':    
<?php if($mid == "" && $pkey == ""){ ?>
			alert(msg);
			Ext.fly('radio_options').remove();
			return;
<?php } ?>
			break;
		case 'ecpay_0':
<?php if($pid == "" && $pchk == ""){ ?>
			alert(msg);
			Ext.fly('radio_options').remove();
			return;
<?php } ?>
			break;
		case 'ecpay_3':
		case 'ecpay_6':
		case 'ecpay_12':
<?php if($pid2 == "" && $pchk2 == ""){ ?>
			alert(msg);
			Ext.fly('radio_options').remove();
			return;
<?php } ?>
			break;
		default:
			break;
		}
		Ext.getDom(f+"_form").submit();
		Ext.fly('radio_options').remove();
	}
</SCRIPT>
<?php
	}else{
		echo "<div style='background-color:white;'>很抱歉，管理者設定此插件有誤，請嘗試聯絡管理者-2-!!</div>";
	}
}else{
	echo "<div style='background-color:white;'>很抱歉，管理者尚未啟動此插件，請嘗試聯絡管理者-3-!!</div>";
}
?>