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
define("_iMODULE_NAME_ATM_","greenworld_webatm"); //設定 Class的name。
define("_iMODULE_CHINESE_NAME_ATM_","綠界－Web ATM繳費"); 
define("_iMODULE_DESCRUPTION_ATM_","提供管理者在Prestashop網站上，使用綠界科技的Web ATM繳費"); 
define("_iMODULE_UNINSTALL_MESSAGE_ATM_","Are you sure you want to delete your details ?"); 
define("_iMODULE_FIRST_TITLE_ATM_","使用線上Web ATM繳費"); 
define("_iMODULE_VERSION_ATM_","1.0.1"); 
class greenworld_webatm extends PaymentModule {
    private $_html='';
    private $_postErrors=array();
    private $_encryption_code;
    private $_shop_code;
    private $BaseURL;       
    private $expireDay;                                       
    private $PaymentType;                                 
    private $setBank;  

    function __construct(){  
        $this->name = _iMODULE_NAME_ATM_;
        
        parent::__construct();
        $this->path=$this->_path;
        $this->tab = 'payments_gateways';  
        $this->version = _iMODULE_VERSION_ATM_;  
        $this->displayName = $this->l(_iMODULE_CHINESE_NAME_ATM_);  
        $this->description = $this->l(_iMODULE_DESCRUPTION_ATM_);
        $this->confirmUninstall = $this->l(_iMODULE_UNINSTALL_MESSAGE__ATM);
        /*這段之後要補上，判斷 商店代號跟檢查碼 沒有的話給予提醒。
        
        if (empty(Configuration::get($this->name.'_encryption_code')) OR empty(Configuration::get($this->name.'_shop_code')) )
            $this->warning = $this->l('No currency set for this module');
       */
        $this->BaseURL="https://ecbank.com.tw/gateway.php?";         
        $this->PaymentType="web_atm";                               //設定訂單付費方式
        $this->setBank="ESUN";                                      //設定訂單銀行

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
                <label>'.$this->l('Message to the world').'</label> 
                <div class="margin-form">  
                    商店代號：<input type="text" name="shop_code" value="'.$this->_shop_code.'" /><br />
                    加密金要：<input type="text" name="encryption_code" value="'.$this->_encryption_code.'" />
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
                Configuration::updateValue($this->name.'_encryption_cod', Tools::getValue('encryption_code'),true);
                $this->_html.='<div class="conf confirm">'.$this->l('Success Updata')."   ".$date.'</div>';
            }else{
                foreach ($this->_postErrors AS $err){
                    $this->_html.='<div class"alert error">'.$this->name.'_encryption_code'."</div>";
                }
            }
        } 
        //如果Configuration資料庫已經有greenworld_vacc_encryption_code 與 greenworld_vacc_shop_code
        //的值，則直接抓取出來資料並顯示。
        if(!Configuration::get($this->name.'_encryption_cod')==null){
            $this->_encryption_code=Configuration::get($this->name.'_encryption_cod');
        }else{
            $this->_encryption_code='Enter Encryption Code';
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
        $smarty->assign('payment',$this->l(_iMODULE_FIRST_TITLE_ATM_));    
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
   
    
        // 商店設定在ECBank管理後台的交易加密私鑰
        $key = $this->getEncryptionCode();
        // 組合字串
        $serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr']);
        // 回傳的交易驗證壓碼
        $tac = trim($_REQUEST['tac']);
        $ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid.php?';
        $post_parm	='key='. $key.'&serial='.$serial.'&tac='.$tac;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$ecbank_gateway);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post_parm);
        $strAuth = curl_exec($ch);
        if (curl_errno($ch)){
                $strAuth = false;
        }
        curl_close($ch);
        if($strAuth == 'valid=1'){
                if($_REQUEST['succ']=='1') {
 
                    $id_order=$_REQUEST['od_sob'];
                    $newOrderStatusId=2;
                    $history = new OrderHistory();
                    $history->id_order = (int)($id_order);
                    $history->changeIdOrderState($newOrderStatusId,$id_order );
                    $history->addWithemail();
                    global $smarty;
                    $smarty->assign(array(
                        'tsr' =>  $_REQUEST['tsr'],
                        'id_order' =>  $_REQUEST["od_sob"],
                    ));
                    return $this->display(__FILE__, 'thankyouPage.tpl');
                }else{
                     
                        echo "Failure";
                }
        }else{
                echo "Illegal";
        }
    }
    
     public function createBaseURL(){
        //在串接碼產生。
        $this->returnURL = $this->BaseURL."mer_id=".$this->ShopCode."&enc_key=".$this->EncryptionCode."&expire_day=".$this->expireDay."&setbank=".$this->setBank."&payment_type=".$this->PaymentType;
        return $this->returnURL;
    }
    public function getBaseURL(){
        return $this->BaseURL;
    }
    public function getExpireDay(){
        return $this->expireDay;
    }
    public function getPaymentType(){
        return $this->PaymentType;
    }
    public function getSetBank(){
        return $this->setBank;
    }
    public function getTotlePrice(){
         $cart=new Cart((int)($cookie->id_cart));
         $total = $cart->getOrderTotal(true, Cart::BOTH);
         return $total; 
    }
    public function getEncryptionCode(){
        $tempReturn="";
        if(Configuration::get($this->name.'_encryption_cod') ){
            $tempReturn=Configuration::get($this->name.'_encryption_cod');
        }else{
            $tempReturn="Error_Encryption_Code";
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

