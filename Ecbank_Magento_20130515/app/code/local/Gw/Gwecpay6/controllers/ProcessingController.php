<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Ecbank
 * @package    Ecbank_EcbankWebatm
 * @copyright  Copyright (c) 2010 Ecbank (http://www.ecbank.com.tw)
 */

class Gw_Gwecpay6_ProcessingController extends Mage_Core_Controller_Front_Action
{
    protected $_successBlockType = 'gwecpay6/success';
    protected $_failureBlockType = 'gwecpay6/failure';
    protected $_cancelBlockType = 'gwecpay6/cancel';

    protected $_order = NULL;
    protected $_paymentInst = NULL;

    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session
     */

    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * when customer selects gwecpay6 payment method
     */
    public function redirectAction()
    {
        try {
            $session = $this->_getCheckout();
            $order = Mage::getModel('sales/order');	
            $order->loadByIncrementId($session->getLastRealOrderId());
            if (!$order->getId()) {
                Mage::throwException('無訂單可供處理');
            }
            if ($order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                $order->setState(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage::helper('gwecpay6')->__('轉向至繳費資訊頁面')
                )->save();
				$order->sendNewOrderEmail();		//發出E-mail通知信
				$order->setEmailSent(true);
            }

            if ($session->getQuoteId() && $session->getLastSuccessQuoteId()) {
                $session->setEcpay6QuoteId($session->getQuoteId());
                $session->setEcpay6SuccessQuoteId($session->getLastSuccessQuoteId());
                $session->setEcpay6RealOrderId($session->getLastRealOrderId());
                $session->getQuote()->setIsActive(false)->save();
				$test = $session->getQuoteId();
				$test2 = $session->setEcpay6QuoteId($session->getQuoteId());
                $session->clear();
            }

            $this->loadLayout();
            $this->renderLayout();
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckout()->addError($e->getMessage());
        } catch(Exception $e) {
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * gwecpay6 returns POST variables to this action
     */
    public function responseAction()
    {
        try {
            $request = $this->_checkReturnedPost();
            $this->_processSale($request);
        } catch (Mage_Core_Exception $e) {
            $this->getResponse()->setBody(
                $this->getLayout()
                    ->createBlock($this->_failureBlockType)
                    ->setOrder($this->_order)
                    ->toHtml()
            );
        }
    }

    /**
     * gwecpay6 return action
     */
    public function successAction()
    {
        try {
            $session = $this->_getCheckout();
            $session->unsEcpay6RealOrderId();
            $session->setQuoteId($session->getEcpay6QuoteId(true));
            $session->setLastSuccessQuoteId($session->getEcpay6SuccessQuoteId(true));
            $this->_redirect('checkout/onepage/success');
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckout()->addError($e->getMessage());
        } catch(Exception $e) {
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart');
    }
	
    /**
     * gwecpay6 return action
     */
    public function cancelAction()
    {
        // set quote to active
        $session = $this->_getCheckout();
        if ($quoteId = $session->getEcpay6QuoteId()) {
            $quote = Mage::getModel('sales/quote')->load($quoteId);
            if ($quote->getId()) {
                $quote->setIsActive(true)->save();
                $session->setQuoteId($quoteId);
            }
        }
        $session->addError(Mage::helper('gwecpay6')->__('The order has been canceled.'));
        $this->_redirect('checkout/cart');
    }


    /**
     * Checking POST variables.
     * Creating invoice if payment was successfull or cancel order if payment was declined
     */
    protected function _checkReturnedPost()
    {	
            // check request type
        if (!$this->getRequest()->isPost())
            Mage::throwException('Wrong request type.');

            // validate request ip coming from gwecpay6/RBS subnet
        $helper = Mage::helper('core/http');
        if (method_exists($helper, 'getRemoteAddr')) {
            $remoteAddr = $helper->getRemoteAddr();
        } else {
            $request = $this->getRequest()->getServer();
            $remoteAddr = $request['REMOTE_ADDR'];
        }
        /*if (substr($remoteAddr,0,11) != '155.136.16.') {
            Mage::throwException('IP can\'t be validated as gwecpay6-IP.');
        }*/

            // get request variables
        $request = $this->getRequest()->getPost();
		//print_r($request);	//觀看request值用
        if (empty($request))
            Mage::throwException('Request doesn\'t contain POST elements.');
			//Check transaction 
		//驗證繳款完成通知是否由綠界傳來
		$check_sum= $key = Mage::getModel('Gw_Gwecpay6_Model_Ecpay6')->getConfigData('check_code');	//在controller須先呼叫model cvs後才能使用getConfigData()
		$incom_check=$this->gwSpcheck($request[process_time],$request[gwsr],$request[amount],$request[spcheck],$check_sum);
		if($incom_check != '1'){
			Mage::throwException('非合法交易!!!');
		}
		if ($request['succ'] !=1)
			 Mage::throwException('Transaction failed.');
            // check order id
        if (empty($request['od_sob']) || strlen($request['od_sob']) > 50)
            Mage::throwException('Missing or invalid order ID');

            // load order for further validation
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($request['od_sob']);
        if (!$this->_order->getId())
            Mage::throwException('Order not found');
        $this->_paymentInst = $this->_order->getPayment()->getMethodInstance();
        return $request;
    }

    /**
     * Process success response
     */
    protected function _processSale($request)
    {
        // check transaction amount and currency
        if ($this->_paymentInst->getConfigData('use_store_currency')) {
            $price      = number_format($this->_order->getGrandTotal(),0,'.','');
            $currency   = $this->_order->getOrderCurrencyCode();
        } else {
            $price      = number_format($this->_order->getBaseGrandTotal(),0,'.','');
            $currency   = $this->_order->getBaseCurrencyCode();
        }
        // check transaction amount
        if ($price != $request['amount'])
            Mage::throwException('Transaction currency doesn\'t match.');

        // check transaction currency		檢查貨幣單位
        //if ($currency != $request['amt'])		
        //   Mage::throwException('Transaction currency doesn\'t match.');

        // save transaction information
        $this->_order->getPayment()
        	->setTransactionId($request['transId'])
        	->setLastTransId($request['transId'])
        	->setCcAvsStatus($request['AVS'])
        	->setCcType($request['cardType']);
        if ($this->_order->canInvoice()) {					//產生發票
            $invoice = $this->_order->prepareInvoice();
            $invoice->register()->capture();
			$invoice->sendEmail(); 							//將發票E-mail給客戶	
            Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
        }
        $this->_order->addStatusToHistory($this->_paymentInst->getConfigData('order_status'), Mage::helper('gwecpay6')->__('繳費完成。'));
        $this->_order->save();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock($this->_successBlockType)
                ->setOrder($this->_order)
                ->toHtml()
        );
    }

    /**
     * Process success response
     */
    protected function _processCancel($request)
    {
        // cancel order
        if ($this->_order->canCancel()) {
            $this->_order->cancel();
            $this->_order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper('gwecpay6')->__('Payment was canceled'));
            $this->_order->save();
        }

        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock($this->_cancelBlockType)
                ->setOrder($this->_order)
                ->toHtml()
        );
    }

    protected function _getPendingPaymentStatus()
    {
        return Mage::helper('gwecpay6')->getPendingPaymentStatus();
    }
	
	//取消訂單
	protected function _cancelByError($message){
		$session = $this->_getCheckout();
		$order = Mage::getModel('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());
		$order->setState(
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage::helper('gwecpay6')->__($message)
        )->save();
		Mage::throwException($message);
	}
	
	//檢核函式
	protected function gwSpcheck($process_time,$gwsr,$amount,$spcheck,$check_sum) {    

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