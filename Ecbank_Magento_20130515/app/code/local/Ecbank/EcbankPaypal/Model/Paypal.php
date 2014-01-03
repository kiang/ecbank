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


class Ecbank_EcbankPaypal_Model_Paypal extends Mage_Payment_Model_Method_Abstract

{

	/**
	* unique internal payment method identifier
	*
	* @var string [a-z0-9_]
	**/
	protected $_code = 'ecbankpaypal_paypal';

    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    protected $_paymentMethod			= 'paypal';
	protected $_testUrl	= 'https://ecbank.com.tw/gateway.php';
    protected $_order;

    /**
     * Get order model
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
		if (!$this->_order) {
			$this->_order = $this->getInfoInstance()->getOrder();
		}
		return $this->_order;
    }

    public function getOrderPlaceRedirectUrl()
    {
          return Mage::getUrl('ecbankpaypal/processing/redirect');
    }

    /**
     * Return payment method type string
     *
     * @return string
     */
    public function getPaymentMethodType()
    {
        return $this->_paymentMethod;
    }

    public function getUrl()
    {
    	return $this->_testUrl;
    }

    /**
     * prepare params array to send it to gateway page via POST
     *
     * @return array
     */
    public function getFormFields()
    {
	    	// get transaction amount and currency
        if ($this->getConfigData('use_store_currency')) {
        	$price      = number_format($this->getOrder()->getGrandTotal(),2,'.','');
    	} else {
        	$price      = number_format($this->getOrder()->getBaseGrandTotal(),2,'.','');
    	}

		$billing	= $this->getOrder()->getBillingAddress();

 		$locale = explode('_', Mage::app()->getLocale()->getLocaleCode());
		if (is_array($locale) && !empty($locale))
			$locale = $locale[0];
		else
			$locale = $this->getDefaultLocale();

		$currency = $this->getConfigData('set_currency');
		$currency = strtoupper($currency);
		//$orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();	//抓取此筆訂單ID
		$orderItems = $this->getOrder()->getItemsCollection(); 		//抓取商品資訊集合
		foreach ($orderItems as $item){
			$productId = $item->product_id;	//抓取該商品之ID
			//$productSku = $item->sku;   //抓取商品SKU
			$productName = $item->getName(); //抓取訂單商品名稱
			//以商品之ID來抓取該商品的描述、所在目錄等細節資訊
			$_product = Mage::getModel('catalog/product')->load($productId); 
			$productDes = $_product->getShortDescription(); 	//抓取商品描述
			/*=====================================未使用================================================
			//$cats = $_product->getCategoryIds();	//抓取目錄ID
			//$categoryId = $cats[0]; // 只需抓取第一個ID 
			$category = Mage::getModel('catalog/category')->load($category_id);	//以目錄ID抓取目錄資訊
			$category_name = $category->getName();   	//目錄名稱
			=====================================未使用================================================*/
			
		}
    	$params = 	array(
						'mer_id'		=>	$this->getConfigData('shop_number'),
						'payment_type'	=>	$this->getPaymentMethodType(),
						'od_sob'		=>	$this->getOrder()->getRealOrderId(),
						'item_name'		=>	$productName,
						'item_desc'		=>	$productDes,
						'cur_type'		=>	$currency,
						'amt'			=>	round($price),
						'cancel_url'	=>	Mage::getUrl('ecbankpaypal/processing/cancel'),
						'return_url'	=> 	Mage::getUrl('ecbankpaypal/processing/response')
    				);
    	return $params;
    }

    /**
     * Refund money
     *
     * @param   Varien_Object $invoicePayment
     * @return  Mage_GoogleCheckout_Model_Payment
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $transactionId = $payment->getLastTransId();
        $params = $this->_prepareAdminRequestParams();

        $params['cartId']   = 'Refund';
        $params['op']       = 'refund-partial';
        $params['transId']  = $transactionId;
        $params['amount']   = $amount;
        $params['currency'] = $payment->getOrder()->getBaseCurrencyCode();

        $responseBody = $this->processAdminRequest($params);
        $response = explode(',', $responseBody);
        if (count($response) <= 0 || $response[0] != 'A' || $response[1] != $transactionId) {
            Mage::throwException($this->_getHelper()->__('Error during refunding online. Server response: %s', $responseBody));
        }
        return $this;
    }

    /**
     * Capture preatutharized amount
     * @param Varien_Object $payment
     * @param <type> $amount
     */
	public function capture(Varien_Object $payment, $amount)
	{
        if (!$this->canCapture()) {
            return $this;
        }

        if (Mage::app()->getRequest()->getParam('transId')) {
            // Capture is called from response action
            $payment->setStatus(self::STATUS_APPROVED);
            return $this;
        }
        $transactionId = $payment->getLastTransId();
        $params = $this->_prepareAdminRequestParams();
        $params['transId']  = $transactionId;
        $params['authMode'] = '0';
        $params['op']       = 'postAuth-full';
    }


    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canRefund ()
    {
        return $this->getConfigData('enable_online_operations');
    }

    public function canRefundInvoicePartial()
    {
        return $this->getConfigData('enable_online_operations');
    }

    public function canRefundPartialPerInvoice()
    {
        return $this->canRefundInvoicePartial();
    }

    public function canCapturePartial()
    {
        if (Mage::app()->getFrontController()->getAction()->getFullActionName() != 'adminhtml_sales_order_creditmemo_new'){
            return false;
        }
        return $this->getConfigData('enable_online_operations');
    }

	protected function processAdminRequest($params, $requestTimeout = 60)
	{
		try {
			$client = new Varien_Http_Client();
			$client->setUri($this->getAdminUrl())
				->setConfig(array('timeout'=>$requestTimeout,))
				->setParameterPost($params)
				->setMethod(Zend_Http_Client::POST);

			$response = $client->request();
			$responseBody = $response->getBody();

			if (empty($responseBody))
				Mage::throwException($this->_getHelper()->__('paypal API failure. The request has not been processed.'));
			// create array out of response

		} catch (Exception $e) {
            Mage::log($e->getMessage());
			Mage::throwException($this->_getHelper()->__('paypal API connection error. The request has not been processed.'));
		}

		return $responseBody;
	}

    protected function _prepareAdminRequestParams()
    {
        $params = array (
            'authPW'   => $this->getConfigData('auth_password'),
            'instId'   => $this->getConfigData('admin_inst_id'),
        );
        if ($this->getConfigData('transaction_mode') == 'test') {
            $params['testMode'] = 100;
        }
        return $params;
    }
}