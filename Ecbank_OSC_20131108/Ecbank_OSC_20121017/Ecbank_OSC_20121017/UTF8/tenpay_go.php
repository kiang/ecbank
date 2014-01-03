<?php
//print_r($_GET);
echo '頁面轉跳中，請稍後';
    $p = '    
	<form name="ecpay" method="post" action="https://ecbank.com.tw/gateway.php">
	<input type="hidden" name="mer_id" value="'.$_GET['mer_id'].'">
    <input type="hidden" name="payment_type" value="tenpay">
    <input type="hidden" name="od_sob" value="'.$_GET['od_sob'].'">
    <input type="hidden" name="amt" value="'.round($_GET['amt']).'">
    <input type="hidden" name="return_url" value="'.$_GET['return_url'].'">
    <input type="hidden" name="ok_url" value="'.$_GET['ok_url'].'">';
	$p.='<input type="submit" value="開始進行資料轉送" style="display:none"> 
            </form>
            <script language=javascript>
            document.forms.ecpay.submit();
            </script>';
	echo $p;
?>