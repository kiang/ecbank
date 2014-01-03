<!DOCTYPE html>
<?php
/**
 * ECSHOP 云网支付@网关插件
 * ============================================================================
 * 版权所有 (C) 2005-2007 北京亿商互动科技发展有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
 * ============================================================================
 * @author:     CHNWAY <chnway@gmail.com>
 * @version:    v2.1
 * @website:	www.chnway.cn
 * ---------------------------------------------
 * $Author ID: chzfz  $
 * $Date: 2007年7月17日  7:37:16 ) $
 */
if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/ecbank_paypal.php';

if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'ecbank_paypal_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';
	
	/* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

	/* 排序 */
	//$modules[$i]['pay_order']  = '1';

    /* 作者 */
    $modules[$i]['author']  = '綠界科技';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.greenworld.com.tw';

    /* 版本号 */
    $modules[$i]['version'] = 'V0.1';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'ecbank_paypal_account',           'type' => 'text',   'value' => '1111'),
        array('name' => 'ecbank_paypal_checkcode',         'type' => 'text',   'value' => '12742742'),
       	array('name' => 'ecbank_paypal_language',	       'type' => 'select',	'value' => '0'),
		array('name' => 'ecbank_paypal_cur_type',	       'type' => 'select',	'value' => 'TWD')
    );
    return;
}

/**
 * 类
 */
class ecbank_paypal
{
    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function ecbank_paypal()
    {
    }

    function __construct()
    {
        $this->ecbank_paypal();
    }
	
    /**
     * 提交函数
     */
    function get_code($order, $payment)
    {
		$payment  = get_payment('ecbank_paypal');
		
		$c_cur		= trim($payment['ecbank_paypal_cur_type']);	//使用貨幣
		$c_mid		= trim($payment['ecbank_paypal_account']); //商店代號
		$c_order	= $order['log_id'];
		$c_order_sn	= $order['order_sn'];
		$c_name		= trim($order['consignee']);		
		$c_address	= trim($order['address']);	
		$c_tel		= trim($order['tel']);	
		$c_post		= trim($order['zipcode']);
		$c_email	= trim($order['email']);
		//$c_orderamount = trim(intval($order['order_amount']));
		$c_orderamount = trim($order['order_amount']);
		$c_ymd		= date('Ymd',time());
		$c_moneytype= "0";
		$c_retflag	= "1";
		$c_returl	= return_url(basename(__FILE__, '.php'));
		$notifytype	= "0";
		$c_language	= $payment['ecbank_paypal_language'];//語系
		$c_memo1	= $order['log_id'];
		$c_memo2	= $order['log_id'];
        

		$def_url  = '<br /><form style="text-align:center;" method=post action="https://ecbank.com.tw/gateway.php">';
		$def_url .= "<input type='hidden' name='mer_id' value='".$c_mid."'>";
		$def_url .= "<input type='hidden' name='payment_type' value='paypal'>";
		//$def_url .= "<input type='hidden' name='setbank' value='ESUN'>";
		$def_url .= "<input type='hidden' name='od_sob' value='".$c_order."'>";
		
		$def_url .= "<input type='hidden' name='item_name' value='".$c_order_sn."'>";
		
		$def_url .= "<input type='hidden' name='cur_type' value='".$c_cur."'>";
		$def_url .= "<input type='hidden' name='amt' value='".$c_orderamount."'>";
		$def_url .= "<input type='hidden' name='return_url' value='".$c_returl."'>";
		$def_url .= "<input type='hidden' name='c_name' value='".$c_name."'>";
		$def_url .= "<input type='hidden' name='c_address' value='".$c_address."'>";
		$def_url .= "<input type='hidden' name='c_tel' value='".$c_tel."'>";
		$def_url .= "<input type='hidden' name='c_post' value='".$c_post."'>";
		$def_url .= "<input type='hidden' name='email' value='".$c_email."'>";
		$def_url .= "<input type='hidden' name='c_ymd' value='".$c_ymd."'>";
		$def_url .= "<input type='hidden' name='c_moneytype' value='".$c_moneytype."'>";
		$def_url .= "<input type='hidden' name='c_retflag' value='".$c_retflag."'>";
		$def_url .= "<input type='hidden' name='c_language' value='".$c_language."'>";
		$def_url .= "<input type='hidden' name='c_memo1' value='".$c_memo1."'>";
		$def_url .= "<input type='hidden' name='c_memo2' value='".$c_memo2."'>";
		$def_url .= "<input type='hidden' name='notifytype' value='".$notifytype."'>";
		$def_url .= "<input type='submit' value='" . $GLOBALS['_LANG']['pay_button'] . "'>";
        $def_url .= "</form><br />";
        return $def_url;
    }

    /**
     * 处理函数
     */
 
    function respond()
    {
		//print_r($_REQUEST);	
		
		if($_REQUEST['succ']=='1') { $_REQUEST['c_succmark']='Y'; }
		if($_REQUEST['succ']=='0') { $_REQUEST['c_succmark']='N'; }
		//echo $_REQUEST['c_succmark'];
		//exit;
		$payment  = get_payment('ecbank_paypal');

		//驗證碼
		$checkcode = trim($payment['ecbank_paypal_checkcode']);
		
		// 組合字串
		$serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr']);
	    //echo $serial;
		// 回傳的交易驗證壓碼
		$tac = trim($_REQUEST['tac']);
		$c_order=trim($_REQUEST['od_sob']);
		
		$c_orderamount = $_REQUEST['amt'];
		//echo $tac;
		

		$ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid.php';
		$post_parm	=	'key='.$checkcode.
						'&serial='.$serial.
						'&tac='.$tac;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$ecbank_gateway);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post_parm);
		$strAuth = curl_exec($ch);
		if (curl_errno($ch)) $strAuth = false;
		//echo $strAuth;
		curl_close($ch);
		if(check_money($c_order, $c_orderamount) ){
			$checkAmount="1";
		}	
		//echo '測試'.$c_orderamount;
		if($strAuth == 'valid=1'){
		    //echo 'ok1';
			/* echo 'check:'. $checkAmount .'<br />';
			echo $_REQUEST['succ']; */
			 	if($_REQUEST['succ']=='1' && $checkAmount == "1") {
					//$sql = 'UPDATE ' . $GLOBALS['ecs']->table('pay_log') . " SET is_paid = '0' WHERE log_id = '$c_order'";
					
					//$GLOBALS['db']->query($sql);
/* 					if($_REQUEST['inv_error']=="0"){
                                        $note.='，發票開立成功。';
                                        }else if($_REQUEST['inv_error']==""){
                                            $note.='，未開立發票。';
                                        }else{
                                            $note.='，發票錯誤代碼'.$_REQUEST['inv_error'];
                                        } */
					order_paid($c_order, PS_PAYED, $note);
					//echo 'ok';
					return true;
					
				} 
           		
		}else{
			
		    //echo '不合法的交易';
			return false;
			exit;
		}
	}

}
?>