<?php

include_once(dirname(__FILE__) . '/../../config/config.inc.php');
include_once(dirname(__FILE__) . '/../../init.php');
include_once(dirname(__FILE__) . '/greenworld_allpay.php');
include_once(dirname(__FILE__) . '/../../classes/OrderHistory.php');


$checkTemp = new greenworld_allpay();

// 商店設定在ECBank管理後台的交易加密私鑰
$key = $checkTemp->getEncryptionCode();
$amount = $checkTemp->getAmount(Tools::getValue("od_sob"));

// 組合字串

$serial = trim($_POST['process_date'] . $_POST['process_time'] . $_POST['gwsr']);

// 回傳的交易驗證壓碼

$amt = trim($_POST['amount']);

// ECBank 驗證Web Service網址
// 取得驗證結果 (也可以使用curl)
$ecbank_gateway = 'https://credit.allpay.com.tw/g_recheck.php';
// 組合字串
//echo "<hr>".$checkcode."<hr>";

$post_parm = 'key=' . $_POST["rech_key"] . '&serial=' . $serial . '&amt=' . $amt;

//echo "<hr>".$post_parm; 

/* $fp = fopen('data.txt', 'w');
  fwrite($fp, $post_parm);

  fclose($fp); */

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $ecbank_gateway);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_parm);
$strAuth = curl_exec($ch);
if (curl_errno($ch))
    $strAuth = false;
curl_close($ch);



if ($strAuth == 'valid=1') {

    if ($amount == $_POST['amount'] && $_POST['succ'] == '1') {

        $id_order = $_REQUEST['od_sob'];
        $newOrderStatusId = 2;
        $history = new OrderHistory();
        $history->id_order = (int) ($id_order);
        $history->changeIdOrderState($newOrderStatusId, $id_order);
        $history->addWithemail();

        echo 'OK';
    } else {
        echo '付款失敗';
    }
} else {
    echo '交易失敗';
}
?>
