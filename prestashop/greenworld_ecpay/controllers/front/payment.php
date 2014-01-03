<?php

class GreenWorld_EcpayPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
                $this->display_column_left = true;
                $this->display_column_right = false;
		
               
		parent::initContent();

		$cart = $this->context->cart;
                $total = $cart->getOrderTotal(true, Cart::BOTH);
                $inttotal=round($total);
                

		$this->context->smarty->assign(array(
                        'total' => $total ,
                        'inttotal'=> $inttotal,
		));

		$this->setTemplate('validation.tpl');
	}
}
?>
