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

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/gw_allpay6.php';

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
    $modules[$i]['desc']    = 'gw_allpay6_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 作者 */
    $modules[$i]['author']  = 'gw_allpay6';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.allpay.com.tw/';

    /* 版本号 */
    $modules[$i]['version'] = 'V0.1';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'gw_allpay6_account',           'type' => 'text',   'value' => '4'),
        array('name' => 'gw_allpay6_checkcode',         'type' => 'text',   'value' => '94499380'),
        array('name' => 'gw_allpay6_installment',       'type' => 'text',   'value' => '0'),
        array('name' => 'gw_allpay6_language',	      'type' => 'select',	'value' => '0'),
        array('name' => 'gw_allpay6_inv_active',	      'type' => 'select',	'value' => '0'),
        array('name' => 'gw_allpay6_inv_mer_id',	      'type' => 'text',         'value' => ''),
    );
    return;
}

/**
 * 类
 */
class gw_allpay6
{
    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function gw_allpay6()
    {
    }

    function __construct()
    {
        $this->gw_allpay6();
    }
	
    /**
     * 提交函数
     */
    function get_code($order, $payment)
    {
		//include_once(ROOT_PATH.'includes/iconv/cls_iconv.php');
		//$iconv = new Chinese(ROOT_PATH);
		$c_mid		= trim($payment['gw_allpay6_account']); 
		//$c_order	= $order['order_sn'];
		$c_order	= $order['log_id'];
		$c_name		= trim($order['consignee']);		
		$c_address	= trim($order['address']);	
		$c_tel		= trim($order['tel']);	
		$c_post		= trim($order['zipcode']);
		$c_email	= trim($order['email']);
		$c_orderamount = $order['order_amount'];
		$c_ymd		= date('Ymd',time());
		$c_moneytype= "0";
		$c_retflag	= "1";
		$c_returl	= return_url(basename(__FILE__, '.php'));
		$notifytype	= "0";
		$c_language	= $payment['gw_allpay6_language'];
		$c_memo1	= $order['log_id'];
		$c_memo2	= $order['log_id'];

		//$srcStr = $c_mid . $c_order . $c_orderamount . $c_ymd . $c_moneytype . $c_retflag . $c_returl . $c_paygate . $c_memo1 . $c_memo2 . $notifytype . $c_language . $c_pass;
		//$c_signstr	= md5($srcStr);
		if (is_numeric($payment['gw_allpay6_installment']) && $payment['gw_allpay6_installment'] > 0){
                    if ($order['pay_fee'] == 0){ //判斷尚未更新過手續費
		  	$c_orderamount=round($c_orderamount*(1+$payment['gw_allpay6_installment']));		  	
		  	$pay_button = "".$GLOBALS['_LANG']['pay_button']."".$GLOBALS['_LANG']['gw_allpay6_stage']."期 ".($payment['gw_allpay6_installment'] * 100)."% 利率付款 總計金額:".$c_orderamount."元";
                        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('pay_log') . ' SET order_amount = '.round($c_orderamount).' WHERE log_id = '.$order['log_id'];
                        $GLOBALS['db']->query($sql);
                        $order_temp=array(
                            "order_amount"=>$c_orderamount, 
                            "pay_fee"=>round($c_orderamount) - round($order['order_amount']),
                        );
                        update_order($order['order_id'], $order_temp);
                    }else{
                        $pay_button = "".$GLOBALS['_LANG']['pay_button']."".$GLOBALS['_LANG']['gw_allpay6_stage']."期 ".($payment['gw_allpay6_installment'] * 100)."% 利率付款 總計金額:".$c_orderamount."元";
                    }    
		}else{
			  $pay_button = "".$GLOBALS['_LANG']['pay_button']."".$GLOBALS['_LANG']['gw_allpay6_stage']."期零利率付款";
		}		
		
		$def_url  = '<br /><form style="text-align:center;" method=post action="https://credit.allpay.com.tw/form_Sc_to5_fn.php">';
		$def_url .= "<input type='hidden' name='client' value='".$c_mid."'>";
		$def_url .= "<input type='hidden' name='act' value='auth'>";
		$def_url .= "<input type='hidden' name='stage' value='".$GLOBALS['_LANG']['gw_allpay6_stage']."'>";
		$def_url .= "<input type='hidden' name='od_sob' value='".$c_order."'>";
		$def_url .= "<input type='hidden' name='名稱' value='".$c_name."'>";
		$def_url .= "<input type='hidden' name='地址' value='".$c_address."'>";
		$def_url .= "<input type='hidden' name='電話' value='".$c_tel."'>";		
		$def_url .= "<input type='hidden' name='email' value='".$c_email."'>";
		$def_url .= "<input type='hidden' name='amount' value='".$c_orderamount."'>";
		$def_url .= "<input type='hidden' name='時間' value='".$c_ymd."'>";
		$def_url .= "<input type='hidden' name='roturl' value='".$c_returl."'>";
                $temp=order_goods($order['order_id']);
                //判斷是否使用電子發票

                if($payment['gw_ecpay3_inv_active']=="1"){
                    $def_url .= "<input type='hidden' name='inv_active' value='1'>";
                    $def_url .= "<input type='hidden' name='inv_mer_id' value='".$payment['gw_allpay6_inv_mer_id']."'>";
                    $def_url .= "<input type='hidden' name='inv_semail' value='".$c_email."'>";
                    for($i=0;$i<count($temp);$i++){
                        $def_url .= "<input type='hidden' name='prd_name[]' value='".$temp[$i]['goods_name']."'>";
                        $def_url .= "<input type='hidden' name='prd_qry[]' value='".intval($temp[$i]['goods_number'])."'>";
                        $def_url .= "<input type='hidden' name='prd_price[]' value='".intval($temp[$i]['goods_price'])."'>";
                    }
                    $def_url .= "<input type='hidden' name='prd_name[]' value=運費>";
                    $def_url .= "<input type='hidden' name='prd_qry[]' value=1>";
                    $def_url .= "<input type='hidden' name='prd_price[]' value='".intval($order['shipping_fee'])."'>";
                    $def_url .= "<input type='hidden' name='prd_name[]' value=手續費>";
                    $def_url .= "<input type='hidden' name='prd_qry[]' value=1>";
                }
                if ($order['pay_fee'] == 0){ 
                    $def_url .= "<input type='hidden' name='prd_price[]' value='".$order_temp['pay_fee']."'>";
                }    
                else{
                    $def_url .= "<input type='hidden' name='prd_price[]' value='".$order['pay_fee']."'>";
                }
		$def_url .= "<input type='submit' value='".$pay_button."'>";
        $def_url .= "</form><br />";
        return $def_url;
    }

