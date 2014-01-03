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
 * @package    Ecbank_EcbankAlipay
 * @copyright  Copyright (c) 2010 Ecbank (http://www.ecbank.com.tw)
 */

class Ecbank_EcbankAlipay_ProcessingController extends Mage_Core_Controller_Front_Action
{
    protected $_successBlockType = 'ecbankalipay/success';
    protected $_failureBlockType = 'ecbankalipay/failure';
    protected $_cancelBlockType = 'ecbankalipay/cancel';

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
     * when customer selects ecbankalipay payment method
     */
    public function redirectAction()
    {
        try {
            $session = $this->_getCheckout();
			$order = Mage::getModel('sales/order');	
            $order->loadByIncrementId($session->getLastRealOrderId());
			//判斷金額是否小於10，金額不符訂單將自動取消。
			$price  = $order['base_grand_total'];
			$priceTranslate = explode(".",$price);
			$price = $priceTranslate[0];
			if($price < 10){
				$message = '交易金額低於10元，訂單將自動取消。';
				$this->_cancelByError($message);
			}
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($session->getLastRealOrderId());
            if (!$order->getId()) {
                Mage::throwException('無訂單可供處理');
            }
            if ($order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                $order->setState(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    $this->_getPendingPaymentStatus(),
                    Mage::helper('ecbankalipay')->__('轉向至Ecbank中，請稍候...')
                )->save();
				$order->sendNewOrderEmail();
				$order->setEmailSent(true);
            }

            if ($session->getQuoteId() && $session->getLastSuccessQuoteId()) {
                $session->setAlipayQuoteId($session->getQuoteId());
                $session->setAlipaySuccessQuoteId($session->getLastSuccessQuoteId());
                $session->setAlipayRealOrderId($session->getLastRealOrderId());
                $session->getQuote()->setIsActive(false)->save();
				$test = $session->getQuoteId();
				$test2 = $session->setAlipayQuoteId($session->getQuoteId());
				//print_r($session);
				//exit;
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
     * ecbankalipay returns POST variables to this action
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
     * ecbankalipay return action
     */
    public function successAction()
    {
        try {
            $session = $this->_getCheckout();
            $session->unsAlipayRealOrderId();
            $session->setQuoteId($session->getAlipayQuoteId(true));
            $session->setLastSuccessQuoteId($session->getAlipaySuccessQuoteId(true));
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
     * ecbankalipay return action
     */
    public function cancelAction()
    {
        // set quote to active
        $session = $this->_getCheckout();
        if ($quoteId = $session->getAlipayQuoteId()) {
            $quote = Mage::getModel('sales/quote')->load($quoteId);
            if ($quote->getId()) {
                $quote->setIsActive(true)->save();
                $session->setQuoteId($quoteId);
            }
        }
        $session->addError(Mage::helper('ecbankalipay')->__('The order has been canceled.'));
        $this->_redirect('checkout/cart');
    }


    /**
     * Checking POST variables.
     * Creating invoice if payment was successfull or cancel order if payment was declined
     */
    protected function _checkReturnedPost()
    {	
            // check request type
        if (!$this->getRequest()->isPost()){
            Mage::throwException('Wrong request type.');
        }
            // validate request ip coming from ecbankalipay/RBS subnet
        $helper = Mage::helper('core/http');
        if (method_exists($helper, 'getRemoteAddr')) {  //this run************************
            $remoteAddr = $helper->getRemoteAddr();
        } else {
            $request = $this->getRequest()->getServer();
            $remoteAddr = $request['REMOTE_ADDR'];
        }
        /*if (substr($remoteAddr,0,11) != '155.136.16.') {
            Mage::throwException('IP can\'t be validated as ecbankalipay-IP.');
        }*/

            // get request variables
        $request = $this->getRequest()->getPost();
		//print_r($request);	//觀看request值用
        if (empty($request)){
            Mage::throwException('Request doesn\'t contain POST elements.');
        }
			//Check transaction
		//驗證繳款完成通知是否由綠界傳來
		$key = Mage::getModel('Ecbank_EcbankAlipay_Model_Cc')->getConfigData('private_key');	//在controller須先呼叫model cvs後才能使用getConfigData()
		$serial = trim($request['proc_date'].$request['proc_time'].$request['tsr'].$request['od_sob'].$request['amt']);
		$mac = trim($request['mac']);
		$ws_url = 'https://ecbank.com.tw/web_service/get_outmac_valid_new.php?key='.$key.
		          '&serial='.$serial.
		          '&mac='.$mac;
		$mac_valid = file_get_contents($ws_url);
		if($mac_valid != 'valid=1'){ 
			Mage::throwException('非合法交易!!!');
		}
		if ($request['succ'] !=1){
			 Mage::throwException('Transaction failed.');
                                        }
            // check order id
        if (empty($request['od_sob']) || strlen($request['od_sob']) > 50){
            Mage::throwException('Missing or invalid order ID');
        }
            // load order for further validation
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($request['od_sob']);
        if (!$this->_order->getId()){
            Mage::throwException('Order not found');
        }
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
        if ($price != $request['amt'])
            Mage::throwException('Transaction currency doesn\'t match.');
			
        if ($this->_order->canInvoice()) {					//產生發票
            $invoice = $this->_order->prepareInvoice();
            $invoice->register()->capture();	
			$invoice->sendEmail(); 							//將發票E-mail給客戶
            Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();
        }
        $this->_order->addStatusToHistory($this->_paymentInst->getConfigData('order_status'), Mage::helper('ecbankalipay')->__('繳費完成。'));
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
            $this->_order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper('ecbankalipay')->__('Payment was canceled'));
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
        return Mage::helper('ecbankalipay')->getPendingPaymentStatus();
    }
	
	//取消訂單
	protected function _cancelByError($message){
		$session = $this->_getCheckout();
		$order = Mage::getModel('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());
		$order->setState(
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage::helper('ecbankvacc')->__($message)
        )->save();
		Mage::throwException($message);
	}
}