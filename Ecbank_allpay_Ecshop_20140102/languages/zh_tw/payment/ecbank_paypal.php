<!DOCTYPE html>
<?php
/**
 * ECSHOP 云网支付@网关插件
 * ============================================================================
 * 版权所有 (C) 2005-2007 北京亿商互动科技发展有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
 * ============================================================================
 * @author:     CHNWAY <chnway@gmail.com>
 * @version:    v2.1
 * @website:	www.chnway.cn
 * ---------------------------------------------
 * $Author ID: chzfz  $
 * $Date: 2007年7月17日  7:37:16 ) $
 */

global $_LANG;

$_LANG['ecbank_paypal'] = '<font color=blue>綠界 ECBank-PayPal</font>';
$_LANG['ecbank_paypal_desc'] = ' 綠界 ECBank - <font color=red>PayPal 支付</font>';
$_LANG['ecbank_paypal_account'] = '商店代號(必填)';
$_LANG['ecbank_paypal_checkcode'] = 'ECBank 檢查碼(必填)';
//$_LANG['gw_ecpay_banktype'] = '指定支付银行(选填)';
//$_LANG['gw_ibon_banktype_desc'] = '如果你目前缺少某个银行的支付接口，可以选用此项，对应的银行代码参考云网支付网关技术手册，默认为全部银行';
$_LANG['ecbank_paypal_language'] = '支付界面語言';
$_LANG['ecbank_paypal_language_desc'] = '出現在銀行行支付界面的語言';
$_LANG['ecbank_paypal_language_range'][0] = '繁體中文';
$_LANG['ecbank_paypal_language_range'][1] = 'English';


$_LANG['ecbank_paypal_cur_type'] = '使用貨幣';
$_LANG['ecbank_paypal_cur_type_desc'] = '出現在支付界面的貨幣別';
$_LANG['ecbank_paypal_cur_type_range']['TWD'] = 'TWD(台幣)';
$_LANG['ecbank_paypal_cur_type_range']['USD'] = 'USD(美金)';
$_LANG['ecbank_paypal_cur_type_range']['CAD'] = 'CAD(加幣)';
$_LANG['ecbank_paypal_cur_type_range']['HKD'] = 'HKD(港幣)';
$_LANG['ecbank_paypal_cur_type_range']['EUR'] = 'EUR(歐元)';
$_LANG['ecbank_paypal_cur_type_range']['JPY'] = 'JPY(日元)';
$_LANG['ecbank_paypal_cur_type_range']['AUD'] = 'AUD(澳幣)';
$_LANG['ecbank_paypal_cur_type_range']['CZH'] = 'CZK(捷克克朗)';
$_LANG['ecbank_paypal_cur_type_range']['DKK'] = 'DKK(丹麥克朗)';
$_LANG['ecbank_paypal_cur_type_range']['HUF'] = 'HUF(匈牙利福林)';
$_LANG['ecbank_paypal_cur_type_range']['ILS'] = 'ILS(以色列新謝克爾)';
$_LANG['ecbank_paypal_cur_type_range']['MXN'] = 'MXN(墨西哥披索)';
$_LANG['ecbank_paypal_cur_type_range']['NOK'] = 'NOK(挪威克朗)';
$_LANG['ecbank_paypal_cur_type_range']['NZD'] = 'NZD(紐西蘭幣)';
$_LANG['ecbank_paypal_cur_type_range']['PLN'] = 'PLN(波蘭茲羅提)';
$_LANG['ecbank_paypal_cur_type_range']['GBP'] = 'GBP(英鎊)';
$_LANG['ecbank_paypal_cur_type_range']['SGD'] = 'SGD(新加坡幣)';
$_LANG['ecbank_paypal_cur_type_range']['SEK'] = 'SEK(瑞典克朗)';
$_LANG['ecbank_paypal_cur_type_range']['CHF'] = 'CHF(瑞士法郎)';


$_LANG['pay_button'] = '進行綠界 ECBank PayPal 線上繳款'
?>