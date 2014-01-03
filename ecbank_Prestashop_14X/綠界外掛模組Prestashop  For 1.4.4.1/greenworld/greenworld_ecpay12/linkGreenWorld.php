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
include_once(dirname(__FILE__).'/greenworld_ecpay12.php');
include_once(dirname(__FILE__).'/../../classes/OrderHistory.php');

session_start();

global $cookie;
$CheckPay=new greenworld_ecpay12();
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
$InstallmentTotal=(int)($inttotal*( 1 +  ($CheckPay->get12Percentage()/100) ) );
$HomePage=Tools::getShopDomain(true, true).__PS_BASE_URI__;
$order = new Order((int)$CheckPay->currentOrder);
$PointToFinislURL='order-confirmation.php?key='.$customer->secure_key.'&id_cart='.(int)($cart->id).'&id_module='.(int)$CheckPay->id.'&id_order='.(int)$CheckPay->currentOrder;
$check=Tools::getValue("check");

$smarty->assign(array(
	'total'                 =>  $total ,
	'this_path'             =>  "http://".$_SERVER["HTTP_HOST"].$CheckPay->path,
        'inttotal'              =>  $inttotal,
        'InstallmentTotal'      =>  $InstallmentTotal,
        'home'                  =>  $HomePage
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
        echo Module::display(_iMODULE_NAME_ECPAY_12_,'ErrorStep.tpl');
        session_destroy(); 
    }else{
        $_SESSION["checkStep"]="1";
        echo Module::display(_iMODULE_NAME_ECPAY_12_,'validationOrder.tpl');
    }
    
}else if($_SESSION["checkStep"]=="1" && $check=="1"){
    $CheckPay->validateOrder((int)$cart->id, 1,  $InstallmentTotal, $CheckPay->displayName, NULL, array(), NULL, false, $customer->secure_key);
    $newOrderStatusId="1";
    $history = new OrderHistory();
    $history->id_order = (int)($CheckPay->currentOrder);
    $history->changeIdOrderState((int)$newOrderStatusId, (int)($CheckPay->currentOrder));
    $history->addWithemail();
    
    $smarty->assign(array(  'mer_id'        =>  $CheckPay->getShopCode(),
                            'payment_type'  =>  $CheckPay->getPaymentType(),
                            'total'         =>  $inttotal,
                            'amt'           =>  $InstallmentTotal,
                            "od_sob"        =>  $CheckPay->currentOrder,
                            "URL"           =>  $CheckPay->getBaseURL(),
                            "return_url"    =>  "http://".$_SERVER["HTTP_HOST"].__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.(int)($cart->id).'&id_module='.(int)$CheckPay->id.'&id_order='.(int)$CheckPay->currentOrder,
        ));
    echo Module::display(_iMODULE_NAME_ECPAY_12_,'sentToEcPay.tpl');
    

    
    
    


    
}else{
    echo Module::display(_iMODULE_NAME_ECPAY_12_,'ErrorStep.tpl');
    session_unregister("checkStep");
    session_destroy();
 
}
include(dirname(__FILE__).'/../../footer.php');

?>

