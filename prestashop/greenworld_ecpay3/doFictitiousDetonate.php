<?php


    include_once(dirname(__FILE__).'/../../config/config.inc.php');
    include_once(dirname(__FILE__).'/../../init.php');
    include_once(dirname(__FILE__).'/greenworld_ecpay3.php');
    include_once(dirname(__FILE__).'/../../classes/OrderHistory.php');

                    
    $checkTemp = new greenworld_ecpay3();
    
    // 商店設定在ECBank管理後台的交易加密私鑰
    $key = $checkTemp->getEncryptionCode();
    $amount = $checkTemp->getAmount(Tools::getValue("od_sob"));
    // 組合字串
    
    $serial = trim($_REQUEST['process_date'].$_REQUEST['process_time'].$_REQUEST['gwsr']);
    
    // 回傳的交易驗證壓碼
    
    $amt = trim($_REQUEST['amount']);
   
    // ECBank 驗證Web Service網址
    $ws_url = 'https://ecpay.com.tw/g_recheck.php?key='.$_REQUEST["rech_key"].
              '&serial='.$serial.
              '&amt='.$amt ;
    // 取得驗證結果 (也可以使用curl)
   
    $tac_valid = file_get_contents($ws_url);
   
    if ($tac_valid == 'valid=1' ) {
        
        if($amount==$_REQUEST['amount'] && $_REQUEST['succ']=='1'){
        
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
