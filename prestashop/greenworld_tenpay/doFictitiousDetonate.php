<?php


include_once(dirname(__FILE__) . '/../../config/config.inc.php');
include_once(dirname(__FILE__) . '/../../init.php');
include_once(dirname(__FILE__) . '/greenworld_tenpay.php');
include_once(dirname(__FILE__) . '/../../classes/OrderHistory.php');


$checkTemp = new greenworld_tenpay();

// 商店設定在ECBank管理後台的交易加密私鑰
$key = $checkTemp->getEncryptionCode();
$amount = $checkTemp->getAmount(Tools::getValue("od_sob"));

// 組合字串

$serial = trim($_POST['proc_date'] . $_POST['proc_time'] . $_POST['tsr'] . $_POST['od_sob']. $_POST['amt'] );

$mac = trim($_POST['mac']);
// 回傳的交易驗證壓碼
// ECBank 驗證Web Service網址
// 取得驗證結果 (也可以使用curl)
$ws_url = 'https://ecbank.com.tw/web_service/get_outmac_valid_new.php';

// 串接驗證參數
$post_str = 'key=' . $key .
        '&serial=' . $serial .
        '&mac=' . $mac;

// 使用curl取得驗證結果
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $ws_url);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
$strAuth = curl_exec($ch);
if (curl_errno($ch)) {
    $strAuth = false;
}


if ($strAuth == 'valid=1' ) {
        
        if($amount==$_POST['amt'] && $_POST['succ']=='1'){
        
            $id_order=$_REQUEST['od_sob'];
            $newOrderStatusId=2;
            $history = new OrderHistory();
            $history->id_order = (int)($id_order);
            $history->changeIdOrderState($newOrderStatusId,$id_order);
            $history->addWithemail();
       
        echo 'OK';
        }else{
            echo '付款失敗';
        }
    }else{
        echo '交易失敗';
    }
?>
