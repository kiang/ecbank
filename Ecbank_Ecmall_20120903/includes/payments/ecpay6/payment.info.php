<?php

return array(
    'code'      => 'ecpay6',
    'name'      => Lang::get('ecpay6'),
    'desc'      => Lang::get('ecpay6_desc'),
    'is_online' => '1',
    'author'    => 'GreenWorld',
    'website'   => 'http://www.greenworld.com.tw',
    'version'   => '1.0',
    'currency'  => Lang::get('ecpay6_currency'),
    'config'    => array(
 	   'ecpay6_account'   => array(        //綠界商店代號
            'text'  => Lang::get('ecpay6_account'),
            'desc'  => Lang::get('ecpay6_account_desc'),
            'type'  => 'text',
        ),    
        'ecpay6_key'       => array(        //檢查碼
        'text'  => Lang::get('ecpay6_key'),
            'type'  => 'text',
        ),
        'ecpay6_i_invoice' =>array(
            'text'  => Lang::get('ecpay6_i_invoice'),
            //'desc'  => Lang::get('ecpay6_i_invoice_desc'),
            'type'  => 'radio',
            'name'  => 'config[ecpay6_i_invoice]',
            'items' => array(yes => 'yes' ,no => 'no'  ), 
        ),
        'ecpay6_imer_id' =>array(
            'text'  => Lang::get('ecpay6_imer_id'),
            'desc'  => Lang::get('ecpay6_imer_id_desc'),
            'type'  => 'text',
        ),   
    ),
);

?>