<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* PayPal IPN Result Checker
*
* @version $Id: checkout.result.php 1394 2008-05-04 19:05:15Z soeren_nb $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/

mm_showMyFileName( __FILE__ );

if( !isset( $_REQUEST["order_id"] ) || empty( $_REQUEST["order_id"] )) {
	echo $VM_LANG->_('VM_CHECKOUT_ORDERIDNOTSET');
	
}
else {
	include( CLASSPATH. "payment/ps_ecpay.cfg.php" );
	$proc_date='';
	$order_id = intval( vmGet( $_REQUEST, "order_id" ));

	$q = "SELECT order_status FROM #__{vm}_orders WHERE ";
	$q .= "#__{vm}_orders.user_id= " . $auth["user_id"] . " ";
	$q .= "AND #__{vm}_orders.order_id= $order_id ";
	$db->query($q);
	if ($db->next_record()) {
		$order_status = $db->f("order_status");
		
		if($order_status == 'C' || $order_status == 'P') {   
        
    	
		//print_r($_REQUEST);
		$process_time = trim(stripslashes($_POST['process_time'])); 	
		$gwsr = trim(stripslashes($_POST['gwsr'])); 	
		$amount = trim(stripslashes($_POST['amount'])); 
		$spcheck = trim(stripslashes($_POST['spcheck'])); 
		$succ=	trim(stripslashes($_POST['succ']));
		$checkcode = ECPAY_CHECKCODE;

		$strAuth=gwSpcheck($process_time,$gwsr,$amount,$spcheck,$checkcode);


		$d['order_id'] = $_REQUEST["order_id"];

			if($strAuth == 'YES' && $succ=='1') {
		   	 	$d['order_status'] = 'C';
				echo '<img src="'.VM_THEMEURL.'images/button_ok.png"     align="middle" alt="'.$VM_LANG->_('VM_CHECKOUT_SUCCESS').'" border="0" />';
				echo "付款已經完成";
				require_once ( CLASSPATH . 'ps_order.php' );
			  	$ps_order= new ps_order;
	   			$ps_order->order_status_update($d);
			} else {
		    	$d['order_status'] = 'X';
				echo '<img src="'.VM_THEMEURL.'images/button_cancel.png" align="middle" alt="'.$VM_LANG->_('VM_CHECKOUT_FAILURE').'" border="0" />';
				echo "付款失敗,點選下列 < 點擊此鏈結查看訂單詳情 > 可重新進行線上刷卡支付";
        	}
		


			
   
    
    	echo '<br /><p><a href="index.php?option=com_virtuemart&page=account.order_details&order_id='.$order_id.'">'.$VM_LANG->_('PHPSHOP_ORDER_LINK').'</a></p>';
   
	}	else {
		echo $VM_LANG->_('VM_CHECKOUT_ORDERNOTFOUND') . '!';
		
	}
	}
}

function gwSpcheck($process_time,$gwsr,$amount,$spcheck,$check_sum) {    
			$yes="YES";
			$no="NO";

	$T=$process_time+$gwsr+$amount;	//算出認證用的字串
	$a = substr($T,0,1).substr($T,2,1).substr($T,4,1);//取出檢查碼的跳字組合 1,3,5 字元
	$b = substr($T,1,1).substr($T,3,1).substr($T,5,1);//取出檢查碼的跳字組合 2,4,6 字元
	$c = ( $check_sum % $T ) + $check_sum + $a + $b;//取餘數 + 檢查碼 + 奇位跳字組合 + 偶位跳字組合
		
		if($spcheck == $c) {
        	return $yes;
    	}  else {
    		return $no;
    	}  
}
       

?>
