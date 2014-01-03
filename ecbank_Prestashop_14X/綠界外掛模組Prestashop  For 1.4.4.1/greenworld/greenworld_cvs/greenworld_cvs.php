<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of greenworld
 *
 * @author 李泓承 
 */
define("_iMODULE_NAME_CVS_","greenworld_cvs"); //設定 Class的name。
define("_iMODULE_CHINESE_NAME_CVS_","綠界－超商代碼繳費"); 
define("_iMODULE_DESCRUPTION_CVS_","提供管理者在Prestashop網站上，使用綠界科技的超商代碼繳費"); 
define("_iMODULE_UNINSTALL_MESSAGE_CVS_","Are you sure you want to delete your details ?"); 
define("_iMODULE_FIRST_TITLE_CVS_","使用超商代碼繳費"); 
define("_iMODULE_VERSION_CVS_","1.0.0"); 
class greenworld_cvs extends PaymentModule {
    private $_html='';
    private $_postErrors=array();
    private $_encryption_code;
    private $_shop_code;
    private $BaseURL;       
    private $expireDay;                                       
    private $PaymentType;                                 
    private $setBank;  

    function __construct(){  
        $this->name = _iMODULE_NAME_CVS_;
        
        parent::__construct();
        $this->path=$this->_path;
        $this->tab = 'payments_gateways';  
        $this->version = _iMODULE_VERSION_CVS_;  
        $this->displayName = $this->l(_iMODULE_CHINESE_NAME_CVS_);  
        $this->description = $this->l(_iMODULE_DESCRUPTION_CVS_);
        $this->confirmUninstall = $this->l(_iMODULE_UNINSTALL_MESSAGE_CVS_);
        /*這段之後要補上，判斷 商店代號跟檢查碼 沒有的話給予提醒。
        
        if (empty(Configuration::get($this->name.'_encryption_code')) OR empty(Configuration::get($this->name.'_shop_code')) )
            $this->warning = $this->l('No currency set for this module');
       */
        
        $this->BaseURL="https://ecbank.com.tw/gateway.php?";         
        $this->expireDay="7";                                       //設定訂單有效時間
        $this->PaymentType="cvs";                                  //設定訂單付費方式
        $this->Prd_desc=$smarty["shop_name"];                                      //設定訂單銀行
        //$this->EncryptionCode=$this->getEncryptionCode();           //在Prestashop中抓取交易加密私鑰 。
        //$this->ShopCode=$this->getShopCode();                       //在Prestashop中抓取商店代碼。
    }

    public function install(){
        //針對perstashop 的內定Hook 進行註冊 payment 跟 paymentReturn
        if (!parent::install() 
                OR !$this->registerHook('payment')

                OR !$this->registerHook('paymentReturn')
                )
            return false;

    }
    
	public function uninstall(){
        if (!Configuration::deleteByName($this->name.'_shop_code')
            OR !Configuration::deleteByName($this->name.'_encryption_code')
            OR !parent::uninstall())
            return false;
    }
	
