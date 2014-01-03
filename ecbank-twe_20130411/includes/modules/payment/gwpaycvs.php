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

  class gwpaycvs {
    var $code, $title, $description, $enabled;

// class constructor
    function gwpaycvs() {
      global $order;

      $this->code = 'gwpaycvs';
      $this->title = MODULE_PAYMENT_GWPAYCVS_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_GWPAYCVS_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_GWPAYCVS_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_GWPAYCVS_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_GWPAYCVS_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_GWPAYCVS_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

	   $this->form_action_url = MODULE_PAYMENT_GWPAYCVS_FORM_ACTION_URL;	

    }

	function payment_error( $url_payment_error ) {
      twe_redirect(twe_href_link(FILENAME_CHECKOUT_PAYMENT, $url_payment_error, 'SSL', true, false));
    }

// class methods
    function update_status() {
      global $order,$db;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_GWPAYCVS_ZONE > 0) ) {
        $check_flag = false;
        $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_GWPAYCVS_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while (!$check->EOF) {
          if ($check->fields['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
 		$check->MoveNext();  
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
		return false;
    }

    function selection() {
      $selection = array('id' => $this->code,
                         'module' => $this->title);
      return $selection;
    }

    function pre_confirmation_check() {
		return false;
    }

    function confirmation() {
		 return array('title' => MODULE_PAYMENT_GWPAYCVS_TEXT_CONFIRMATION);
		
    }
  

    function process_button() {
      global $order, $SID;

      $SID = twe_session_name() . '=' . twe_session_id();
      $mid = MODULE_PAYMENT_GWPAYCVS_MID;

      $price = round($order->info['total']);
      $address = $order->customer['state'].$order->customer['city'].$order->customer['street_address'];
	  if ( MODULE_PAYMENT_GWPAYCVS_RETUREURL != '') {$reurl = 'returl' ; $returl_var= MODULE_PAYMENT_GWPAYCVS_RETUREURL ; }
	  
	  $ordnum = date('Ymdhis');
      $process_button_string =
        twe_draw_hidden_field('client', $mid) .
        twe_draw_hidden_field('amount', $price) .
		    //twe_draw_hidden_field('LG', UTF8) .
        twe_draw_hidden_field('訂購人', $order->customer['firstname']) .
        twe_draw_hidden_field('聯絡電話', $order->customer['telephone']) .
        twe_draw_hidden_field('送貨地址', $address) .
        twe_draw_hidden_field('email', $order->customer['email_address']) .
        twe_draw_hidden_field('郵遞區號', $order->customer['postcode']).
		twe_draw_hidden_field('roturl' , twe_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL')) .
		twe_draw_hidden_field('od_sob',$ordnum).
		twe_draw_hidden_field('systemtype', 'twe2.2_ecpay');
      return $process_button_string;
    }

    function before_process() {
     	
    return true;
    }

	
    function get_error() {
	   return false;
    }

    function after_process() {
      return false;
    }

    function output_error() {
      return false;
    }

    function check() {
      global $db;
      if (!isset($this->_check)) {
        $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_GWPAYCVS_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function install() {
	global $db;
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ( 'MODULE_PAYMENT_GWPAYCVS_STATUS', 'True', '6', '18', 'twe_cfg_select_option(array(\'True\', \'False\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_GWPAYCVS_ZONE', '0', '6', '18', 'twe_get_zone_class_title', 'twe_cfg_pull_down_zone_classes(', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_GWPAYCVS_SORT_ORDER', '0', '6', '18', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_GWPAYCVS_ORDER_STATUS_ID', '0', '6', '18', 'twe_cfg_pull_down_order_statuses(', 'twe_get_order_status_name', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ( 'MODULE_PAYMENT_GWPAYCVS_MID', '1111', '6', '18', now())");
      //$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_GWPAYCVS_CHECKCODE', 'ABCDEFGH', '6', '18', now())");
  	  $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_GWPAYCVS_ALLOWED', '',   '6', '0', now())");
	  
    }

    function remove() {
	global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_GWPAYCVS_STATUS', 'MODULE_PAYMENT_GWPAYCVS_ALLOWED', 'MODULE_PAYMENT_GWPAYCVS_ZONE', 'MODULE_PAYMENT_GWPAYCVS_SORT_ORDER', 'MODULE_PAYMENT_GWPAYCVS_ORDER_STATUS_ID', 'MODULE_PAYMENT_GWPAYCVS_MID');
    }
  }
?>