<?php
    include_once(dirname(__FILE__).'/../../config/config.inc.php');
    include_once(dirname(__FILE__).'/../../init.php');
    include_once(dirname(__FILE__).'/greenworld_ibon.php');
    include_once(dirname(__FILE__).'/../../classes/OrderHistory.php');

    
    $checkTemp = new greenworld_ibon();
    
    // 商店設定在ECBank管理後台的交易加密私鑰
    $key = $checkTemp->getEncryptionCode();
    $amount = $checkTemp->getAmount(Tools::getValue("od_sob"));
    
    $serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr']);
    // 回傳的交易驗證壓碼
    $tac = trim($_REQUEST['tac']);
   
    // ECBank 驗證Web Service網址
    $ws_url = 'https://ecbank.com.tw/web_service/get_outmac_valid.php?key='.$key.
              '&serial='.$serial.
              '&tac='.$tac;
    // 取得驗證結果 (也可以使用curl)
    $tac_valid = file_get_contents($ws_url);
    
    if($tac_valid == 'valid=1' && $amount==$_REQUEST['amt']){
        
        $id_order=$_REQUEST['od_sob'];
        $newOrderStatusId=2;
        $history = new OrderHistory();
        $history->id_order = (int)($id_order);
        $history->changeIdOrderState($newOrderStatusId,$id_order );
        $history->addWithemail();
        echo 'OK';
    }else{
        echo 'NOT OK';
    }

?>
