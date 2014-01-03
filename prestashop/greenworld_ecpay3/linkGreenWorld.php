<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of linkGreenWorld
 *
 * @author 李泓承
 */

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../header.php');
include_once(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__).'/greenworld_ecpay3.php');
include_once(dirname(__FILE__).'/../../classes/OrderHistory.php');

session_start();

global $cookie;
$CheckPay=new greenworld_ecpay3();
$authorized = false;
foreach (Module::getPaymentModules() as $module)
    if ($module['name'] == $CheckPay->name){
            $authorized = true;
            break;
    }
if (!$authorized)
    die(Tools::displayError('This payment method is not available.'));


$customer = new Customer((int)$cart->id_customer);
$total = $cart->getOrderTotal(true, Cart::BOTH);
$inttotal=round($total);
$HomePage=Tools::getShopDomain(true, true).__PS_BASE_URI__;
$order = new Order((int)$CheckPay->currentOrder);
$PointToFinislURL='order-confirmation.php?key='.$customer->secure_key.'&id_cart='.(int)($cart->id).'&id_module='.(int)$CheckPay->id.'&id_order='.(int)$CheckPay->currentOrder;
$check=Tools::getValue("check");

$smarty->assign(array(
	'total' => $total ,
	'this_path' => "http://".$_SERVER["HTTP_HOST"].$CheckPay->path,
        'inttotal'=> $inttotal,
        'home'=> $HomePage
    ));
//
// SESSION["checkStep"] 用來防止，按上一頁，衍生出reload的問題。 
// 第一次進入開始並宣告 checkStep。
// 進入後呼叫模板 validationOrder.tpl，該模板是讓使用著知道自己將要繳多少錢， 並將checkStep設定值為字串 1
// validationOrder.tpl 當user按下確定鍵之後，會將也面在倒回此頁面
//  
// 第二次倒入頁面後，判斷checkStep 值為"1"，對Prestashop 後檯進行成立訂單。
// 訂單成立後，將相關資料傳送至EC_Bank 進行記錄
// 如有錯誤，將透過模滿  XXXXXXXXX.tpl顯示。
// 並將checkStep 設定為 2
//
//如果非以上checkStep 應該有的值出現或是根本不存在，則將呼叫錯誤面板。讓使用者知道他是錯誤操作。
//
if($check!="1"){
    session_unregister("checkStep");
    session_destroy();   
}

if(!session_is_registered("checkStep")){
    session_register("checkStep");
    
    if ($total==0){
        echo Module::display(_iMODULE_NAME_ECPAY_3,'ErrorStep.tpl');
        session_destroy(); 
    }else{
        $_SESSION["checkStep"]="1";
        echo Module::display(_iMODULE_NAME_ECPAY3_,'validationOrder.tpl');
    }

}else if($_SESSION["checkStep"]=="1" && $check=="1"){
    $CheckPay->validateOrder((int)$cart->id, 1, $inttotal, $CheckPay->displayName, NULL, array(), NULL, false, $customer->secure_key);
    $newOrderStatusId="1";
    $history = new OrderHistory();
    $history->id_order = (int)($CheckPay->currentOrder);
    $history->changeIdOrderState((int)$newOrderStatusId, (int)($CheckPay->currentOrder));
    $history->addWithemail();
    $URL=$CheckPay->getBaseURL();
    $PostData.="mer_id=".$CheckPay->getShopCode();
    $PostData.="&enc_key=".$CheckPay->getEncryptionCode();
    $PostData.="&payment_type=".$CheckPay->getPaymentType(); 
    $PostData.="&amt=".$inttotal;
    $PostData.="&od_sob=".$CheckPay->currentOrder;
    $PostData.="&return_url=".rawurlencode("http://".$_SERVER["HTTP_HOST"].$CheckPay->path."doFictitiousDetonate.php");
 

   
    
    
    // 建立CURL連線
    $ch = curl_init();
    // 設定擷取的URL網址
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_HEADER, false);
    //將curl_exec()獲取的訊息以文件流的形式返回，而不是直接輸出。
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    //設定CURLOPT_POST 為 1或true，表示要用POST方式傳遞
    curl_setopt($ch, CURLOPT_POST, 0); 
    //CURLOPT_POSTFIELDS 後面則是要傳接的POST資料。
    curl_setopt($ch, CURLOPT_POSTFIELDS, $PostData);
    // 執行
    $strAuth=curl_exec($ch);
    // 關閉CURL連線
    curl_close($ch);
    parse_str($strAuth, $res);


    if(!isset($res['error']) || $res['error'] != '0'){
     
        //echo '取號錯誤'.$res['error'];
        $smarty->assign(array(
            'Error' => $res['error']));
        session_unregister("checkStep");
        session_destroy(); 
        echo "ERROR:".$res['error'];
        //echo Module::display('greenworld','payErrorPage.tpl');
    }else {
        //echo '交易單號: '.$res['tsr'];
        //echo '銀行代碼: '.$res['bankcode'];
        //echo '銀行帳戶: '.$res['vaccno'];
        $finishURL=__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.(int)($cart->id).'&id_module='.(int)$CheckPay->id.'&id_order='.(int)$CheckPay->currentOrder;
        $finishURL.="&tsr=".$res['tsr'];
        $finishURL.="&mer_id=".$res['mer_id'];
        $finishURL.="&expire_date=".$res['expire_date'];

        session_unregister("checkStep");
        session_destroy();
        Tools::redirectLink($finishURL);

        //echo Module::display('greenworld','thankyouPage.tpl');
    }
    
}else{
    echo Module::display(_iMODULE_NAME_ECPAY3_,'ErrorStep.tpl');
    session_unregister("checkStep");
    session_destroy();
 
}
include(dirname(__FILE__).'/../../footer.php');

?>

