<?php
    include_once(dirname(__FILE__).'/../../config/config.inc.php');
    include_once(dirname(__FILE__).'/../../init.php');
    include_once(dirname(__FILE__).'/greenworld_vacc.php');
    include_once(dirname(__FILE__).'/../../classes/OrderHistory.php');
/*
$smarty->assign(array(
    'ECBANK_mer_id' =>  Tools::getValue("mer_id"),          //ECBank商店代號
    'payment_type'  =>  Tools::getValue("payment_type"),    //付款方式
    'ECBANK_tsr'    =>  Tools::getValue("tsr"),             //交易單號是唯一的編號
    'od_sob'        =>  Tools::getValue("od_sob"),          //回傳取號時傳入的自訂交易編號
    'amt'           =>  Tools::getValue("amt"),             //交易金額
    'expire_date'   =>  Tools::getValue("expire_date"),     //繳費截止日期
    'succ'          =>  Tools::getValue("succ"),            //交易狀態
    'payer_bank'    =>  Tools::getValue("payer_bank"),      //付款人銀行代碼
    'payer_acc'     =>  Tools::getValue("payer_acc"),       //付款人銀行帳號後5碼
    'proc_date'     =>  Tools::getValue("proc_date"),       //處理日期
    'proc_time'     =>  Tools::getValue("proc_time"),       //處理時間
    'tac'           =>  Tools::getValue("tac"),             //交易驗證壓碼
      ));
*/
    //echo"ddd";
    
    $checkTemp=New greenworld_vacc();
    
    // 商店設定在ECBank管理後台的交易加密私鑰
    $key = $checkTemp->getEncryptionCode();
    // 組合字串
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
    
    if ($tac_valid == 'valid=1' ) {
        
        if($amount==$_REQUEST['amt'] && $_REQUEST['succ']=='1'){
        
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
