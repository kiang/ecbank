<?php
/**
 * EcbankTenpay Payment Gateway
 * Plugin URI: http://www.ecbank.com.tw
 * Description: Ecbank_Tenpay 財付通收款模組
 * Version: 1.0
 * Author URI: http://www.ecbank.com.tw
 * Author: 綠界科技 Ecbank
 * Plugin Name:        EcBank_Tenpay
 * @class 		EcBank_Tenpay
 * @extends		WC_Payment_Gateway
 * @version		1.0
 * @author 		GreenWorld Roger    
 */
add_action('plugins_loaded', 'ecbanktenpay_gateway_init', 0);

function ecbanktenpay_gateway_init() {
//load_plugin_textdomain( 'ecbanktenpay', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/'  ); 
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_EcbankTenpay extends WC_Payment_Gateway {

        /**
         * Constructor for the gateway.
         *
         * @access public
         * @return void
         */
        public function __construct() {
            $this->id = 'Ecbanktenpay';
            $this->icon = apply_filters('woocommerce_ecbanktenpay_icon', plugins_url('icon/green_log_40.gif', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('Ecbanktenpay', 'woocommerce');

            // Load the form fields.
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();

            // Define user set variables
            $this->title = $this->settings['title'];
            $this->description = $this->settings['description'];
            $this->mer_id = $this->settings['mer_id'];
            $this->check_code = $this->settings['check_code'];
            $this->notify_url = trailingslashit(home_url());
            $this->gateway = "https://ecbank.com.tw/gateway.php";

            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action('woocommerce_thankyou_Ecbanktenpay', array($this, 'thankyou_page'));  //需與id名稱大小寫相同
            add_action('woocommerce_receipt_Ecbanktenpay', array($this, 'receipt_page'));
            //add_action('init', array(&$this, 'check_Ecbanktenpay_response'));
            // Customer Emails
            //add_action('woocommerce_email_before_order_table', array(&$this, 'email_instructions'), 10, 2);
        }

        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         */
        function init_form_fields() {  //後台設置欄位
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('啟用/關閉', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('啟動 EcbankTenpay 財付通 收款模組', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('標題', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('客戶在結帳時所看到的標題', 'woocommerce'),
                    'default' => __('EcbankTenpay 財付通 收款', 'woocommerce')
                ),
                'description' => array(
                    'title' => __('客戶訊息', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('', 'woocommerce'),
                    'default' => __(' 綠界 ECBank - <font color=red>Tenpay 財付通 繳費</font>', 'woocommerce')
                ),
                'mer_id' => array(
                    'title' => __('商店代號', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('請填入您Ecbank商店代號', 'woocommerce'),
                    'default' => __('0000', 'woocommerce')
                ),
                'check_code' => array(
                    'title' => __('商家驗證碼', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('請填入您Ecbank商店的商家驗證碼', 'woocommerce'),
                    'default' => __('', 'woocommerce')
                )
            );
        }

        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis
         *
         * @access public
         * @return void
         */
        public function admin_options() {
            ?>
            <h3><?php _e('綠界 Ecbank Tenpay 財付通 收款模組', 'woocommerce'); ?></h3>
            <p><?php _e('此模組可以讓您使用綠界科技的Ecbank Tenpay 財付通 收款功能', 'woocommerce'); ?></p>
            <table class="form-table">
                <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
                ?>
            </table><!--/.form-table-->
            <?php
        }

        /**
         * Get EcbankTenpay Args for passing to Ecbank
         *
         * @access public
         * @param mixed $order
         * @return array
         */
        function get_ecbanktenpay_args($order) {
            global $woocommerce;

            $paymethod = 'tenpay';
            $order_id = $order->id;

            if ($this->debug == 'yes') {
                $this->log->add('ecbanktenpay', 'Generating payment form for order #' . $order_id . '. Notify URL: ' . $this->notify_url);
            }
            $buyer_name = $order->billing_last_name . $order->billing_first_name;

            $total_fee = $order->order_total;

            $ecbanktenpay_args = array(
                "mer_id" => $this->mer_id,
                "payment_type" => $paymethod,
                "od_sob" => $order_id,
                "amt" => round($order->get_order_total()),
                "return_url" => $this->get_return_url($order),
                'ok_url' => $this->get_return_url($order)
            );
            $ecbanktenpay_args = apply_filters('woocommerce_ecbanktenpay_args', $ecbanktenpay_args);
            return $ecbanktenpay_args;
        }

        /**
         * Output for the order received page.
         *
         * @access public
         * @return void
         */
        function thankyou_page($order_id) {  //接收回傳參數驗證
            global $woocommerce;
            $order = &new WC_Order($order_id);
            if ($description = $this->get_description())
                echo wpautop(wptexturize($description));
            //驗證碼
            $checkcode = $this->check_code;
            // 組合字串
            $serial = trim($_REQUEST['proc_date'] . $_REQUEST['proc_time'] . $_REQUEST['tsr'] . $_REQUEST['od_sob'] . $_REQUEST['amt']);
            // 回傳的交易驗證壓碼
            $mac = trim($_REQUEST['mac']);
            $od_sob = trim($_REQUEST['od_sob']);
            $amt = $_REQUEST['amt'];

            $ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid_new.php';
            // 串接驗證參數
            $post_parm = 'key=' . $checkcode .
                    '&serial=' . $serial .
                    '&mac=' . $mac;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $ecbank_gateway);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_parm);
            $strAuth = curl_exec($ch);
            if (curl_errno($ch))
                $strAuth = false;
            curl_close($ch);
            if ($strAuth == 'valid=1') {
                if (($_REQUEST['succ'] == '1') && (round($order->get_total()) == round($amt))) {
                    echo "交易成功";
                    // Remove cart
                    $woocommerce->cart->empty_cart();
                    $order->update_status('processing', __('Payment received, awaiting fulfilment', 'Ecbanktenpay'));
                } else {
                    echo "交易失敗";
                }
            } else {
                echo "驗證失敗";
            }
        }

        /**
         * Generate the EcbankTenpay button link (POST method)
         *
         * @access public
         * @param mixed $order_id
         * @return string
         */
        function generate_ecbanktenpay_form($order_id) {///***********************************************************
            global $woocommerce;
            $order = new WC_Order($order_id);

            $ecbanktenpay_args = $this->get_ecbanktenpay_args($order);
            $ecbank_gateway = $this->gateway;
            $ecbanktenpay_args_array = array();
            foreach ($ecbanktenpay_args as $key => $value) {
                    $ecbanktenpay_args_array[] = '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
            }

            $woocommerce->add_inline_js('
			jQuery("body").block({
					message: "<img src=\"' . esc_url(apply_filters('woocommerce_ajax_loader_url', $woocommerce->plugin_url() . '/assets/images/ajax-loader.gif')) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />' . __('感謝您的訂購，接下來畫面將導向到Tenpay 財付通的選單頁面', 'ecbanktenpay') . '",
					overlayCSS:
				{
					background: "#fff",
					opacity: 0.6
				},
					centerY: false,
					css: {
					top:			"20%",
					padding:        20,
					textAlign:      "center",
					color:          "#555",
					border:         "3px solid #aaa",
					backgroundColor:"#fff",
					cursor:         "wait",
					lineHeight:		"32px"
					}
					});
			jQuery("#submit_ecbanktenpay_payment_form").click();				
			');

            return '<form id="ecbanktenpay" name="ecbanktenpay" action=" ' . $ecbank_gateway . ' " method="post" target="_top">' . implode('', $ecbanktenpay_args_array) . '
				<input type="submit" class="button-alt" id="submit_ecbanktenpay_payment_form" value="' . __('Pay via EcbankTenpay', 'ecbanktenpay') . '" />
				</form>' . "<script>document.forms['ecbanktenpay'].submit();</script>";
        }

        /**
         * Output for the order received page.
         *
         * @access public
         * @return void
         */
        function receipt_page($order) {
            echo '<p>' . __('感謝您的訂購，現在將導向到財付通登入頁面，請稍後.', 'ecbanktenpay') . '</p>';
            echo $this->generate_ecbanktenpay_form($order);
        }

        /**
         * Add content to the WC emails.
         *
         * @access public
         * @param WC_Order $order
         * @param bool $sent_to_admin
         * @return void

          function email_instructions( $order, $sent_to_admin ) {
          if ( $sent_to_admin ) return;

          if ( $order->status !== 'on-hold') return;

          if ( $order->payment_method !== 'ecbanktenpay') return;

          if ( $description = $this->get_description() )
          echo wpautop( wptexturize( $description ) );
          }
         */

        /**
         * Process the payment and return the result
         *
         * @access public
         * @param int $order_id
         * @return array
         */
        function process_payment($order_id) {
            global $woocommerce;
            $order = new WC_Order($order_id);

            // Empty awaiting payment session
            unset($_SESSION['order_awaiting_payment']);
            //$this->receipt_page($order_id);
            return array(
                'result' => 'success',
                'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))))
            );
        }

        /**
         * Payment form on checkout page
         *
         * @access public
         * @return void
         */
        function payment_fields() {
            if ($this->description)
                echo wpautop(wptexturize($this->description));
        }

    }

    /**
     * Add the gateway to WooCommerce
     *
     * @access public
     * @param array $methods
     * @package		WooCommerce/Classes/Payment
     * @return array
     */
    function add_ecbanktenpay_gateway($methods) {
        $methods[] = 'WC_Ecbanktenpay';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_ecbanktenpay_gateway');
}
?>