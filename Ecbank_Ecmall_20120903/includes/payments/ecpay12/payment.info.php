<?php

return array(
    'code'      => 'ecpay12',
    'name'      => Lang::get('ecpay12'),
    'desc'      => Lang::get('ecpay12_desc'),
    'is_online' => '1',
    'author'    => 'GreenWorld',
    'website'   => 'http://www.greenworld.com.tw',
    'version'   => '1.0',
    'currency'  => Lang::get('ecpay12_currency'),
    'config'    => array(
 	   'ecpay12_account'   => array(        //綠界商店代號
            'text'  => Lang::get('ecpay12_account'),
            'desc'  => Lang::get('ecpay12_account_desc'),
            'type'  => 'text',
        ),    
        'ecpay12_key'       => array(        //檢查碼
        'text'  => Lang::get('ecpay12_key'),
            'type'  => 'text',
        ),
           'ecpay12_i_invoice' =>array(
            'text'  => Lang::get('ecpay12_i_invoice'),
            //'desc'  => Lang::get('ecpay12_i_invoice_desc'),
            'type'  => 'radio',
            'name'  => 'config[ecpay12_i_invoice]',
            'items' => array(yes => 'yes' ,no => 'no'  ), 
        ),
        'ecpay12_imer_id' =>array(
            'text'  => Lang::get('ecpay12_imer_id'),
            'desc'  => Lang::get('ecpay12_imer_id_desc'),
            'type'  => 'text',
        ),  
    ),
);

?>