    /**
     * 处理函数
     */
 
    function respond()
    {
		
		if($_REQUEST['succ']=='1') { $_REQUEST['c_succmark']='Y'; }
		if($_REQUEST['succ']=='0') { $_REQUEST['c_succmark']='N'; }
		$payment  = get_payment('gw_allpay6');
		//print_r($_REQUEST);
		$c_mid			= $_REQUEST['c_mid'];		
		$c_order		= $_REQUEST['od_sob'];		//訂單編號
		$c_orderamount	= $_REQUEST['amount'];//商户提供的订单总金额，
		$c_ymd			= $_REQUEST['process_date'];		//商户传输过来的订单产生日期，格式为"yyyymmdd"，如20050102
		$c_transnum		= $_REQUEST['gwsr'];	//云网支付网关提供的该笔订单的交易流水号，
		$c_succmark		= $_REQUEST['c_succmark'];	//交易成功标志，Y-成功 N-失败			
		$c_moneytype	= $_REQUEST['c_moneytype'];	//支付币种，0为人民币
		$c_cause		= $_REQUEST['response_msg]'];		//如果订单支付失败，则该值代表失败原因		
		$c_memo1		= $_REQUEST['c_memo1'];		//商户提供的需要在支付结果通知中转发的商户参数一
		$c_memo2		= $_REQUEST['c_memo2'];		//商户提供的需要在支付结果通知中转发的商户参数二
		$c_signstr		= $_REQUEST['inspect'];	//云网支付网关对已上信息进行MD5加密后的字
		$c_checkcode	= trim($payment['gw_allpay6_checkcode']);
                $c_installmente	= trim($payment['gw_allpay6_installment']);
		
		function gwSpcheck($s,$U) { //算出認證用的字串
				$a = substr($U,0,1).substr($U,2,1).substr($U,4,1); //取出檢查碼的跳字組合 1,3,5 字元
				$b = substr($U,1,1).substr($U,3,1).substr($U,5,1); //取出檢查碼的跳字組合 2,4,6 字元
				$c = ( $s % $U ) + $s + $a + $b; //取餘數 + 檢查碼 + 奇位跳字組合 + 偶位跳字組合
				return $c; 
		}
	
		$TOkSi = $_REQUEST['process_time'] + $_REQUEST['gwsr'] + $_REQUEST['amount'];
		$my_spcheck = gwSpcheck($c_checkcode,$TOkSi); 
                /*if(intval(order_amount($c_order))==intval($c_orderamount)){
                    $checkAmount="1";
                };*/
				
        if(check_money($c_order, $c_orderamount) ){
			$checkAmount="1";
		}  
        /* echo '$c_orderamount='.$c_orderamount.'<br>'        ;
        echo '$c_order='.$c_order.'<br>';
        echo '$my_spcheck='.$my_spcheck.'<br>';
        echo '$_REQUEST["spcheck"]='.$_REQUEST['spcheck'].'<br>';
        echo '$_REQUEST["succ"]='.$_REQUEST['succ'].'<br>';
        echo '$checkAmount='.$checkAmount.'<br>'; */
                
		if($my_spcheck!= $_REQUEST['spcheck']  || $_REQUEST['succ']!='1' || $checkAmount!="1"){
                        echo '失敗';
			return false;
			} else {
    			$note = '歐付寶交易流水号:'.$c_transnum.' 總金額:'.$c_orderamount;
                        
                        if($_REQUEST['inv_error']=="0"){
                            $note.='，發票開立成功。';
                        }else if($_REQUEST['inv_error']==""){
                            $note.='，未開立發票。';
                        }else{
                            $note.='，發票錯誤代碼'.$_REQUEST['inv_error'];
                        }
            	/* 改变订单状态 */
				// PS_PAYING  代表已經付款
				// PS_PAYED   代表還沒有付款 
				//echo $c_order."-".PS_PAYED."-".$c_ymd."<br>";
				order_paid($c_order, PS_PAYED, $note);
            	//exit;
				//order_paid($c_memo2, PS_PAYING, $note);
           		return true;
    		}
	}
	
}
?>