<?php
/*

   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(german.php,v 1.119 2003/05/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (german.php,v 1.25 2003/08/25); www.nextcommerce.org
   (c) 2003	 xtcommerce  www.xt-commerce.com   
   Released under the GNU General Public License 
*/

  define('MODULE_PAYMENT_GWPAYCVS_TEXT_TITLE', '綠界科技(便利超商)線上付款機制');
  define('MODULE_PAYMENT_GWPAYCVS_TEXT_DESCRIPTION', '綠界科技(便利超商)線上付款機制<br><a href=http://www.anow.com.tw/cvs/ target=_blank>http://www.anow.com.tw/cvs/</a>');
  define('MODULE_PAYMENT_GWPAYCVS_STATUS_TITLE','綠界科技(便利超商)線上付款模組啟動');
  define('MODULE_PAYMENT_GWPAYCVS_STATUS_DESC','綠界科技(便利超商)線上付款機制是否啟動?');

  define('MODULE_PAYMENT_GWPAYCVS_ZONE_TITLE','綠界科技(便利超商)線上付款機制付款結帳地區設定');
  define('MODULE_PAYMENT_GWPAYCVS_ZONE_DESC','如果設定地區，則只有該地區的使用者能使用綠界科技(便利超商)線上付款機制模組.');

  define('MODULE_PAYMENT_GWPAYCVS_SORT_ORDER_TITLE','綠界科技(便利超商)線上付款機制顯示順序');
  define('MODULE_PAYMENT_GWPAYCVS_SORT_ORDER_DESC','顯示順序，數字越小順序在前');

  define('MODULE_PAYMENT_GWPAYCVS_ORDER_STATUS_ID_TITLE','付款結帳完成時預設訂單狀態');
  define('MODULE_PAYMENT_GWPAYCVS_ORDER_STATUS_ID_DESC','使用綠界科技(便利超商)線上付款結帳完成時,TWE 內訂單的預設狀態');
  define('MODULE_PAYMENT_GWPAYCVS_MID_TITLE','綠界科技 商店編號');
  define('MODULE_PAYMENT_GWPAYCVS_MID_DESC','設定綠界科技(便利超商)線上付款機制商店編號<br>如果還沒申請請電 02-89763899 分機:779王小姐');
  define('MODULE_PAYMENT_GWPAYCVS_RETUREURL_TITLE',"設定授權結果回傳網址");
  define('MODULE_PAYMENT_GWPAYCVS_TEXT_CONFIRMATION','本網站採用(<a href="http://www.anow.com.tw/cvs/" target="_BLANK"><font color=green>綠界科技</font>綠界科技(便利超商)線上付款機制</a>)</font>。在您按下"確認訂單"鈕後,網頁將導向本公司專屬的 SSL 加密網頁中進行，請放心使用！</span>');
  define('MODULE_PAYMENT_GWPAYCVS_CHECKCODE_TITLE','綠界科技 回傳檢查設定');
  define('MODULE_PAYMENT_GWPAYCVS_CHECKCODE_DESC','設定綠界金流最高管理權限帳號,可避免偽冒封包回傳');
  define('MODULE_PAYMENT_GWPAYCVS_ALLOWED_TITLE','綠界線上金流 轉帳國家'); 
  define('MODULE_PAYMENT_GWPAYCVS_ALLOWED_DESC','輸入國家代碼，則只有列出國家可以使用這個付款方式 (例如 AT,DE (留白表示不設限))');  
  define('MODULE_PAYMENT_GWPAYCVS_TEXT_ERROR_1', '中國信託信用卡分期付款結帳，每筆結帳金額不得低於新台幣300元');
  define('MODULE_PAYMENT_GWPAYCVS_TEXT_ERROR_2', '線上付款授權失敗，請確認所輸入相關資訊正確無誤,或換張信用卡');
  define('MODULE_PAYMENT_GWPAYCVS_TEXT_ERROR_3', '授權回傳驗證失敗, 本網站只接受由(綠界科技)所回傳的授權結果,閒雜人等不用試了');
  define('MODULE_PAYMENT_GWPAYCVS_TEXT_ERROR', '綠界科技(便利超商)線上付款機制，錯誤訊息!');
  //define('MODULE_PAYMENT_GWPAYCVS_FORM_ACTION_URL', 'https://gwpay.com.tw/form_Sc_to5.php');  //如果是使用綠界 gwpay 的話就把這個起用
  define('MODULE_PAYMENT_GWPAYCVS_FORM_ACTION_URL', 'http://ts.payonline.com.tw/cvs_barcode_osc.php');
  //define('MODULE_PAYMENT_GWPAYCVS_FORM_ACTION_URL', 'https://ecay.com.tw/form_Sc_to5e.php');  //英文表單
?>
