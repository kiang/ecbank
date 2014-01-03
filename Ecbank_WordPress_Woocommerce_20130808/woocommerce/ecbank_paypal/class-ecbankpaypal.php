<?php
/**
 * Ecbank_paypal Payment Gateway
 * Plugin URI: http://www.ecbank.com.tw
 * Description: Ecbank_paypal 收款模組
 * Version: 1.0
 * Author URI: http://www.ecbank.com.tw
 * Author: 綠界科技 Ecbank
 * Plugin Name:        EcBank_paypal
 * @class 		EcBank_paypal
 * @extends		WC_Payment_Gateway
 * @version		1.0
 * @author 		GreenWorld Roger    
 */
add_action('plugins_loaded', 'ecbankpaypal_gateway_init', 0);

function ecbankpaypal_gateway_init() {
//load_plugin_textdomain( 'ecbankpaypal', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/'  ); 
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_Ecbankpaypal extends WC_Payment_Gateway {

        /**
         * Constructor for the gateway.
         *
         * @access public
         * @return void
         */
        public function __construct() {
            $this->id = 'Ecbankpaypal';
            $this->icon = apply_filters('woocommerce_ecbankpaypal_icon', plugins_url('icon/green_log_40.gif', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('Ecbankpaypal', 'woocommerce');

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
            add_action('woocommerce_thankyou_Ecbankpaypal', array($this, 'thankyou_page'));  //需與id名稱大小寫相同
            add_action('woocommerce_receipt_Ecbankpaypal', array($this, 'receipt_page'));

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
                    'label' => __('啟動 Ecbankpaypal 收款模組', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('標題', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('客戶在結帳時所看到的標題', 'woocommerce'),
                    'default' => __('Ecbankpaypal 收款', 'woocommerce')
                ),
                'description' => array(
                    'title' => __('客戶訊息', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('', 'woocommerce'),
                    'default' => __(' 綠界 ECBank - <font color=red>paypal 繳費</font>', 'woocommerce')
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
            <h3><?php _e('綠界 Ecbank paypal 收款模組', 'woocommerce'); ?></h3>
            <p><?php _e('此模組可以讓您使用綠界科技的Ecbank paypal收款功能', 'woocommerce'); ?></p>
            <table class="form-table">
                <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
                ?>
            </table><!--/.form-table-->
            <?php
        }

        /**
         * Get Ecbankpaypal Args for passing to Ecbank
         *
         * @access public
         * @param mixed $order
         * @return array
         */
        function get_ecbankpaypal_args($order) {
            global $woocommerce;

            $paymethod = 'paypal';
            $order_id = $order->id;

            if ($this->debug == 'yes'){
                $this->log->add('ecbankpaypal', 'Generating payment form for order #' . $order_id . '. Notify URL: ' . $this->notify_url);
            }
            $buyer_name = $order->billing_last_name . $order->billing_first_name;

            $total_fee = $order->order_total;

            $ecbankpaypal_args = array(
                "mer_id" => $this->mer_id,
                "payment_type" => $paymethod,
                "od_sob" => $order_id,
                "amt" => round($order->get_order_total()),
                "cur_type" => 'TWD',
                "item_name" => "綠界科技 paypal 收款服務",
                "return_url" => $this->get_return_url($order),
                "cancel_url" => 'http://'.$_SERVER['HTTP_HOST']
            );
            $ecbankpaypal_args = apply_filters('woocommerce_ecbankpaypal_args', $ecbankpaypal_args);
            return $ecbankpaypal_args;
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
            $serial = trim($_REQUEST['proc_date'] . $_REQUEST['proc_time'] . $_REQUEST['tsr']);
            // 回傳的交易驗證壓碼
            $tac = trim($_REQUEST['tac']);
            $od_sob = trim($_REQUEST['od_sob']);
            $amt = $_REQUEST['amt'];

            $ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid.php';
            $post_parm = 'key=' . $checkcode .
                    '&serial=' . $serial .
                    '&tac=' . $tac;
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
                    $order->update_status('processing', __('Payment received, awaiting fulfilment', 'Ecbankpaypal'));
                } else {
                    echo "交易失敗";
                }
            } else {
                echo "驗證失敗";
            }
        }

        /**
         * Generate the Ecbankpaypal button link (POST method)
         *
         * @access public
         * @param mixed $order_id
         * @return string
         */
        function generate_ecbankpaypal_form($order_id) {///
            global $woocommerce;
            $order = new WC_Order($order_id);
            $ecbankpaypal_args = $this->get_ecbankpaypal_args($order);
            $ecbank_gateway = $this->gateway;
            $ecbankpaypal_args_array = array();
            foreach ($ecbankpaypal_args as $key => $value) {
                $ecbankpaypal_args_array[] = '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
            }

            $woocommerce->add_inline_js('
			jQuery("body").block({
					message: "<img src=\"' . esc_url(apply_filters('woocommerce_ajax_loader_url', $woocommerce->plugin_url() . '/assets/images/ajax-loader.gif')) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />' . __('感謝您的訂購，接下來畫面將導向到paypal的登入頁面', 'ecbankpaypal') . '",
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
			jQuery("#submit_ecbankpaypal_payment_form").click();				
			');

            return '<form id="ecbankpaypal" name="ecbankpaypal" action=" ' . $ecbank_gateway . ' " method="post" target="_top">' . implode('', $ecbankpaypal_args_array) . '
				<input type="submit" class="button-alt" id="submit_ecbankpaypal_payment_form" value="' . __('Pay via Ecbankpaypal', 'ecbankpaypal') . '" />
				</form>' . "<script>document.forms['ecbankpaypal'].submit();</script>";
        }

        /**
         * Output for the order received page.
         *
         * @access public
         * @return void
         */
        function receipt_page($order) {
            echo '<p>' . __('Thank you for your order, please click the button below to pay with Ecbankpaypal.', 'ecbankpaypal') . '</p>';
            echo $this->generate_ecbankpaypal_form($order);
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

          if ( $order->payment_method !== 'ecbankpaypal') return;

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
    function add_ecbankpaypal_gateway($methods) {
        $methods[] = 'WC_Ecbankpaypal';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_ecbankpaypal_gateway');
}
?>