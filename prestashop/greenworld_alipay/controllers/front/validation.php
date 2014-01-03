<?php

session_start();

class GreenWorld_AlipayValidationModuleFrontController extends ModuleFrontController {

    public function postProcess() {
        $cart = $this->context->cart;
       
        $this->display_column_left = true;
        $this->display_column_right = false;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
            Tools::redirect('index.php?controller=order&step=1');

        $authorized = false;
        foreach (Module::getPaymentModules() as $module)
            if ($module['name'] == 'greenworld_alipay') {
                $authorized = true;
                break;
            }

        if (!$authorized)
            die($this->module->l('This payment method is not available.', 'validation'));

        $customer = new Customer($cart->id_customer);
        $currency = $this->context->currency;
        if (!Validate::isLoadedObject($customer))
            Tools::redirect('index.php?controller=order&step=1');

        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $inttotal = (round($total));

        $return_url = rawurlencode(Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/doFictitiousDetonate.php');


        
        
        //商品上傳
        
        $type = 'upload_goods';
        $goods_href = 'http://';
        $ecbank_gateway = 'https://ecbank.com.tw/web_service/alipay_goods_upload.php';
        $mer_id=Configuration::get('gw_alipay_mer_id');
        $encryption_code=Configuration::get('gw_alipay_encryption');
        
        $product_name = array();
        $product_qty = array();
        foreach($cart->getProducts() as $product => $value ){
           
                $product_name[] = $value['id_product'];
                $product_qty[] =  $value['quantity'];
           	$post_str='enc_key='.$encryption_code.
                          '&mer_id='.$mer_id.
                          '&type='.$type.
                          '&goods_id='.$value['id_product'].
                          '&goods_title='.$value['name'].
                          '&goods_price='.intval($value['price']).
                          '&goods_href='.urlencode($goods_href);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$ecbank_gateway);
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch,CURLOPT_POSTFIELDS,$post_str);
                $strAuth = curl_exec($ch);
            
        }
        curl_close($ch);
        
        $_SESSION["name"] = $product_name;
        
        $_SESSION["quantity"] = $product_qty ;
        
       
        
        $this->module->validateOrder((int) $cart->id, 1, $inttotal, $this->module->displayName, null, array(), null, (int) $currency->id, $customer->secure_key);
        if ($total != (float) $inttotal) {
            Db::getInstance()->execute('
                        UPDATE `' . _DB_PREFIX_ . 'orders`
                        SET `total_paid` = ' . $inttotal . ', total_paid_tax_incl=' . $inttotal . '
                        WHERE `id_order` = ' . $this->module->currentOrder);
        }
        $finishURL = 'index.php?controller=order-confirmation&id_cart=' . (int) $cart->id . '&id_module=' . (int) $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key . '&amt=' . $inttotal . '&return_url=' . $return_url . '.php&mer_id=' . Configuration::get('gw_paypal_mer_id');

        //  $finishURL='index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&amt='.$inttotal.'&return_url='.urlencode($return_url).'&mer_id='.Configuration::get('gw_webatm_mer_id');
        //Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);

        Tools::redirectLink($finishURL);

        //echo Module::display('greenworld','thankyouPage.tpl');
        // }
        //echo 'index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key;
        Tools::redirect('index.php?controller=order-confirmation&id_cart=' . (int) $cart->id . '&id_module=' . (int) $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key);
    }

}

?>
