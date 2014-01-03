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

class ps_ecbank_tenpay {

    var $classname = "ps_ecbank_tenpay";
    var $payment_code = "綠界科技ECPAY";
	
    
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
        <table width="70%" align="center">
          <tr>
          <td width="16%" align="center"><strong>商店代號</strong></td>
              <td width="53%">
                  <input type="text" name="ECBANK_TENPAY_MID" class="inputbox" value="<?php  echo ECBANK_TENPAY_MID; ?>" />              </td>
              <td width="31%">綠界 ECBANK 商店代號              </td>
          </tr>
          <tr>
          <td align="center"><strong>檢查碼</strong></td>
              <td>
            <input name="ECBANK_TENPAY_CHECKCODE" type="text" class="inputbox" value="<?php  echo ECBANK_TENPAY_CHECKCODE ?>" size="30" />              </td>
              <td>綠界 ECBANK 交易檢查碼              </td>
          </tr>
          <tr>
            <td align="center">取得模組</td>
            <td><BUTTON onClick="CopyAndPaste('PaySbuy_FORM', 'payment_extrainfo')">點選此按鈕完成模組載入</BUTTON>
            <textarea name="PaySbuy_FORM" cols="80" rows="15" readonly="readonly" STYLE="display:none;">
            <?php echo "<?php\n"; ?>
                    //product_id,order_item_name,product_quantity

                    $order_id = $db->f("order_id");
                    $order_total = $db->f("order_total");

	$param = array(
                                                           'payment_type' => 'tenpay', 
                                                           'od_sob' => $order_id,
			//'item_name' => $order_id,
			//'item_desc' => $order_id,
			//'cur_type' => $order_id,
                                                            'amt' => ceil($order_total),
                                                         //'cancel_url' => $"order_id,
                                                           'return_url' => $order_id
	);     
        $form = '
	<form  name="ecbank" method="post" action="https://ecbank.com.tw/gateway.php">
	<input type="hidden" name="mer_id" value='.ECBANK_TENPAY_MID.'>
	<input type="hidden" name="payment_type" value="'.$param['payment_type'].'">
	<input type="hidden" name="od_sob" value='.$param["od_sob"].'>
	<input type="hidden" name="amt" value="'.$param["amt"].'">
	<input type="hidden" name="return_url" value='.SECUREURL.'index.php?option=com_virtuemart&amp;page=checkout.ecbank_tenpay_result&amp;order_id='.$db->f("order_id").' />
                   <input type="hidden" name="ok_url" value='.SECUREURL.'index.php?option=com_virtuemart&amp;page=checkout.ecbank_tenpay_result_ok&amp;order_id='.$db->f("order_id").' />
	<!input type="hidden" name="return_url" value="'.SECUREURL.'administrator/components/com_virtuemart/ecbank_tenpay_notify.php">';
         $form .= '
	<input type="submit" value="訂單已經產生,接下來請稍候,系統將自動轉至付款頁面,請不要關掉視窗"> 
	</form> 
    <script language=javascript>
            document.forms.ecbank.submit();
    </script>';
echo $form;
?>
            </textarea></td>
            <td>&nbsp;</td>
          </tr> 
</table>
        <?php
    }
    /*
     *     <script language=javascript>
		document.forms.ecbank_tenpay.submit();
	</script>
     * 
     */
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
      				"ECBANK_TENPAY_MID" => $d['ECBANK_TENPAY_MID'],
              		"ECBANK_TENPAY_CHECKCODE" => $d['ECBANK_TENPAY_CHECKCODE']
					
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
