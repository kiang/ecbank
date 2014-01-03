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

$_LANG['ecbank_pincode'] = '<font color=blue>綠界 ECBank 超商代碼支付</font>';
$_LANG['ecbank_pincode_desc'] = ' 綠界 ECBank - <font color=red>超商代碼支付</font>';
$_LANG['ecbank_pincode_account'] = '商店代號(必填)';
$_LANG['ecbank_pincode_checkcode'] = '綠界 ECBank 檢查碼(必填)';
//$_LANG['gw_ecpay_banktype'] = '指定支付银行(选填)';
//$_LANG['gw_ibon_banktype_desc'] = '如果你目前缺少某个银行的支付接口，可以选用此项，对应的银行代码参考云网支付网关技术手册，默认为全部银行';
$_LANG['ecbank_pincode_language'] = '支付界面語言';
$_LANG['ecbank_pincode_language_desc'] = '出現在銀行行支付界面的語言';
$_LANG['ecbank_pincode_language_range'][0] = '繁體中文';
$_LANG['ecbank_pincode_language_range'][1] = 'English';
$_LANG['pay_button'] = '進行綠界 ECBank 虛擬帳號支付取號';
$_LANG['ecbank_pincode_inv_active']='是否啟動電子發票';
$_LANG['ecbank_pincode_inv_active_range'][0]='關閉';
$_LANG['ecbank_pincode_inv_active_range'][1]='開啟';
$_LANG['ecbank_pincode_inv_mer_id']='電子發票系統的商店代號';
?>