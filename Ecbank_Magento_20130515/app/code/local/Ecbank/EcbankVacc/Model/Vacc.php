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


class Ecbank_EcbankVacc_Model_Vacc extends Mage_Payment_Model_Method_Abstract

{

	/**
	* unique internal payment method identifier
	*
	* @var string [a-z0-9_]
	**/
	protected $_code = 'ecbankvacc_vacc';

    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    protected $_paymentMethod			= 'vacc';
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
		$actionUrl = Mage::getUrl('ecbankvacc/processing/redirect');
		return $actionUrl;
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
	
    public function sendParams()	//將參數與網址組合並用背景方式發送
    {
		$value = $this->setParams();
                                      $gateWay = $this->_testUrl;
		$urlParams = 'mer_id='.$value['mer_id'].'&payment_type='.$value['payment_type'].'&setbank='.
		$value['setbank'].'&enc_key='.$value['enc_key'].'&od_sob='.$value['od_sob'].'&amt='.$value['amt'].
		'&expire_day='.$value['expire_day'].'&ok_url='.$value['ok_url'];
		// 以GET方式背景取號 (也可以使用curl)
		$strAuth = '';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$gateWay);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$urlParams);
		$strAuth = curl_exec($ch);
		if (curl_errno($ch)) { print_r($strAuth); $strAuth = false;  exit; }
		curl_close($ch);

		// 分解字串
		parse_str($strAuth, $res);

		return $res;
    }

    public function getAdminUrl()
    {
    	if ($this->getConfigData('transaction_mode') == 'live')
    		return $this->_liveAdminUrl;
    	return $this->_testAdminUrl;
    }


    /**
     * prepare params array to send it to gateway page via POST
     *
     * @return array
     */
    public function setParams()
    {              
	    	// get transaction amount and currency
        if ($this->getConfigData('use_store_currency')) {
        	$price      = number_format($this->getOrder()->getGrandTotal(),2,'.','');
        	$currency   = $this->getOrder()->getOrderCurrencyCode();
    	} else {
        	$price      = number_format($this->getOrder()->getBaseGrandTotal(),2,'.','');
        	$currency   = $this->getOrder()->getBaseCurrencyCode();
    	}

		$billing	= $this->getOrder()->getBillingAddress();

 		$locale = explode('_', Mage::app()->getLocale()->getLocaleCode());
		if (is_array($locale) && !empty($locale))
			$locale = $locale[0];
		else
			$locale = $this->getDefaultLocale();

		$expiration = $this->getConfigData('expiration');
		if($expiration > 60)
			$expiration =60;
    	$params = 	array(
						'mer_id'		=>	$this->getConfigData('shop_number'),
						'payment_type'	=>	$this->getPaymentMethodType(),
						'setbank'		=>	'ESUN',
						'enc_key'		=>	$this->getConfigData('private_key'),
						'od_sob'		=>	$this->getOrder()->getRealOrderId(),
						'amt'			=>	round($price),
						'expire_day'	=>	$expiration,
						'ok_url'		=>  Mage::getUrl('ecbankvacc/processing/response')
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
				Mage::throwException($this->_getHelper()->__('vacc API failure. The request has not been processed.'));
			// create array out of response

		} catch (Exception $e) {
            Mage::log($e->getMessage());
			Mage::throwException($this->_getHelper()->__('vacc API connection error. The request has not been processed.'));
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