<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
/* 支付方式代码 */
$pay_code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';

//获取首信支付方式
if (empty($pay_code) && !empty($_REQUEST['v_pmode']) && !empty($_REQUEST['v_pstring'])) {
    $pay_code = 'cappay';
}

//获取快钱神州行支付方式
if (empty($pay_code) && ($_REQUEST['ext1'] == 'shenzhou') && ($_REQUEST['ext2'] == 'ecshop')) {
    $pay_code = 'shenzhou';
}

/* 参数是否为空 */
if (empty($pay_code)) {
    $msg = "0|pay_not_exist";
} else {
    /* 检查code里面有没有问号 */
    if (strpos($pay_code, '?') !== false) {
        $arr1 = explode('?', $pay_code);
        $arr2 = explode('=', $arr1[1]);

        $_REQUEST['code'] = $arr1[0];
        $_REQUEST[$arr2[0]] = $arr2[1];
        $_GET['code'] = $arr1[0];
        $_GET[$arr2[0]] = $arr2[1];
        $pay_code = $arr1[0];
    }

    /* 判断是否启用 */
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
    if ($db->getOne($sql) == 0) {
        $msg = "0|pay_disabled";
    } else {
        $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';

        /* 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息 */
        if (file_exists($plugin_file)) {
            /* 根据支付方式代码创建支付类的对象并调用其响应操作方法 */
            include_once($plugin_file);

            $payment = new $pay_code();
            $msg = ($payment->respond()) ? "1|OK" : "0|Fail";
        } else {
            $msg = "0|pay_not_exist";
        }
    }
    
}
echo $msg;
?>
