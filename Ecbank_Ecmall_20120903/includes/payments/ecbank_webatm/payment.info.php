<?php

return array(
    'code'      => 'ecbank_webatm',
    'name'      => Lang::get('ecbank_webatm'),
    'desc'      => Lang::get('ecbank_webatm_desc'),
    'is_online' => '1',
    'author'    => 'GreenWorld',
    'website'   => 'http://www.greenworld.com.tw',
    'version'   => '1.0',
    'currency'  => Lang::get('ecbank_webatm_currency'),
    'config'    => array(
    'ecbank_webatm_account'   => array(        //綠界商店代號
            'text'  => Lang::get('ecbank_webatm_account'),
            'desc'  => Lang::get('ecbank_webatm_account_desc'),
            'type'  => 'text',
        ),    
        'ecbank_webatm_key'       => array(        //檢查碼
        'text'  => Lang::get('ecbank_webatm_key'),
            'type'  => 'text',
        ),
              'ecbank_webatm_i_invoice' =>array(
            'text'  => Lang::get('ecbank_webatm_i_invoice'),
            //'desc'  => Lang::get('ecbank_webatm_i_invoice_desc'),
            'type'  => 'radio',
            'name'  => 'config[ecbank_webatm_i_invoice]',
            'items' => array(yes => 'yes' ,no => 'no'  ), 
        ),
        'ecbank_webatm_imer_id' =>array(
            'text'  => Lang::get('ecbank_webatm_imer_id'),
            'desc'  => Lang::get('ecbank_webatm_imer_id_desc'),
            'type'  => 'text',
        ),
    ),
);

?>