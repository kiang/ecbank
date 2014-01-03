<?php
define('IN_DISCUZ', true);
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
$ok_url = $_POST['ok_url'];
//$ok_url = explode("?",$_POST['ok_url']);
$payment_type = $_POST['payment_type'];
$i_invoice = $_POST['i_invoice'];
$imer_id = $_POST['imer_id'];
$ecbank_gateway = 'https://ecbank.com.tw/web_service/alipay_goods_upload.php';
$post_str='enc_key='.$enc_key.
          '&mer_id='.$mer_id.
          '&type=upload_goods'.
          '&goods_id=999'.
          '&goods_title=交易金額'.
          '&goods_price='.round($amt).
          '&goods_href=www.ecbank.com.tw';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$ecbank_gateway);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$post_str);
$strAuth = curl_exec($ch);
if (curl_errno($ch)){
    $strAuth = false;
    }
curl_close($ch);
if($strAuth == 'state=NEW_SUCCESS'){
    echo '新增上架商品成功<br>';
}else if($strAuth == 'state=MODIFY_SUCCESS'){
    echo '修改上架商品成功<br>';
}else{
    echo '錯誤：'.$strAuth.'<br>';
}

?>

<form action="https://ecbank.com.tw/gateway.php" method="post">
<input type="hidden" name="mer_id" value="<?=$mer_id?>" />
<input type="hidden" name="payment_type" value='alipay' />
<input type="hidden" name="od_sob" value="<?=$od_sob?>" />
<input type="hidden" name="amt" value="<?=$amt?>" />
<input type="hidden" name="return_url" value="<?=$ok_url?>" />
<input type="hidden" name="ok_url" value="<?=$ok_url?>" />
<input type="hidden" name="goods_name[]" value="999" />
<input type="hidden" name="goods_amount[]" value="1" />
<?php if($i_invoice == 'yes1'){  ?>
    <input type="hidden" name="inv_active" value="1" />
    <input type="hidden" name="inv_mer_id" value="<?=$imer_id ?>" />
    <input type="hidden" name="inv_amt" value="<?php echo $amt ?>"/>
    <input type="hidden" name="inv_semail" value="test@test.net"/>
    <input type="hidden" name="inv_delay" value="0"/>
    <input type="hidden" name="prd_name[]" value="交易金額"/>
    <input type="hidden" name="prd_qry[]" value="1"/>
    <input type="hidden" name="prd_price[]" value="<?php echo $amt ?>"/>
<?php }  ?>
<script type="text/javascript" language="javascript">
	function do_submit() { document.forms[0].submit(); }
	do_submit();
</script>