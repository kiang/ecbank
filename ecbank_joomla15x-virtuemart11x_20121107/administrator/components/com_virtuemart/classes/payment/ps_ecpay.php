<?php
//defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );


if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );


/**
*
* @version $Id: ps_worldpay.php,v 1.3 2005/09/29 20:02:18 soeren_nb Exp $
* @package VirtueMart
* @subpackage payment
* @copyright Copyright (C) 2004-2005 Soeren Eberhardt. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/

class ps_ecpay {

    var $classname = "ps_ecpay";
    var $payment_code = "綠界科技ECPAY";
	
    
    /**
    * Show all configuration parameters for this payment method
    * @returns boolean False when the Payment method has no configration
    */
    function show_configuration() {
        global $VM_LANG;
        
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
            <td colspan="2" align="center"><a href="http://www.ecbank.com.tw" target="_blank"><img src="http://www.ecpay.com.tw/b2c.gif" border="0" /></a>&nbsp;</td>
            <td align="center"><table width="400" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td height="51" align="center" bgcolor="#66FF99"><a href="https://ecpay.com.tw" target="_blank">ECPAY 信用卡收款後台登入</a></td>
              </tr>
              <tr>
                <td height="51" align="center" bgcolor="#CCFF66"><a href="http://www.ecpay.com.tw/" target="_blank">立即申請 ECPAY <br />
                </a>洽詢請電 02-89763899 分機 323 徐小姐</td>
              </tr>
            </table></td>
          </tr>
        </table>
        <table width="70%" align="center">
          <tr>
          <td align="center"><strong>商店代號</strong></td>
              <td>
                  <input type="text" name="ECPAY_MID" class="inputbox" value="<?php  echo ECPAY_MID; ?>" />              </td>
              <td>綠界 ECPAY 商店代號              </td>
          </tr>
          <tr>
          <td align="center"><strong>檢查碼</strong></td>
              <td>
                  <input type="text" name="ECPAY_CHECKCODE" class="inputbox" value="<?php  echo ECPAY_CHECKCODE; ?>" />              </td>
              <td>綠界 ECPAY 檢查碼              </td>
          </tr>
          <tr>
            <td align="center"><b>語&nbsp;&nbsp;&nbsp;&nbsp; 系</b></td>
            <td><input type="text" name="LG" class="inputbox" value="<?php  echo LG; ?>" /></td>
            <td>UTF-8 請填 UTF8,(Big5 保持空白) </td>
          </tr>
          <tr>
            <td align="center">取得模組</td>
            <td><BUTTON onClick="CopyAndPaste('PaySbuy_FORM', 'payment_extrainfo')">點選此按鈕完成模組載入</BUTTON>
            <textarea name="PaySbuy_FORM" cols="80" rows="15" readonly="readonly" STYLE="display:none;">
            <?php echo "<?php\n"; ?>
            
	$param = array(
    		'amount' => ceil($db->f("order_total")),
            'od_sob' => $db->f("order_id")
	);        
  echo '
	<form  name="ecpay" method="post" action="https://ecpay.com.tw/form_Sc_to5.php">
	<input type=hidden name=act value=auth>
	<input type=hidden name=client value='.ECPAY_MID.'>
	<input type=hidden name=amount value='.$param["amount"].'>
	<input type=hidden name=od_sob value='.$param["od_sob"].'>
	<input type="hidden" name="roturl" value='.SECUREURL.'index.php?option=com_virtuemart&amp;page=checkout.ecpay_result&amp;order_id='.$db->f("order_id").' />
	<input type=hidden name=LG value=LG />
	<input type=hidden name=訂單編號 value='.$param["od_sob"].'>
	<input type=hidden name=email value='.$user->email.'>
	<input type=hidden name=地址 value='.$user->address_1.'&#10'.$user->address_2.'&#10'.$user->city.'&#10'.$user->state.'>
	<input type=hidden name=name value='.$user->title.$user->first_name.$user->middle_name.$user->last_name.'>
	<input type=hidden name=country value='.$user->country.'>
	<input type=hidden name=postcode value='.$user->zip.'>
	<input type=hidden name=聯絡電話  value='.$user->phone_1.'>
	<input type=submit value=訂單已經產生,請不要關閉視窗,按此鈕或系統將自動轉至進行線上刷卡頁面> 
	</form>
    <script language=javascript>
		document.forms.ecpay.submit();
	</script>';
?>
            
            </textarea></td>
            <td>&nbsp;</td>
          </tr> 
</table>
        <?php
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
      				"ECPAY_MID" => $d['ECPAY_MID'],
              		"ECPAY_CHECKCODE" => $d['ECPAY_CHECKCODE'],
					   //"ROTURL" => $d['ROTURL'],
					   "LG" => $d['LG']
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
