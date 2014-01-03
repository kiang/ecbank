<?php

return array(
    'code'      => 'ecbank_barcode',
    'name'      => Lang::get('ecbank_barcode'),
    'desc'      => Lang::get('ecbank_barcode_desc'),
    'is_online' => '1',
    'author'    => 'GreenWorld',
    'website'   => 'http://www.greenworld.com.tw',
    'version'   => '1.1',
    'currency'  => Lang::get('ecbank_barcode_currency'),
    'config'    => array(
 	   'ecbank_barcode_account'   => array(        //綠界商店代號
            'text'  => Lang::get('ecbank_barcode_account'),
            'desc'  => Lang::get('ecbank_barcode_account_desc'),
            'type'  => 'text',
        ),    
        'ecbank_barcode_key'       => array(        //檢查碼
            'text'  => Lang::get('ecbank_barcode_key'),
            'type'  => 'text',
        ),
        'ecbank_barcode_i_invoice' =>array(
            'text'  => Lang::get('ecbank_barcode_i_invoice'),
            //'desc'  => Lang::get('ecbank_barcode_i_invoice_desc'),
            'type'  => 'radio',
            'name'  => 'config[ecbank_barcode_i_invoice]',
            'items' => array(yes => 'yes' ,no => 'no'  ), 
        ),
        'ecbank_barcode_imer_id' =>array(
            'text'  => Lang::get('ecbank_barcode_imer_id'),
            'desc'  => Lang::get('ecbank_barcode_imer_id_desc'),
            'type'  => 'text',
        ),
    ),
);

?>