	public function getAmount($order_id){
       
        $amount =    Db::getInstance()->execute('
                        select total_paid from  `'._DB_PREFIX_.'orders`
                        WHERE `id_order` = '.$order_id);
		
        
        return $amount;
                
    }
	

    private function _displayForm(){  
        $this->_html .= '  
        <form action="'.$_SERVER['REQUEST_URI'].'" method="post">  
                <div class="margin-form">  
                    商店代號：<input type="text" name="shop_code" value="'.$this->_shop_code.'" /><br />
                    加密金要：<input type="text" name="encryption_code" value="'.$this->_encryption_code.'" /><br />
                    顯示在超商代碼繳費的商店名稱：<input type="text" name="prd_desc" value="'.$this->_prd_desc.'" />
                </div>  
                <input type="submit" name="submit" value="'.$this->l('Save Setting').'" class="button" />  
        </form>';
    }  
    
    public function getContent(){
        if (Tools::isSubmit('submit')){  
            if(!sizeof ($this->_postErrors)){
                $t=getdate();
                $date=$t["year"]."-".$t["mon"]."-".$t["mday"]."(".$t["hours"].":".$t["minutes"].":".$t["seconds"].")";
                Configuration::updateValue($this->name.'_shop_code', Tools::getValue('shop_code'),true);
                Configuration::updateValue($this->name.'_encryption_code', Tools::getValue('encryption_code'),true);
                Configuration::updateValue($this->name.'_prd_desc', Tools::getValue('prd_desc'),true);
                $this->_html.='<div class="conf confirm">'.$this->l('Success Updata')."   ".$date.'</div>';
            }else{
                foreach ($this->_postErrors AS $err){
                    $this->_html.='<div class"alert error">'.$err."</div>";
                }
            }
        } 
        //如果Configuration資料庫已經有greenworld_vacc_encryption_code 與 greenworld_vacc_shop_code
        //的值，則直接抓取出來資料並顯示。
        if(!Configuration::get($this->name.'_encryption_code')==null){
            $this->_encryption_code=Configuration::get($this->name.'_encryption_code');
        }else{
            $this->_encryption_code='Enter Encryption Code';
        }
        if(!Configuration::get($this->name.'_prd_desc')==null){
            $this->_prd_desc=Configuration::get($this->name.'_prd_desc');
        }else{
            $this->_prd_desc='Enter Shop Descript';
        }
        if(!Configuration::get($this->name.'_shop_code')==null){
            $this->_shop_code=Configuration::get($this->name.'_shop_code');
        }else{
            $this->_shop_code='Enter Shop Code';
        }
        $this->_displayForm();

        return $this->_html;  
    }
     public function hookPayment($params){
        global $smarty;
        $smarty->assign('payment',$this->l(_iMODULE_FIRST_TITLE_CVS_));    
	$smarty->assign(array('this_path' => $this->_path));
        
        foreach ($params['cart']->getProducts() AS $product)
        {
                $pd = ProductDownload::getIdFromIdProduct((int)($product['id_product']));
                if ($pd AND Validate::isUnsignedInt($pd))
                        return false;
        }
	return $this->display(__FILE__, 'greenworld.tpl');
        

     }
     public function hookPaymentReturn($params){
        if (!$this->active)
            return ;

        global $smarty;
        $smarty->assign(array(
            'tsr' =>  Tools::getValue("tsr"),
            'payno' =>  Tools::getValue("payno"),
            'id_order' =>  Tools::getValue("id_order"),
	));
        return $this->display(__FILE__, 'thankyouPage.tpl');
         
     }
     
     public function createBaseURL(){
        //在串接碼產生。
        $this->returnURL = $this->BaseURL."mer_id=".$this->ShopCode."&enc_key=".$this->EncryptionCode."&expire_day=".$this->expireDay."&setbank=".$this->setBank."&payment_type=".$this->PaymentType;
        return $this->returnURL;
    }
    public function getBaseURL(){
        return $this->BaseURL;
    }
    public function getPaymentType(){
        return $this->PaymentType;
    }
    /*
    public function getTotlePrice(){
         $cart=new Cart((int)($cookie->id_cart));
         $total = $cart->getOrderTotal(true, Cart::BOTH);
         return $total; 
    }*/
    public function getEncryptionCode(){
        $tempReturn="";
        if(Configuration::get($this->name.'_encryption_code') ){
            $tempReturn=Configuration::get($this->name.'_encryption_code');
        }else{
            $tempReturn="Error_Encryption_Code";
        }
        return $tempReturn;
    }
    public function getPrdDesc(){
        $tempReturn="";
        if(Configuration::get($this->name.'_prd_desc') ){
            $tempReturn=Configuration::get($this->name.'_prd_desc');
        }else{
            $tempReturn="Error_Prd_desc";
        }
        return $tempReturn;
    }
    public function getShopCode(){
         $tempReturn="";
        if(Configuration::get($this->name.'_shop_code') ){
            $tempReturn= Configuration::get($this->name.'_shop_code');
        }else{
            $tempReturn="Error_Shop_Code";
        }
        return $tempReturn;        
    }
}  


// End of: greenworld.php  
?>  

