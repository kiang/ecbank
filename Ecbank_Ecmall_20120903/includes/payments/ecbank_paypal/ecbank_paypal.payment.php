<?php

/**
 *    綠界科技 ECmall 付款模組
 *    @支付方式	綠界 ECBank
 *    @author    martellwang
 *    @usage    none
 *    @日期:2010/04/23
 */

class Ecbank_paypalPayment extends BasePayment
{
    
    var $_gateway   =   'https://ecbank.com.tw/gateway.php';
    var $_code      =   'ecbank_paypal';

    
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
        $mer_id = $this->_config['ecbank_paypal_account'];
		
		// 交易加密私鑰
		$checkcode = $this->_config['ecbank_paypal_key'];
								   

        $seller = $this->_config['ecbank_paypal_account'];

        /* 商品名称 */
        $mch_name = $this->_get_subject($order_info);

        /* 總金额 */
        $amount = $order_info['order_amount'] ;

       

        /* 交易说明 */
        $mch_desc = $this->_get_subject($order_info);
        $need_buyerinfo = '2' ;

        /* 货币类型 */
        $fee_type = '1';
    
        $od_sob = $this->_get_trade_sn($order_info);    

      
		$transaction_id = $mch_vno;
        /* 返回的路径 */
		$bk_posturl =$this->_create_notify_url($order_info['order_id']);
		//$roturl =$this->_create_notify_url($order_info['order_id']);
     
        $roturl    = $this->_create_return_url($order_info['order_id']);
		//$roturl    = $this->_create_notify_url($order_info['order_id']);
		//$bk_posturl    = $this->_create_return_url($order_info['order_id']);
		
        //判斷是否使用電子發票
        $i_invoice  = $this->_config['ecbank_paypal_i_invoice'];
        //電子發票商店代號
        $imer_id = $this->_config['ecbank_paypal_imer_id'];
        /*買家email*/
        $email = $order_info['buyer_email'] ;
        
        /* 交易参数 */
        $parameter = array(
            'mer_id'            => $mer_id,                       // 商店代號
			'payment_type'		=> paypal,							//paypal
			'od_sob'    		=> $od_sob,             // 交易号(订单号)，由商户网站产生(建议顺序
			'item_name'    		=> $od_sob,             // 交易項目名稱
			'item_desc'    		=> $mch_name,             // 交易項目說明
			'cur_type'    		=> 'TWD',             // 貨幣類別
			'amt'         		=> round($amount),                    //售價
			'cancel_url'         		=> $roturl,                    //售價
			'ok_url'         		=> urlencode($bk_posturl),                  //售價
			'return_url'         		=> $roturl                   //售價
        );
        if($i_invoice == 'yes'){
            $parameter['inv_active']='1';
            $parameter['inv_mer_id']=$imer_id;
            $parameter['inv_amt']=round($amount);
            $parameter['inv_delay']='0';
            $parameter['inv_semail']=$email;
            $parameter['prd_name[]']='交易金額';
            $parameter['prd_qry[]']=1;
            $parameter['prd_price[]']=round($amount);
        }
        return $this->_create_payform('POST', $parameter);
    }

    /**
     *    返回通知结果
     *
     */
	
	 
    function verify_notify($order_info, $strict = false)
    {
       // print_r($order_info); echo "<hr>";  //exit;
		//print_r($_REQUEST); echo "<hr>";
	   // exit;
	   
	   if (empty($order_info))
		{
			$this->_error('order_info_empty');
			return false;
		}
		
		$notify = $this->_get_notify();
	
		$ecbank_check_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid.php';		
		
	
		if($notify['tac']) {
		//$gwcheck=gwSpcheck($notify['process_time'],$notify['gwsr'],$notify['amount'],$notify['spcheck'],$this->_config['gwpay_key']);
		
		$serial = trim($notify['proc_date'].$notify['proc_time'].$notify['tsr']);
		$checkcode=$this->_config['ecbank_paypal_key'];
		$post_parm	=	'key='.$checkcode.
						'&serial='.$serial.
						'&tac='.$notify['tac'];

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
		}


				
		
        if ($order_info['out_trade_sn'] != $notify['od_sob'])
        {
            /* 通知中的订单与欲改变的订单不一致 */
            $this->_error('order_inconsistent');
			
            return false;
        }
		
		
		if (abs($order_info['order_amount']) != $notify['amt'])
        {
            
			echo "<hr>".$order_info['order_amount']."<hr>".$notify['amt'];  exit;
			/* 支付的金额与实际金额不一致 */
            $this->_error('price_inconsistent');
			
            return false;
        }
		
		
		
		
		//$show_code=echo_mycode($_REQUEST['tspay_no'],$_POST['store']);
		//$show_store=echo_mycode($_REQUEST['store']);
		//echo $show_code;
		//exit;
		
		
		
		
		if($notify['succ']) {
		  	if ($notify['succ'] == '1')  {
							
            	$pay_status =  ORDER_ACCEPTED;
        
			} else {
				$this->_error('notify_unauthentic');
				$pay_status = ORDER_CANCELED;
				return false;
				
			}
		} else {  
                    $pay_status = ORDER_FINISHED;			
		}
			
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