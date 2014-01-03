
<?php
if (!defined('IN_ECS')) {
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/allpay_card9.php';

if (file_exists($payment_lang)) {
    global $_LANG;

    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE) {
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code'] = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc'] = 'allpay_card9_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod'] = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online'] = '1';

    /* 排序 */
    //$modules[$i]['pay_order']  = '1';

    /* 作者 */
    $modules[$i]['author'] = '歐付寶';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.allpay.com.tw';

    /* 版本号 */
    $modules[$i]['version'] = 'V0.1';

    /* 配置信息 */
    $modules[$i]['config'] = array(
        array('name' => 'allpay_card9_account', 'type' => 'text', 'value' => '1111'),
        array('name' => 'allpay_card9_iv', 'type' => 'text', 'value' => 'iv'),
        array('name' => 'allpay_card9_key', 'type' => 'text', 'value' => 'key')
    );
    return;
}

/**
 * 类
 */
class allpay_card9 {

    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function allpay_card9() {
        
    }

    function __construct() {
        $this->allpay_card9();
    }

    /**
     * 提交函数
     */
    function get_code($order, $payment) {

        $c_mid = trim($payment['allpay_card9_account']);
        $log_id = $order['log_id'];
        $order_id = $order['order_sn'];
        $c_orderamount = $order['order_amount'];
        $c_returl = return_url(basename(__FILE__, '.php')) . "?log_id=" . $log_id;
        $c_returl = str_replace('respond', 'allpay_response', $c_returl);
        
        $key = trim($payment['allpay_card9_key']);
        $iv = trim($payment['allpay_card9_iv']);

        $goods = order_goods($order['order_id']);

        foreach ($goods as $good) { //先上架商品
            $product.="#" . $good['goods_name'];
        }
        $product = substr($product, 1);
        $date = date('Y/m/d H:i:s');
        $desc = "Allpay_Ecshop_Module";
        
        $input_array = array(
            'ChoosePayment' => 'Credit',
            'ClientBackURL' => $GLOBALS['ecs']->url(),
            'ItemName' => $product,
            'MerchantID' => $c_mid,
            'MerchantTradeDate' => $date,
            'MerchantTradeNo' => $order_id,
            'PaymentType' => 'aio',
            'ReturnURL' => $c_returl,
            'TotalAmount' => intval($c_orderamount),
            'TradeDesc' => $desc,
            'CreditInstallment' => 9,
            'InstallmentAmount' => intval($c_orderamount)
        );
        ksort($input_array);
        $checkvalue = "HashKey=" . $key . "&" . urldecode(http_build_query($input_array)) . "&HashIV=" . $iv;
        $checkvalue = urlencode($checkvalue);
        $checkvalue = strtolower($checkvalue);
        $checkvalue = md5($checkvalue);

        $gateway = "https://payment.allpay.com.tw/Cashier/AioCheckOut";
        //$gateway = "http://payment-stage.allpay.com.tw/Cashier/AioCheckOut";

        $def_url = '<form style="text-align:center;" method=post action="' . $gateway . '">';
        foreach ($input_array as $param => $value) {
            $def_url .= "<input type='hidden' name='$param' value='$value'>";
        }
        $def_url .= "<input type='hidden' name='CheckMacValue' value='" . $checkvalue . "'>";
        $def_url .= "<input type='submit' value='" . $GLOBALS['_LANG']['pay_button'] . "'>";
        $def_url .= "</form><br />";
        return $def_url;
    }

    function get_result($gateway, $str) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $gateway);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $rs = curl_exec($ch);

        curl_close($ch);

        return $rs;
    }

    /**
     * 处理函数
     */
    function respond() {

        //exit;
        $payment = get_payment('allpay_card9');


        $timestamp = time();
        $key = trim($payment['allpay_card9_key']);
        $iv = trim($payment['allpay_card9_iv']);
        $order_id = $_REQUEST['MerchantTradeNo'];
        $log_id = $_REQUEST['log_id'];
        $mer_id = $_REQUEST['MerchantID'];

        $input_array = array(
            "MerchantID" => $mer_id,
            "MerchantTradeNo" => $order_id,
            "TimeStamp" => $timestamp
        );
        ksort($input_array);
        $checkvalue = "HashKey=$key&" . urldecode(http_build_query($input_array)) . "&HashIV=$iv";
        $checkvalue = strtolower(urlencode($checkvalue));
        $checkvalue = md5($checkvalue);
        $input_array["CheckMacValue"] = $checkvalue;

        $sned_string = http_build_query($input_array);

        $gateway = "https://payment.allpay.com.tw/Cashier/QueryTradeInfo";
        //$gateway = "http://payment-stage.allpay.com.tw/Cashier/QueryTradeInfo";

        $result = $this->get_result($gateway, $sned_string);

        if (check_money($log_id, $_REQUEST['TradeAmt'])) {
            $checkAmount = "1";
        }
        parse_str($result, $res);
        //echo $_REQUEST['RtnCode'] . "||" . $checkAmount . "||" . $res["TradeStatus"] . "||" . $res["TradeAmt"] . "||" . $_REQUEST['TradeAmt'] . "||";
        if ($_REQUEST['RtnCode'] == '1' && $checkAmount == '1' && $res["TradeStatus"] == "1" && $res["TradeAmt"] == $_REQUEST['TradeAmt']) {
            $note = "付款完成" . date("Y-m-d H:i:s");
            order_paid($log_id, PS_PAYED, $note);
            return true;
        } else {
            return false;
        }
    }

}
?>
