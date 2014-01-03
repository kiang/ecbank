<?php

return array(
    'code'      => 'ecbank_alipay',
    'name'      => Lang::get('ecbank_alipay'),
    'desc'      => Lang::get('ecbank_alipay_desc'),
    'is_online' => '1',
    'author'    => 'GreenWorld',
    'website'   => 'http://www.greenworld.com.tw',
    'version'   => '1.0',
    'currency'  => Lang::get('ecbank_alipay_currency'),
    'config'    => array(
    'ecbank_alipay_account'   => array(        //綠界商店代號
            'text'  => Lang::get('ecbank_alipay_account'),
            'desc'  => Lang::get('ecbank_alipay_account_desc'),
            'type'  => 'text',
        ),    
        'ecbank_alipay_key'       => array(        //檢查碼
        'text'  => Lang::get('ecbank_alipay_key'),
            'type'  => 'text',
        ),
           'ecbank_alipay_i_invoice' =>array(
            'text'  => Lang::get('ecbank_alipay_i_invoice'),
            //'desc'  => Lang::get('ecbank_alipay_i_invoice_desc'),
            'type'  => 'radio',
            'name'  => 'config[ecbank_alipay_i_invoice]',
            'items' => array(yes => 'yes' ,no => 'no'  ), 
        ),
        'ecbank_alipay_imer_id' =>array(
            'text'  => Lang::get('ecbank_alipay_imer_id'),
            'desc'  => Lang::get('ecbank_alipay_imer_id_desc'),
            'type'  => 'text',
        ),  
    ),
);

?>