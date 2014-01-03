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

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/ecbank_vacc.php';

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
    $modules[$i]['desc']    = 'ecbank_vacc_desc';

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
        array('name' => 'ecbank_vacc_account',           'type' => 'text',   'value' => '1111'),
        array('name' => 'ecbank_vacc_checkcode',         'type' => 'text',   'value' => '12742742'),
       	//array('name' => 'ecbank_vacc_language',	         'type' => 'select', ' value' => '0')
        array('name' => 'ecbank_vacc_inv_active',	      'type' => 'select',	'value' => '0'),
        array('name' => 'ecbank_vacc_inv_mer_id',	      'type' => 'text',         'value' => '')
    );
    return;
}

/**
 * 类
 */
class ecbank_vacc
{
    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function ecbank_vacc()
    {
    }

    function __construct()
    {
        $this->ecbank_vacc();
    }
	
	
	
    /**
     * 提交函数
     */
    function get_code($order, $payment)
    {
		
		$c_mid		= trim($payment['ecbank_vacc_account']); 
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
		//$c_language	= $payment['ecbank_vacc_language'];
		$c_memo1	= $order['log_id'];
		$c_memo2	= $order['log_id'];
		$def_url	= '';
		$key=trim($payment['ecbank_vacc_checkcode']);

		//開始綠界虛擬帳號取號
		
		$param = array (
        // ecbank主機
        'ecbank_gateway' => 'https://ecbank.com.tw/gateway.php',
        // 您的ECBank商店代號
        'mer_id' => $c_mid,
		// 付款方式 vacc 即為虛擬帳號
        'payment_type' => 'vacc',
		// 付款收單銀行
        'setbank' => 'ESUN',
        // 商店設定在ECBank管理後台的交易加密私鑰
        'enc_key' => $key,
        // 賣家自訂交易編號
		'od_sob' => $c_order,
        // 繳費金額
        'amt' => intval($c_orderamount),
		//允許繳費有效天數
		'expire_day' => '7',
        // 付款完成通知網址
        'ok_url' => rawurlencode($c_returl)
		);

		// 執行取號
		$strAuth = '';
                $nvpStr ='mer_id='.$param['mer_id'].
			'&payment_type='.$param['payment_type'].
			'&setbank='.$param['setbank'].
			'&enc_key='.$param['enc_key'].
  		    '&od_sob='.$param['od_sob'].
			'&amt='.$param['amt'].
   			'&expire_day='.$param['expire_day'].
 		    '&ok_url='.$param['ok_url'];

                $temp=order_goods($order['order_id']);
                //判斷是否使用電子發票
                if($payment['ecbank_vacc_inv_active']=="1"){
                    $nvpStr.="&inv_active=1";
                    $nvpStr.="&inv_mer_id=".$payment['ecbank_vacc_inv_mer_id'];
                    $nvpStr.="&inv_semail=".$c_email;
                    $nvpStr.="";
                    $nvpStr.="";
                    $nvpStr.="";
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
			if (curl_errno($ch)) { print_r($strAuth); $strAuth = false;  exit; }
			curl_close($ch);
		if($strAuth) {
                parse_str($strAuth, $res);
				//print_r($strAuth);
				//print_r($res);
                if(!isset($res['error']) || $res['error'] != '0')
                    $def_url= "<acript>alert('取號錯誤')</script>";
                else {
                     $def_url.='轉帳銀行代碼:(<font color=blue size=+2>'.$res['bankcode'].'</font>)';
		   			 $def_url.='轉帳銀行帳戶:(<font color=blue size=+2>'.$res['vaccno'].'</font>)';
					 $def_url.="<br>持玉山銀行金融卡轉帳可免付跨行交易手續費，其它銀行依該行跨行手續費規定扣繳<br><a href=https://netbank.esunbank.com.tw/webatm/> 玉山銀行 WEB-ATM https://netbank.esunbank.com.tw/webatm/</a>";
					 $note = '綠界 ECBank交易流水号:'.$res['tsr'];
				     //order_paid($c_order, '0', $note);
        			 return $def_url;                
			     }
				 
		} else {
		
        $def_url= "取號失敗";
		return $def_url;  
		//exit;
		}
    }

    /**
     * 处理函数
     */
 
    function respond()
    {
		
		//exit;
		$payment  = get_payment('ecbank_vacc');

		//驗證碼
		$checkcode = trim($payment['ecbank_vacc_checkcode']);
		
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
			//exit;
		}
		echo 'OK';
		return true;
	}
	
	
	
}
?>