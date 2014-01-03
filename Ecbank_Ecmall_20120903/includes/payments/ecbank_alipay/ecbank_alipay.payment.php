<?php

/**
 *    綠界科技 ECmall 付款模組
 *    @支付方式	線上信用卡刷卡授權
 *    @author    martellwang
 *    @usage    none
 */
class Ecbank_AlipayPayment extends BasePayment
{
    
    var $_gateway   =   'https://ecbank.com.tw/gateway.php';
    var $_code      =   'ecbank_alipay';

    
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
        $chnid = $this->_config['ecbank_alipay_account'];

        $seller = $this->_config['ecbank_alipay_account'];
        
        $key = $this->_config['ecbank_alipay_key'];

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
		$bk_posturl =$this->_create_notify_url($order_info['order_id']);  //背景通知付款結果網址
		
		//$roturl		=$this->_create_notify_url($order_info['order_id']);     
        
		$roturl         = $this->_create_return_url($order_info['order_id']);  //付款完導向的網址
        //判斷是否使用電子發票
        $i_invoice  = $this->_config['ecbank_alipay_i_invoice'];
        //電子發票商店代號
        $imer_id = $this->_config['ecbank_alipay_imer_id'];
        /*買家email*/
        $email = $order_info['buyer_email'] ;	        
        
        $db = &db(); //讀取商品資料
        $sql = 'select goods_id,goods_name,price,quantity from '.DB_PREFIX.'order_goods where order_id='.$order_info['order_id'];
        $params = $db->getAll($sql);
        $ecbank_gateway = 'https://ecbank.com.tw/web_service/alipay_goods_upload.php';
       /* 上傳商品開始  */
       foreach($params as $param){
        $post_str='enc_key='.$key.
	'&mer_id='.$seller.
	'&type=upload_goods'.
	'&goods_id='.$param['goods_id'].
	'&goods_title='.$param['goods_name'].
	'&goods_price='.round($param['price']).
	'&goods_href=http://ecbank.com.tw';
        // 使用curl取得驗證結果
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$ecbank_gateway);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post_str);
        $strAuth = curl_exec($ch);
        if (curl_errno($ch)){
            $strAuth = false;
        }
        curl_close($ch);
        }	
	$p = '	
            <form name="ecpay" method="post" action="https://ecbank.com.tw/gateway.php">
            <input type="hidden" name="mer_id" value="'.$seller.'">
            <input type="hidden" name="payment_type" value="alipay">
            <input type="hidden" name="od_sob" value="'.$od_sob.'">
            <input type="hidden" name="amt" value="'.round($amount).'">
            <input type="hidden" name="return_url" value="'.$roturl.'">
            <input type="hidden" name="ok_url" value="'.$bk_posturl.'">';
                foreach($params as $param){
            $p .= '
                <input type="hidden" name="goods_name[]" value="'.$param['goods_id'].'">
                <input type="hidden" name="goods_amount[]" value="'.$param['quantity'].'">
                ';
            }
            if($i_invoice == 'yes'){
                $p .= '
                    <input type="hidden" name="inv_active" value=1>
                    <input type="hidden" name="inv_mer_id" value="'.$imer_id.'">
                    <input type="hidden" name="inv_amt" value="'.round($amount).'">
                    <input type="hidden" name="inv_delay" value=0>
                    <input type="hidden" name="inv_semail" value="'.$email.'">
                    ';    
                foreach($params as $param){
                $p .= '
                    <input type="hidden" name="prd_name[]" value="'.$param['goods_name'].'">
                    <input type="hidden" name="prd_qry[]" value="'.$param['quantity'].'">
                    <input type="hidden" name="prd_price[]" value="'.$param['price'].'">
                ';    
                }
                $p .= '
                    <input type="hidden" name="prd_name[]" value=運費>
                    <input type="hidden" name="prd_qry[]" value=1>
                    <input type="hidden" name="prd_price[]" value="'.(round($amount)-($order_info['[goods_amount] '])).'">
                ';    
            }
            $p.='<input type="submit" value="開始進行資料轉送" style="display:none"> 
            </form>
            <script language=javascript>
            document.forms.ecpay.submit();
            </script>';
       /* 編寫上傳訂單資料表 */

        /*只有商品上架與修改成功才會上傳訂單 */
        if($strAuth == 'state=NEW_SUCCESS'){
            echo '新增上架商品成功';
            echo $p;
        }else if($strAuth == 'state=MODIFY_SUCCESS'){
            echo '修改上架商品成功';
            echo $p;
        }else{
            echo '錯誤：'.$strAuth;
        }
    }

    /**
     *    返回通知结果
     *
     */
    function verify_notify($order_info, $strict = false)
    {          
	    if (empty($order_info))
            {
		$this->_error('order_info_empty');
		return false;
            }
            $notify = $this->_get_notify();	
            $ecbank_check_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid_new.php';
			
		
		//驗證是否來自於綠界科技的封包
		$serial = trim($notify['proc_date'].$notify['proc_time'].$notify['tsr'].$notify['od_sob'].$notify['amt']);
		$checkcode=$this->_config['ecbank_alipay_key'];
		$post_parm	=	'key='.$checkcode.
						'&serial='.$serial.
						'&mac='.$notify['mac'];
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
			if($strAuth != 'valid=1'){
        	           $this->_error('sign_inconsistent');
			            return false;
        	}
		
		//驗證封包作業結束
	if ($notify['succ'] != '1'){
            $this->_error('notify_unauthentic');
            return false;
      //} else {
      //			$pay_status = ORDER_ACCEPTED;
      //			echo '2';
     //echo $notify['succ'].'<hr>'.$pay_status; exit;
	} else {
            $pay_status	=	ORDER_ACCEPTED;
            echo 'ok';
	}
        return array(
            'target'    =>  $pay_status,
	);
    }
}
?>