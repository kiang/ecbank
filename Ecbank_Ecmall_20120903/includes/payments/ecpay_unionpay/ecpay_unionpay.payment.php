<?php

/**
 *    綠界科技 ECmall 付款模組
 *    @支付方式	線上信用卡刷卡授權
 *    @author    martellwang
 *    @usage    none
 */

class Ecpay_UnionpayPayment extends BasePayment
{
    
    var $_gateway   =   'https://ecpay.com.tw/form_Sc_to5.php';
    var $_code      =   'ecpay_unionpay';

    
    function get_payform($order_info)
    {
        /* 版本編號 */
        $version = '1.0';

  
        $act = 'auth';

      
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
        $chnid = $this->_config['ecpay_unionpay_account'];

        $seller = $this->_config['ecpay_unionpay_account'];

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
     
        $roturl    = $this->_create_return_url($order_info['order_id']);
        //判斷是否使用電子發票
        $i_invoice  = $this->_config['ecpay_unionpay_i_invoice'];
        //電子發票商店代號
        $imer_id = $this->_config['ecpay_unionpay_imer_id'];
        /*買家email*/
        $email = $order_info['buyer_email'] ;		
		

        /* 交易参数 */
        $parameter = array(
             'date'              => $today,                       // 商户日期：如20051212
            'desc'              => $mch_name,
            'client'      	=> $seller,                        // 商家的财付通商户号
            'od_sob'    	=> $od_sob,             // 交易号(订单号)，由商户网站产生(建议顺序累加)
            'act'               => 'auth',
            'CUPus'             => '1',
            'amount'         	=> round($amount),                    //订单总价
            'roturl'       	=> $roturl,
            'bk_posturl'     	=> $bk_posturl,
            'mallurl'           => $roturl
        );
            if($i_invoice == 'yes'){
            $parameter['inv_active']='1';
            $parameter['inv_mer_id']=$imer_id;
            $parameter['inv_amt']=round($amount);
            $parameter['inv_delay']='0';
            $parameter['inv_semail']=$email;
            $parameter['prd_name[]']='交易金額';
            $parameter['prd_qry[]']='1';
            $parameter['prd_price[]']=round($amount);
        }  
        return $this->_create_payform('POST', $parameter);
    }

    /**
     *    返回通知结果
     *
回
     */
    function verify_notify($order_info, $strict = false)
    {
        
       
	   
	   if (empty($order_info))
		{
			$this->_error('order_info_empty');
			return false;
		}
		
		$notify = $this->_get_notify();
	
		
		 function gwSpcheck($process_time,$gwsr,$amount,$spcheck,$check_sum){    
			$T=$process_time+$gwsr+$amount;	//算出認證用的字串
			$a = substr($T,0,1).substr($T,2,1).substr($T,4,1);//取出檢查碼的跳字組合 1,3,5 字元
			$b = substr($T,1,1).substr($T,3,1).substr($T,5,1);//取出檢查碼的跳字組合 2,4,6 字元
			$c = ( $check_sum % $T ) + $check_sum + $a + $b;//取餘數 + 檢查碼 + 奇位跳字組合 + 偶位跳字組合
				if($spcheck == $c) {
    	    		return '1';
    			}  else {
    				return '0';
    			}	  
			}
		
		if($notify['spcheck']) {
			$gwcheck=gwSpcheck($notify['process_time'],$notify['gwsr'],$notify['amount'],$notify['spcheck'],$this->_config['ecpay_unionpay_key']);
		
			if ($gwcheck != '1')
    	    {
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
		
		
		if (abs($order_info['order_amount']) != $notify['amount'])
        {
            
			echo "<hr>".$order_info['order_amount']."<hr>";  exit;
			/* 支付的金额与实际金额不一致 */
            $this->_error('price_inconsistent');
			
            return false;
        }
		
		
		  if ($notify['succ'] != '1')
        {
            $this->_error('notify_unauthentic');
			$pay_status = ORDER_CANCELED;
			//echo "不對";
            return false;
        } else {
				$pay_status = ORDER_ACCEPTED;
			//echo "對了";
		}
		
			//echo "到了".$pay_status; exit;	
         return array(
			'target'    =>  $pay_status,
			
		 );
	
    }
    
	
	
    function gwSpcheck($process_time,$gwsr,$amount,$spcheck,$check_sum)
    {    
		$T=$process_time+$gwsr+$amount;	//算出認證用的字串
		$a = substr($T,0,1).substr($T,2,1).substr($T,4,1);//取出檢查碼的跳字組合 1,3,5 字元
		$b = substr($T,1,1).substr($T,3,1).substr($T,5,1);//取出檢查碼的跳字組合 2,4,6 字元
		$c = ( $check_sum % $T ) + $check_sum + $a + $b;//取餘數 + 檢查碼 + 奇位跳字組合 + 偶位跳字組合
		if($spcheck == $c) {
    	    	return '1';
    		}  else {
    			return '0';
    		}	  
	}
    
    
}

?>