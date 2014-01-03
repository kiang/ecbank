<?php

/**
 *    綠界科技 ECmall 付款模組
 *    @支付方式	綠界 ECBank
 *    @author    martellwang
 *    @usage    none
 *    @日期:2010/04/23
 */

class Ecbank_vaccPayment extends BasePayment
{
    
    var $_gateway   =   'https://ecbank.com.tw/gateway.php';
    var $_code      =   'ecbank_vacc';

    
    function get_payform($order_info)
    {
        /* 版本編號 */
        $version = '1.0';

 
        //$act = 'auth';

      
        /* 交易日期 */
        $today = date('Ymd');

        if (!empty($order_info['order_id']))
        {
            $attach = '';
        }
        else
        {        
            $attach = 'voucher';
        }
        
        /* 平台提供者,綠界科技商店代號 */
        $mer_id = $this->_config['ecbank_vacc_account'];
		
		// 交易加密私鑰
		$checkcode = $this->_config['ecbank_vacc_key'];
								   

        $seller = $this->_config['ecbank_vacc_account'];

        /* 商品名称 */
        $mch_name = $this->_get_subject($order_info);

        /* 總金额 */
        $amt = $order_info['order_amount'] ;

       

        /* 交易说明 */
        $mch_desc = $this->_get_subject($order_info);
        $need_buyerinfo = '2' ;

        /* 货币类型 */
        $fee_type = '1';
    
        $od_sob = $this->_get_trade_sn($order_info);    

      
	$transaction_id = $mch_vno;
        /* 返回的路径 */
		$bk_posturl =$this->_create_notify_url($order_info['order_id']);  //付款結果回傳位址
		//$roturl =$this->_create_notify_url($order_info['order_id']);
     
        $roturl    = $this->_create_return_url($order_info['order_id']);   //付款完成導向地址
		//$bk_posturl    = $this->_create_return_url($order_info['order_id']);
	//判斷是否使用電子發票
        $i_invoice  = $this->_config['ecbank_vacc_i_invoice'];
        //電子發票商店代號
        $imer_id = $this->_config['ecbank_vacc_imer_id'];
        /*買家email*/
        $email = $order_info['buyer_email'] ;
        
        $db = &db(); //讀取商品資料
        $sql = 'select goods_id,goods_name,price,quantity from '.DB_PREFIX.'order_goods where order_id='.$order_info['order_id'];
        $params = $db->getAll($sql);

        /* 交易参数 */
		$setbank='ESUN';
		$expire_day='14';
$post_str ='mer_id='.$mer_id.
	'&payment_type=vacc'.
	'&setbank='.$setbank.
	'&enc_key='.$checkcode.
	'&od_sob='.$od_sob.
	'&amt='.round($amt).
	'&expire_day='.$expire_day.
	'&ok_url='.urlencode($bk_posturl);
        if ($i_invoice == 'yes'){  //電子發票開始
            $post_str .= '&inv_active=1'.
             '&inv_mer_id='.$imer_id.
             '&inv_amt='.round($amt).
             '&inv_semail='.$email.
             '&inv_delay=0';
        foreach($params as $param){
            $post_str.='&prd_name[]='.$param['goods_name'].
                              '&prd_qry[]='.$param['quantity'].
                              '&prd_price[]='.round($param['price']);
        }
        $post_str.='&prd_name[]=運費'.
                              '&prd_qry[]=1'.
                              '&prd_price[]='.round(($amt)-($order_info['goods_amount']));
        }                  //電子發票結束
		
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,'https://ecbank.com.tw/gateway.php');
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$post_str);
$strAuth = curl_exec($ch);
if (curl_errno($ch)) {
	$strAuth = false;
}
curl_close($ch);
if($strAuth) {
	// 分解字串
	parse_str($strAuth, $res);
	// 判斷取號結果
	if(!isset($res['error']) || $res['error'] != '0'){
		echo "取號錯誤，錯誤代碼為=".$res['error'];
	}else {
		echo "銀行代碼:".$res['bankcode'];
		echo "銀行帳戶:".$res['vaccno'];
		echo "交易單號:".$res['tsr'];
	}
} else {
	echo "取號失敗";
}                
                
                //$strAuth = file_get_contents($ecbank_auth_url);
		$parm=urlencode($strAuth);
		//exit;
		
	echo '	
