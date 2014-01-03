<?php

class GreenWorld_VaccValidationModuleFrontController extends ModuleFrontController
{
	public function postProcess()
	{
		$cart = $this->context->cart;

                $this->display_column_left = true;
                $this->display_column_right = false;
                
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'greenworld_vacc')
			{
				$authorized = true;
				break;
			}

		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));

		$customer = new Customer($cart->id_customer);
                $currency = $this->context->currency;
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
                $inttotal=(round($total));
               
                
		$this->module->validateOrder((int)$cart->id, 1, $inttotal, $this->module->displayName, null, array(), null, (int)$currency->id, $customer->secure_key);
                if($total!=(float)$inttotal){
                    Db::getInstance()->execute('
                        UPDATE `'._DB_PREFIX_.'orders`
                        SET `total_paid` = '.$inttotal.', total_paid_tax_incl='.$inttotal.'
                        WHERE `id_order` = '.$this->module->currentOrder);
                }

                $mer_id=Configuration::get('gw_vacc_mer_id');
                $encryption_code=Configuration::get('gw_vacc_encryption');
                $PostData="";
                $PostData.="mer_id=$mer_id";
                $PostData.="&enc_key=$encryption_code";
                $PostData.="&expire_day=7";
				$PostData.="&setbank=ESUN";
                $PostData.="&payment_type=vacc"; 
                $PostData.="&amt=".$inttotal;
                $PostData.="&od_sob=".$this->module->currentOrder;

                $PostData.="&ok_url=".rawurlencode(Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/doFictitiousDetonate.php');
                //$PostData.="&ok_url=".rawurlencode("http://".$_SERVER["HTTP_HOST"].$CheckPay->path."doFictitiousDetonate.php");
               
			    
                // 建立CURL連線
                $ch = curl_init();
                // 設定擷取的URL網址
                curl_setopt($ch, CURLOPT_URL, "https://ecbank.com.tw/gateway.php?");
                curl_setopt($ch, CURLOPT_HEADER, false);
                //將curl_exec()獲取的訊息以文件流的形式返回，而不是直接輸出。
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                //設定CURLOPT_POST 為 1或true，表示要用POST方式傳遞
                curl_setopt($ch, CURLOPT_POST, 0); 
                //CURLOPT_POSTFIELDS 後面則是要傳接的POST資料。
                curl_setopt($ch, CURLOPT_POSTFIELDS, $PostData);
                // 執行
                $strAuth=curl_exec($ch);
                // 關閉CURL連線
                curl_close($ch);
                parse_str($strAuth, $res);;
				
				print_r($PostData);
				
				
                if(!isset($res['error']) || $res['error'] != '0'){

                    $this->context->smarty->assign(array(
                            'error_code' => $res['error'],
                    ));
        
                    die(Tools::displayError('This payment method is not available.'));
                    //echo Module::display('greenworld','payErrorPage.tpl');
                }else {

                    
                    $finishURL='index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key;
                    $finishURL.="&tsr=".$res['tsr'];
                    $finishURL.="&mer_id=".$res['mer_id'];
                    $finishURL.="&expire_date=".$res['expire_date'];
					$finishURL.="&vaccno=".$res['vaccno'];
					$finishURL.="&bankcode=".$res['bankcode'];
					$finishURL.="&amt=".$inttotal;
					$finishURL.="&od_sob=".$this->module->currentOrder;

                    //Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
                    Tools::redirectLink($finishURL);
					
					
                    //echo Module::display('greenworld','thankyouPage.tpl');
                }
                
                //echo 'index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key;
                //Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);

	}
}

?>
