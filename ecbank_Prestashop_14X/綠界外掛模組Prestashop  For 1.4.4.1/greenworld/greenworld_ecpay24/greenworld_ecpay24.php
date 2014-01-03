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
define("_iMODULE_NAME_ECPAY_24_","greenworld_ecpay24"); //設定 Class的name。
define("_iMODULE_CHINESE_NAME_ECPAY_24_","綠界－線上分24期刷卡繳費"); 
define("_iMODULE_DESCRUPTION_ECPAY_24_","提供管理者在Prestashop網站上，使用綠界科技的線上分24期刷卡繳費"); 
define("_iMODULE_UNINSTALL_MESSAGE_ECPAY_24_","Are you sure you want to delete your details ?"); 
define("_iMODULE_FIRST_TITLE_ECPAY_24_","使用線上分24期刷卡繳費"); 
define("_iMODULE_VERSION_ECPAY_24_","1.0.0"); 
class greenworld_ecpay24 extends PaymentModule {
    private $_html='';
    private $_postErrors=array();
    private $_shop_code;
    private $BaseURL;       
    private $expireDay;                                       
    private $PaymentType;                                 
    private $setBank;  

    function __construct(){  
        $this->name = _iMODULE_NAME_ECPAY_24_;
        
        parent::__construct();
        $this->path=$this->_path;
        $this->tab = 'payments_gateways';  
        $this->version = _iMODULE_VERSION_ECPAY_24_;  
        $this->displayName = $this->l(_iMODULE_CHINESE_NAME_ECPAY_24_);  
        $this->description = $this->l(_iMODULE_DESCRUPTION_ECPAY_24_);
        $this->confirmUninstall = $this->l(_iMODULE_UNINSTALL_MESSAGE_ECPAY_24_);
        /*這段之後要補上，判斷 商店代號跟檢查碼 沒有的話給予提醒。
        
        if (empty(Configuration::get($this->name.'_encryption_code')) OR empty(Configuration::get($this->name.'_shop_code')) )
            $this->warning = $this->l('No currency set for this module');
       */
        $this->BaseURL="https://ecpay.com.tw/form_Sc_to5_fn.php";         
        $this->PaymentType="auth";                               //設定訂單付費方式                                      //設定訂單銀行

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
                    EcPay商店代號：<input type="text" name="shop_code" value="'.$this->_shop_code.'" /><br />
                    EcPay商店檢查碼：<input type="text" name="shop_check_code" value="'.$this->_shop_check_code.'" /><br />
                    24期利率：<input type="text" name="24_percentage" value="'.$this->_24_percent.'" size="10" /><br />
                    分期利率只需輸入整數，如銀行告知收取5%，那您只需輸入5即可。<BR/>
                    目前提供分期只有外加的部分，假設客戶購買1000元商品，您所設定之利率為5。<BR/>
                    那最終將跟客戶收取1005元。<BR/>
                    <input type="submit" name="submit" value="'.$this->l('Save Setting').'" class="button" />  
                 </div>
        </form>';
    }  
    
    public function getContent(){
        if (Tools::isSubmit('submit')){  
            if(!sizeof ($this->_postErrors)){
                $t=getdate();
                $date=$t["year"]."-".$t["mon"]."-".$t["mday"]."(".$t["hours"].":".$t["minutes"].":".$t["seconds"].")";
                Configuration::updateValue($this->name.'_shop_code', Tools::getValue('shop_code'),true);
                Configuration::updateValue($this->name.'_shop_check', Tools::getValue('shop_check_code'),true);
                Configuration::updateValue($this->name.'_24_percent', Tools::getValue('24_percentage'),true);
                $this->_html.='<div class="conf confirm">'.$this->l('Success Updata')."   ".$date.'</div>';
            }else{
                foreach ($this->_postErrors AS $err){
                    //$this->_html.='<div class"alert error">'.$this->name.'_postErrors'."</div>";
                }
            }
        } 
        //如果Configuration資料庫已經有greenworld_vacc_encryption_code 與 greenworld_vacc_shop_code
        //的值，則直接抓取出來資料並顯示。
        if(!Configuration::get($this->name.'_shop_code')==null){
            $this->_shop_code=Configuration::get($this->name.'_shop_code');
        }else{
            $this->_shop_code='Enter Shop Code';
        }
        if(!Configuration::get($this->name.'_24_percent')==null){
            $this->_24_percent=Configuration::get($this->name.'_24_percent');
        }else{
            $this->_24_percent='Enter Percentage Code';
        }
        if(!Configuration::get($this->name.'_shop_check')==null){
            $this->_shop_check_code=Configuration::get($this->name.'_shop_check');
        }else{
            $this->_shop_check_code='Enter Shop Check Code';
        }
        $this->_displayForm();

        return $this->_html;  
    }
    
     public function hookPayment($params){
        global $smarty;
        $smarty->assign('payment',$this->l(_iMODULE_FIRST_TITLE_ECPAY_24_));    
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
        //是否交易成功
        if($_POST["succ"]=="1"){
            $checkStep1="1";
        }
        //驗證
        $checkPrice=new Order($_REQUEST['od_sob']);
        $systemPrice=$checkPrice->getTotalProductsWithTaxes();
        $systemPrice=round($systemPrice);
        if($systemPrice==$_POST["amount"]){
            $checkStep2="1";
        }
        
        $check_sum=$this->getShopCheckCode(); //這就是登入後台所看到的檢核碼
        $incom_check=$this->gwSpcheck($_POST[process_time],$_POST[gwsr],$_POST[amount],$_POST[spcheck],$check_sum);
        if($incom_check=='1' && $checkStep1=="1" && $checkStep2=="1") {
            $id_order=$_REQUEST['od_sob'];
            $newOrderStatusId=2;
            $history = new OrderHistory();
            $history->id_order = (int)($id_order);
            $history->changeIdOrderState($newOrderStatusId,$id_order );
            $history->addWithemail();
            global $smarty;
            $smarty->assign(array(
                'id_order'  =>  $_REQUEST["od_sob"],
                'gwsr'      =>  $_REQUEST["gwsr"],
                'stast'     =>  $_REQUEST["stast"],
                'staed'     =>  $_REQUEST["staed"],
                'stage'     =>  $_REQUEST["stage"],
                'amount'    =>  $_REQUEST["amount"],
            ));
            return $this->display(__FILE__, 'thankyouPage.tpl');
        } else {
            return $this->display(__FILE__, 'tradefail.tpl');
        }

    /*
     * 

        // 商店設定在ECBank管理後台的交易加密私鑰
        $key = $this->getShopCheckCode();
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
     * 
     */
    }
    // 信用卡稽核函數。
    private function gwSpcheck($process_time,$gwsr,$amount,$spcheck,$check_sum) {    

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
    public function getShopCheckCode(){
        $tempReturn="";
        if(Configuration::get($this->name.'_shop_check') ){
            $tempReturn=Configuration::get($this->name.'_shop_check');
        }else{
            $tempReturn="Error_Shop_Check_Code";
        }
        return $tempReturn;
    }
    public function get24Percentage(){
        $tempReturn="";
        if(Configuration::get($this->name.'_24_percent') ){
            $tempReturn=Configuration::get($this->name.'_24_percent');
        }else{
            $tempReturn="Error_Shop_Check_Code";
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

