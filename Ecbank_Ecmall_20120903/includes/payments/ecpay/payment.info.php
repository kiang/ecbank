<?php

return array(
    'code'      => 'ecpay',
    'name'      => Lang::get('ecpay'),
    'desc'      => Lang::get('ecpay_desc'),
    'is_online' => '1',
    'author'    => 'GreenWorld',
    'website'   => 'http://www.greenworld.com.tw',
    'version'   => '1.0',
    'currency'  => Lang::get('ecpay_currency'),
    'config'    => array(
 	   'ecpay_account'   => array(        //綠界商店代號
            'text'  => Lang::get('ecpay_account'),
            'desc'  => Lang::get('ecpay_account_desc'),
            'type'  => 'text',
        ),    
        'ecpay_key'       => array(        //檢查碼
        'text'  => Lang::get('ecpay_key'),
            'type'  => 'text',
        ),
              'ecpay_i_invoice' =>array(
            'text'  => Lang::get('ecpay_i_invoice'),
            //'desc'  => Lang::get('ecpay_i_invoice_desc'),
            'type'  => 'radio',
            'name'  => 'config[ecpay_i_invoice]',
            'items' => array(yes => 'yes' ,no => 'no'  ), 
        ),
        'ecpay_imer_id' =>array(
            'text'  => Lang::get('ecpay_imer_id'),
            'desc'  => Lang::get('ecpay_imer_id_desc'),
            'type'  => 'text',
        ),
    ),
);

?>