<?php

return array(
    'code'      => 'ecpay_unionpay',
    'name'      => Lang::get('ecpay_unionpay'),
    'desc'      => Lang::get('ecpay_unionpay_desc'),
    'is_online' => '1',
    'author'    => 'GreenWorld',
    'website'   => 'http://www.greenworld.com.tw',
    'version'   => '1.0',
    'currency'  => Lang::get('ecpay_unionpay_currency'),
    'config'    => array(
 	   'ecpay_unionpay_account'   => array(        //綠界商店代號
            'text'  => Lang::get('ecpay_unionpay_account'),
            'desc'  => Lang::get('ecpay_unionpay_account_desc'),
            'type'  => 'text',
        ),    
        'ecpay_unionpay_key'       => array(        //檢查碼
        'text'  => Lang::get('ecpay_unionpay_key'),
            'type'  => 'text',
        ),
         'ecpay_unionpay_i_invoice' =>array(
            'text'  => Lang::get('ecpay_unionpay_i_invoice'),
            //'desc'  => Lang::get('_ecpay_unionpay_i_invoice_desc'),
            'type'  => 'radio',
            'name'  => 'config[ecpay_unionpay_i_invoice]',
            'items' => array(yes => 'yes' ,no => 'no'  ), 
        ),
         'ecpay_unionpay_imer_id' =>array(
            'text'  => Lang::get('ecpay_unionpay_imer_id'),
            'desc'  => Lang::get('ecpay_unionpay_imer_id_desc'),
            'type'  => 'text',
        ),
    ),
);

?>