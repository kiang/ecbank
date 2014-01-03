<?php

class Ecbank_Alipay_Block_Test extends Mage_Core_Block_Template{

	public function getFormAction()
    {
        return $this->_getOrder()->getPayment()->getMethodInstance()->getUrl();
    }

}

?>