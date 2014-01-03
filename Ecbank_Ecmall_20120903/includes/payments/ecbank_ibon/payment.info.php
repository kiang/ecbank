<?php

return array(
    'code'      => 'ecbank_ibon',
    'name'      => Lang::get('ecbank_ibon'),
    'desc'      => Lang::get('ecbank_ibon_desc'),
    'is_online' => '1',
    'author'    => 'GreenWorld',
    'website'   => 'http://www.greenworld.com.tw',
    'version'   => '1.0',
    'currency'  => Lang::get('ecbank_ibon_currency'),
    'config'    => array(
 	   'ecbank_ibon_account'   => array(        //綠界商店代號
            'text'  => Lang::get('ecbank_ibon_account'),
            'desc'  => Lang::get('ecbank_ibon_account_desc'),
            'type'  => 'text',
        ),    
        'ecbank_ibon_key'       => array(        //檢查碼
        'text'  => Lang::get('ecbank_ibon_key'),
            'type'  => 'text',
        ),
           'ecbank_ibon_i_invoice' =>array(
            'text'  => Lang::get('ecbank_ibon_i_invoice'),
            //'desc'  => Lang::get('ecbank_ibon_i_invoice_desc'),
            'type'  => 'radio',
            'name'  => 'config[ecbank_ibon_i_invoice]',
            'items' => array(yes => 'yes' ,no => 'no'  ), 
        ),
        'ecbank_ibon_imer_id' =>array(
            'text'  => Lang::get('ecbank_ibon_imer_id'),
            'desc'  => Lang::get('ecbank_ibon_imer_id_desc'),
            'type'  => 'text',
        ),
    ),
);

?>