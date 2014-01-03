<?php
//defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );


if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );


/**
* 綠界科技 ECBank 金流模組 
*
*
* @version $Id: ps_ecbank_cvs.php,v 1.0 2010/03/16 12:00:02  $
* @package VirtueMart
* @subpackage payment
* @copyright Copyright (C) 2004-2009 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/

class ps_ecbank_cvs_curl {

    var $classname = "ps_ecbank_cvs_curl";
    var $payment_code = "綠界科技 ECBank";
	//error_reporting(0);
	//error_reporting(E_ALL ^ E_NOTICE);
	
    
    /**
    * Show all configuration parameters for this payment method
    * @returns boolean False when the Payment method has no configration
    */
    function show_configuration() {
        global $VM_LANG;
        $db = new ps_DB;
		$payment_method_id = vmGet( $_REQUEST, 'payment_method_id', null );
        /** Read current Configuration ***/
        require_once(CLASSPATH ."payment/".$this->classname.".cfg.php");
        ?>
	    <SCRIPT type="text/javascript">
			function CopyAndPaste( from, to ){
				document.getElementsByName(to)[0].value = document.getElementsByName(from)[0].value;
			}
		</SCRIPT>
        <table width="400" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td colspan="2" align="center"><a href="http://www.ecbank.com.tw" target="_blank"><img src="http://www.ecbank.com.tw/photo/logo-new.gif" border="0" /></a>&nbsp;</td>
            <td align="center"><table width="400" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td height="51" align="center" bgcolor="#66FF99"><a href="https://ecbank.com.tw" target="_blank">ECBank 後台登入</a></td>
              </tr>
              <tr>
                <td height="51" align="center" bgcolor="#CCFF66"><a href="https://ecbank.com.tw/members/register.php" target="_blank">立即免費註冊ECBank </a></td>
              </tr>
            </table></td>
          </tr>
        </table>
        <table width="70%" border="1" align="center" cellpadding="1" cellspacing="0">
         
        <tr>
              <td align="center"><strong>商店代號</strong></td>
              <td><input type="text" name="ECBANK_CVS_MID" class="inputbox" value="<?php  echo ECBANK_CVS_MID ?>" /></td>
              <td>綠界 ECPAY 商店代號</td>
          </tr>
          <tr>
          <td align="center"><strong>交易加密私鑰</strong></td>
              <td>
                  <input name="ECBANK_CVS_CHECKCODE" type="text" class="inputbox" value="<?php echo ECBANK_CVS_CHECKCODE ?>" size="30" />
              </td>
              <td>綠界ECBank 交易加密私鑰</td>
          </tr>
          <tr>
            <td align="center">取得模組</td>
            <td>
            <textarea name="PaySbuy_FORM" cols="80" rows="15" readonly="readonly" STYLE="display:none;">
<?php echo "<?php\n"; ?>
$param = array (
 // ecbank主機
 'ecbank_gateway' =>  'https://ecbank.com.tw/gateway.php',
 // 您的ECBank商店代號
 'mer_id' => ECBANK_CVS_MID,
 // 商店設定在ECBank管理後台的交易加密私鑰
 'enc_key' => ECBANK_CVS_CHECKCODE,
 // 商品說明及備註。(會出現在超商繳費平台螢幕上)
 'od_sob' => $db->f("order_id"),
 'prd_desc' => $db->f("order_id"),
 //'desc1' => rawurlencode('顏色：粉紅色'),
 //'desc2' => rawurlencode('w30 h60'),
 //'desc3' => rawurlencode('2010 限量款'),
 'desc4' => rawurlencode('付款完請保留繳費收据'),
 // 繳費金額
 'amt' => ceil($db->f("order_total")) ,
 // 付款完成通知網址
 'ok_url' => rawurlencode(SECUREURL.'administrator/components/com_virtuemart/ecbank_cvs_notify.php' )
);

$strAuth = '';
$nvpStr = 'payment_type=cvs'.
   '&od_sob='.$param['od_sob'].
   '&mer_id='.$param['mer_id'].
   '&enc_key='.$param['enc_key'].
   '&amt='.$param['amt'].
   '&prd_desc='.$param['prd_desc'].
  // '&desc1='.$param['desc1'].
   //'&desc2='.$param['desc2'].
   //'&desc3='.$param['desc3'].
   //'&desc4='.$param['desc4'].
   '&ok_url='.$param['ok_url'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$param['ecbank_gateway']);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpStr);
