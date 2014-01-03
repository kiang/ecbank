<?php

return array(
    'code'      => 'ecpay3',
    'name'      => Lang::get('ecpay3'),
    'desc'      => Lang::get('ecpay3_desc'),
    'is_online' => '1',
    'author'    => 'GreenWorld',
    'website'   => 'http://www.greenworld.com.tw',
    'version'   => '1.0',
    'currency'  => Lang::get('ecpay3_currency'),
    'config'    => array(
 	   'ecpay3_account'   => array(        //綠界商店代號
            'text'  => Lang::get('ecpay3_account'),
            'desc'  => Lang::get('ecpay3_account_desc'),
            'type'  => 'text',
        ),    
        'ecpay3_key'       => array(        //檢查碼
        'text'  => Lang::get('ecpay3_key'),
            'type'  => 'text',
        ),
              'ecpay3_i_invoice' =>array(
            'text'  => Lang::get('ecpay3_i_invoice'),
            //'desc'  => Lang::get('ecpay3_i_invoice_desc'),
            'type'  => 'radio',
            'name'  => 'config[ecpay3_i_invoice]',
            'items' => array(yes => 'yes' ,no => 'no'  ), 
        ),
        'ecpay3_imer_id' =>array(
            'text'  => Lang::get('ecpay3_imer_id'),
            'desc'  => Lang::get('ecpay3_imer_id_desc'),
            'type'  => 'text',
        ),   
    ),
);

?>