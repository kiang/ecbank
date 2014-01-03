<?php

return array(
    'code'      => 'ecbank_paypal',
    'name'      => Lang::get('ecbank_paypal'),
    'desc'      => Lang::get('ecbank_paypal_desc'),
    'is_online' => '1',
    'author'    => 'GreenWorld',
    'website'   => 'http://www.greenworld.com.tw',
    'version'   => '1.0',
    'currency'  => Lang::get('ecbank_paypal_currency'),
    'config'    => array(
 	   'ecbank_paypal_account'   => array(        //綠界商店代號
            'text'  => Lang::get('ecbank_paypal_account'),
            'desc'  => Lang::get('ecbank_paypal_account_desc'),
            'type'  => 'text',
        ),    
        'ecbank_paypal_key'       => array(        //檢查碼
        'text'  => Lang::get('ecbank_paypal_key'),
            'type'  => 'text',
        ),
           'ecbank_paypal_i_invoice' =>array(
            'text'  => Lang::get('ecbank_paypal_i_invoice'),
            //'desc'  => Lang::get('ecbank_paypal_i_invoice_desc'),
            'type'  => 'radio',
            'name'  => 'config[ecbank_paypal_i_invoice]',
            'items' => array(yes => 'yes' ,no => 'no'  ), 
        ),
        'ecbank_paypal_imer_id' =>array(
            'text'  => Lang::get('ecbank_paypal_imer_id'),
            'desc'  => Lang::get('ecbank_paypal_imer_id_desc'),
            'type'  => 'text',
        ),
    ),
);

?>