$strAuth = curl_exec($ch);
if (curl_errno($ch)) $strAuth = false;
curl_close($ch);

if($strAuth) {
 parse_str($strAuth, $res);
 if(!isset($res['error']) || $res['error'] != '0') {
 	  echo '取號錯誤:錯誤代碼 = ('.$res['error'].')';
 } else {
 
 echo '

<table width=90% border=1 align=center cellpadding=3 cellspacing=1>
  <tr>
    <td width=149 align=center bgcolor=#FFFF99>付款方式</td>
    <td width=235 bgcolor=#FFFF99>便利超商代碼繳費</td>
  </tr>
  <tr>
    <td align=center>超商繳費代碼</td>
    <td>'.$res['payno'].'</td>
  </tr>
  <tr>
    <td align=center>繳費金額</td>
    <td>'.intval($res['amt']).'元</td>
  </tr>
  <tr>
    <td align=center>訂單編號</td>
    <td>'.$res['od_sob'].'</td>
  </tr>
  <tr>
    <td colspan=2 align=center><a href=http://www.ecbank.com.tw/expenses-famiport.htm target=_blank>全家 FamiPort 門市操作步驟</a></td>
  </tr>
  <tr>
    <td colspan=2 align=center><a href=http://www.ecbank.com.tw/expenses-life-et.htm target=_blank>萊爾富Life-ET 門市操作步驟</a></td>
  </tr>
  <tr>
    <td colspan=2 align=center><a href=http://www.ecbank.com.tw/expenses-okgo.htm target=_blank>OK 便利商店 門市操作步驟</a></td>
  </tr>
  
  <tr>
    <td colspan=2><strong class=mycolor>請記下上列超商繳費代碼,至最近之全家或萊爾富便利商店,操作代碼繳費機台, 於列印出有調碼之繳款單後,至櫃台支付,便可完成繳費,繳費之收據請留存以供備核,繳費之後才算完成購物流程</strong></td>
  </tr>
  <tr>
    <td colspan=2 align=center class=myfont><br>
      <br>
      <br>
    本線上金流機制採用&lt; <a href=http://www.ecbank.com.tw target=_blank>綠界科技 ECBank 線上支付平台</a> &gt;, 於超商繳費單列印平台可見綠界科技 ECBank 圖示,請安心使用</td>
  </tr>
</table>

';

 }
} else
 echo '取號失敗:錯誤代碼 = '.$res['error'];
?>
            </textarea>
<BUTTON onClick="CopyAndPaste('PaySbuy_FORM', 'payment_extrainfo')">點選此按鈕完成模組載入</BUTTON>            </td>
            <td>&nbsp;</td>
          </tr> 
</table>
        <?php
        return true;
    }
    
    function has_configuration() {
      // return false if there's no configuration
      return true;
   }
   
  /**
	* Returns the "is_writeable" status of the configuration file
	* @param void
	* @returns boolean True when the configuration file is writeable, false when not
	*/
   function configfile_writeable() {
      return is_writeable( CLASSPATH."payment/".$this->classname.".cfg.php" );
   }
   
  /**
	* Returns the "is_readable" status of the configuration file
	* @param void
	* @returns boolean True when the configuration file is writeable, false when not
	*/
   function configfile_readable() {
      return is_readable( CLASSPATH."payment/".$this->classname.".cfg.php" );
   }
   
  /**
	* Writes the configuration file for this payment method
	* @param array An array of objects
	* @returns boolean True when writing was successful
	*/
   function write_configuration( &$d ) {
      
      $my_config_array = array(
      				"ECBANK_CVS_MID" => $d['ECBANK_CVS_MID'],
              		"ECBANK_CVS_CHECKCODE" => $d['ECBANK_CVS_CHECKCODE']
	  );
     $config = "<?php\n";
     
     $config .= "if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); \n\n";
		foreach( $my_config_array as $key => $value ) {     
     $config .= "define ('$key', '$value');\n";
      }
      
      $config .= "?>";
  
      if ($fp = fopen(CLASSPATH ."payment/".$this->classname.".cfg.php", "w")) {
          fputs($fp, $config, strlen($config));
          fclose ($fp);
          return true;
      }
     else
        return false;
   }
   
  /**************************************************************************
  ** name: process_payment()
  ** returns: 
  ***************************************************************************/
   function process_payment($order_number, $order_total, &$d) {
  // print_r($_REQUEST);
   //exit;
	        return true;
    }
   
}
