<?php

include_once(dirname(__FILE__) . '/../../config/config.inc.php');
include_once(dirname(__FILE__) . '/../../init.php');
include_once(dirname(__FILE__) . '/greenworld_barcode.php');
include_once(dirname(__FILE__) . '/../../classes/OrderHistory.php');


$checkTemp = New greenworld_barcode();

// 商店設定在ECBank管理後台的交易加密私鑰
//$key = $checkTemp->getEncryptionCode();
// 組合字串
$key = $encryption_code = Configuration::get('gw_barocde_encryption');
$amount = $checkTemp->getAmount(Tools::getValue("od_sob"));

$serial = trim($_REQUEST['proc_date'] . $_REQUEST['proc_time'] . $_REQUEST['tsr']);
// 回傳的交易驗證壓碼
$tac = trim($_REQUEST['tac']);

// ECBank 驗證Web Service網址
$ws_url = 'https://ecbank.com.tw/web_service/get_outmac_valid.php?key=' . $key .
        '&serial=' . $serial .
        '&tac=' . $tac;
// 取得驗證結果 (也可以使用curl)
$tac_valid = file_get_contents($ws_url);

if ($tac_valid == 'valid=1') {

    if ($amount == $_REQUEST['amt'] && $_REQUEST['succ'] == '1') {

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
