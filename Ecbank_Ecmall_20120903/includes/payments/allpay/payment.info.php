<?php

return array(
    'code'      => 'allpay',
    'name'      => Lang::get('allpay'),
    'desc'      => Lang::get('allpay_desc'),
    'is_online' => '1',
    'author'    => 'GreenWorld',
    'website'   => 'http://www.greenworld.com.tw',
    'version'   => '1.0',
    'currency'  => Lang::get('allpay_currency'),
    'config'    => array(
 	   'allpay_account'   => array(        //綠界商店代號
            'text'  => Lang::get('allpay_account'),
            'desc'  => Lang::get('allpay_account_desc'),
            'type'  => 'text',
        ),    
        'allpay_key'       => array(        //檢查碼
        'text'  => Lang::get('allpay_key'),
            'type'  => 'text',
        ),
        'allpay_i_invoice' =>array(
            'text'  => Lang::get('allpay_i_invoice'),
            //'desc'  => Lang::get('allpay_i_invoice_desc'),
            'type'  => 'radio',
            'name'  => 'config[allpay_i_invoice]',
            'items' => array(yes => 'yes' ,no => 'no'  ), 
        ),
        'allpay_imer_id' =>array(
            'text'  => Lang::get('allpay_imer_id'),
            'desc'  => Lang::get('allpay_imer_id_desc'),
            'type'  => 'text',
        ),  
    ),
);

?>