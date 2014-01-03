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

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/ecbank_pincodeibon.php';

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
    $modules[$i]['desc']    = 'ecbank_pincodeibon_desc';

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
        array('name' => 'ecbank_pincodeibon_account',           'type' => 'text',   'value' => '1111'),
        array('name' => 'ecbank_pincodeibon_checkcode',         'type' => 'text',   'value' => '12742742'),
       	array('name' => 'ecbank_pincodeibon_language',	         'type' => 'select', ' value' => '0'),
        array('name' => 'ecbank_pincodeibon_inv_active',	      'type' => 'select',	'value' => '0'),
        array('name' => 'ecbank_pincodeibon_inv_mer_id',	      'type' => 'text',         'value' => '')
    );
    return;
}

/**
 * 类
 */
class ecbank_pincodeibon
{
    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function ecbank_pincodeibon()
    {
    }

    function __construct()
    {
        $this->ecbank_pincodeibon();
    }
	
    /**
     * 提交函数
     */
    function get_code($order, $payment)
    {
		//print_r($order);exit;
		$payment  = get_payment('ecbank_pincodeibon');
		
		$c_mid		= trim($payment['ecbank_pincodeibon_account']); 
		$c_order	= $order['log_id'];
		$c_od_sob	= $order['order_sn'];
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
		$c_language	= $payment['ecbank_pincodeibon_language'];
		$c_memo1	= $order['log_id'];
		$c_memo2	= $order['log_id'];
		$key=trim($payment['ecbank_pincodeibon_checkcode']);
		$def_url	= "";

		//開始綠界虛擬帳號取號
		$param = array (
        // ecbank主機
        'ecbank_gateway' => 'https://ecbank.com.tw/gateway.php',
        // 您的ECBank商店代號
        'mer_id' => $c_mid,
        // 商店設定在ECBank管理後台的交易加密私鑰
        'enc_key' => $key,
        // 取得指定銀行虛擬帳號
        //'setbank' => 'ESUN',
		// 賣家自訂交易編號
		'od_sob' => $c_order,
        // 繳費金額
        'amt' => intval($c_orderamount),
		//允許繳費有效天數
		'prd_desc' => $c_order,
        // 付款完成通知網址
        'ok_url' => rawurlencode($c_returl)
		);
		// 執行取號
		$strAuth = '';
	$nvpStr ='payment_type=ibon'.
  		     '&od_sob='.$param['od_sob'].
   			'&mer_id='.$param['mer_id'].
   			'&enc_key='.$param['enc_key'].
   '&amt='.$param['amt'].
   '&prd_desc='.$param['prd_desc'].
  // '&desc1='.$param['desc1'].
   //'&desc2='.$param['desc2'].
   //'&desc3='.$param['desc3'].
   '&ok_url='.$param['ok_url'];
        
                $temp=order_goods($order['order_id']);
                //判斷是否使用電子發票
                if($payment['ecbank_pincodeibon_inv_active']=="1"){
                    $nvpStr.="&inv_active=1";
                    $nvpStr.="&inv_mer_id=".$payment['ecbank_pincodeibon_inv_mer_id'];
                    $nvpStr.="&inv_semail=".$c_email;
                    for($i=0;$i<count($temp);$i++){
                        $nvpStr .= "&prd_name[]=".$temp[$i]['goods_name'];
                        $nvpStr .= "&prd_qry[]=".$temp[$i]['goods_number'];
                        $nvpStr .= "&prd_price[]=".intval($temp[$i]['goods_price']);
                    }
                    $nvpStr .= "&prd_name[]=運費";
                    $nvpStr .= "&prd_qry[]=1";
                    $nvpStr .= "&prd_price[]=".$order['shipping_fee'];;
                }
        

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$param['ecbank_gateway']);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpStr);
$strAuth = curl_exec($ch);
if (curl_errno($ch)) $strAuth = false;
curl_close($ch);

		//exit;
		parse_str($strAuth, $res);
		
		if(!isset($res['error']) || $res['error'] != '0')
		    $def_url= '取號錯誤('.$res['error'].')';
		else {
		    //$def_url ='交易單號: '.$res['tsr'];
		    $def_url.='[統一超商7-Eleven]超商繳費代碼:(<font color=blue size=+2>'.$res['payno'].'</font>)<br><br>';
			$def_url.='<a href=http://www.ecbank.com.tw/expenses-ibon.htm target=_blank>統一超商7-Eleven ibon 門市操作步驟</a><br>';
            $def_url.='請記下上列超商繳費代碼,至最近之統一超商7-Eleven便利商店,操作代碼繳費機台, 於列印出有調碼之繳款單後,至櫃台支付,<br>便可完成繳費,繳費之收據請留存以供備核,繳費之後才算完成購物流程'; 
			$def_url.='<br><br>本線上金流機制採用&lt; <a href=http://www.ecbank.com.tw target=_blank>綠界科技 ECBank 線上支付平台</a> &gt;';
		    //$def_url.='銀行帳戶:(<font color=blue size=+2>'.$res['vaccno'].'</font>)';
			$note = '綠界 ECBank 交易流水号:'.$res['tsr'];
			
			 //order_paid($c_order, '0', $note);
       	 	return $def_url;
			
			
		};
    }

    /**
     * 处理函数
     */
 
    function respond()
    {
		echo 'OK';
		//exit;
		$payment  = get_payment('ecbank_pincodeibon');

		//驗證碼
		$checkcode = trim($payment['ecbank_pincodeibon_checkcode']);
		
		// 組合字串
		$serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr']);
	
		// 回傳的交易驗證壓碼
		$tac = trim($_REQUEST['tac']);
		$c_order=trim($_REQUEST['od_sob']);
		$c_orderamount = $_REQUEST['amt'];

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
		curl_close($ch);
				
		
		if(check_money($c_order, $c_orderamount) ){
			$checkAmount="1";
		}	
		if($strAuth == 'valid=1'){
		    
				if($_REQUEST['succ']=='1' && $checkAmount == "1") {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('pay_log') . " SET is_paid = '0' WHERE log_id = '$c_order'";
					
					$GLOBALS['db']->query($sql);
					
                                        if($_REQUEST['inv_error']=="0"){
                                            $note.='，發票開立成功。';
                                        }else if($_REQUEST['inv_error']==""){
                                            $note.='，未開立發票。';
                                        }else{
                                            $note.='，發票錯誤代碼'.$_REQUEST['inv_error'];
                                        }
                                        order_paid($c_order, PS_PAYED, $note);
					return true;
				} 
           		
		}else{
			
		    echo '不合法的交易';
			return false;
			exit;
		}
	}
}
?>