<?php

return array(
    'code'      => 'ecbank_cvs',
    'name'      => Lang::get('ecbank_cvs'),
    'desc'      => Lang::get('ecbank_cvs_desc'),
    'is_online' => '1',
    'author'    => 'GreenWorld',
    'website'   => 'http://www.greenworld.com.tw',
    'version'   => '1.0',
    'currency'  => Lang::get('ecbank_cvs_currency'),
    'config'    => array(
 	   'ecbank_cvs_account'   => array(        //綠界商店代號
            'text'  => Lang::get('ecbank_cvs_account'),
            'desc'  => Lang::get('ecbank_cvs_account_desc'),
            'type'  => 'text',
        ),    
        'ecbank_cvs_key'       => array(        //檢查碼
        'text'  => Lang::get('ecbank_cvs_key'),
            'type'  => 'text',
        ),
        'ecbank_cvs_i_invoice' =>array(
            'text'  => Lang::get('ecbank_cvs_i_invoice'),
            //'desc'  => Lang::get('ecbank_cvs_i_invoice_desc'),
            'type'  => 'radio',
            'name'  => 'config[ecbank_cvs_i_invoice]',
            'items' => array(yes => 'yes' ,no => 'no'  ), 
        ),
        'ecbank_cvs_imer_id' =>array(
            'text'  => Lang::get('ecbank_cvs_imer_id'),
            'desc'  => Lang::get('ecbank_cvs_imer_id_desc'),
            'type'  => 'text',
        ),
   
    ),
);

?>