<form name="ecpay" method="post" action="'.$roturl.'">
<input type="hidden" name="parm" value="'.$parm.'">
<input type="submit" value="開始進行線上刷卡" style="display:none"> 
</form>
<script language=javascript>
document.forms.ecpay.submit();
</script>';

		

		echo $strAuth; exit;

		//$info=$res;
		//echo '<pre>';
		//print_r($info); 
		//echo '</pre>';
		//verify_notify($order_info, $strict = false);
		//exit;
		//return $info;
			//$info=$this->_create_payform('GET', $parameter); 
		//	print_r($res);
			
		//	exit;
		//$a['b']='1234';
		//return $parameter;

        //return $this->_create_payform('GET', $parameter);
		//$mysip=$this->_create_payform('GET', $parameter); 		echo $mysip;
    }

    /**
     *    返回通知结果
     *
     */
	
	 
    function verify_notify($order_info, $strict = false)
    {
	    parse_str(urldecode($_REQUEST[parm]), $notify);
	
	   if (empty($order_info))
		{
			$this->_error('order_info_empty');
			return false;
		}
		
        if($notify['tac'] ) {
		//$gwcheck=gwSpcheck($notify['process_time'],$notify['gwsr'],$notify['amount'],$notify['spcheck'],$this->_config['gwpay_key']);
		
		$serial = trim($notify['proc_date'].$notify['proc_time'].$notify['tsr']);
		$checkcode=$this->_config['ecbank_vacc_key'];
		$post_parm	=	'key='.$checkcode.
						'&serial='.$serial.
						'&tac='.$notify['tac'];

		//curl 運作開始
		$ecbank_check_gateway="https://ecbank.com.tw/web_service/get_outmac_valid.php";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$ecbank_check_gateway);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post_parm);
		$strAuth = curl_exec($ch);
		if (curl_errno($ch)) $strAuth = false;
		curl_close($ch);				
		//curl 運作結束 , 產出 $strAuth			
		//echo $strAuth; exit;				
		//if ($gwcheck != '1')
		
		 //error_log("有沒有成功:[".$strAuth."]\r\n", 3, "/var/www/module.ecbank.com.tw/tmp/ecmall.log");
			if($strAuth != 'valid=1'){
    	                $this->_error('sign_inconsistent');
			            return false;
        	}
		}
		

		//out_trade_sn
		
		if($_REQUEST['tac'] ) {
		//$gwcheck=gwSpcheck($notify['process_time'],$notify['gwsr'],$notify['amount'],$notify['spcheck'],$this->_config['gwpay_key']);
		
		$serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr']);
		$checkcode=$this->_config['ecbank_vacc_key'];
		$post_parm	=	'key='.$checkcode.
						'&serial='.$serial.
						'&tac='.$_REQUEST['tac'];
		
		$ecbank_check_gateway="https://ecbank.com.tw/web_service/get_outmac_valid.php";
		//curl 運作開始
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$ecbank_check_gateway);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post_parm);
		$strAuth = curl_exec($ch);
		if (curl_errno($ch)) $strAuth = false;
		curl_close($ch);				
		//curl 運作結束 , 產出 $strAuth			
		//echo $strAuth; exit;				
		//if ($gwcheck != '1')
		
		 
			if($strAuth != 'valid=1'){
    	                $this->_error('sign_inconsistent');
			            return false;
        	}
		
		

        if ($order_info['out_trade_sn'] != $_REQUEST['od_sob'])
        {
            /* 通知中的订单与欲改变的订单不一致 */
			//echo "這邊:<hr>".$order_info['out_trade_sn']."<hr>".abs($notify['od_sob']);  exit;
            $this->_error('order_inconsistent');
			
            return false;
        }
		
		//echo 
		if (abs($order_info['order_amount']) != abs($_REQUEST['amt']))
        {
            
			//echo "這邊:<hr>".$order_info['order_amount']."<hr>".abs($notify['amt']);  exit;
			/* 支付的金额与实际金额不一致 */
            $this->_error('price_inconsistent');
			
            return false;
        }
		
		
		}
		


			$ookk=	'<script type="text/javascript">window.onload = function () {
 				var htmlECBank = "</b><font color=red>尚未付款<br>您已經完成訂單作業,此訂單銀行轉帳代碼如下<br>銀行代碼:<font color=blue>'.$notify['bankcode'].'</font><br>帳號:<font color=blue>'.$notify['vaccno'].'</font><br>轉帳金額:<font color=blue>'.abs($notify['amt']).'</font>元<br>抄寫下號碼後,您可就近到至 ATM 機台操作轉帳,請保留付款收據";
			$(".succeed h4").append(htmlECBank);}</script>';
		
		if($notify['succ'] || $_REQUEST['succ'] ) {
		  	if ($notify['succ'] == '1' || $_REQUEST['succ'] == '1')  {
							
            	$pay_status =  ORDER_ACCEPTED;
        
			} else {
				$this->_error('notify_unauthentic');
				$pay_status = ORDER_CANCELED;
				return false;
				
			}
		} else {
				//echo $echo_op;	//這個時候才把表單秀出
				echo $ookk;
				$pay_status = ORDER_FINISHED;			
		};	
			
         return array(
			'target'    =>  $pay_status,
			
		 );
		 
	}
    function verify_result($result) 
    {
				
        if ($result)
        {
           	echo "ok";
        } else {
			echo "fall";	
		}
    }        
}
?>