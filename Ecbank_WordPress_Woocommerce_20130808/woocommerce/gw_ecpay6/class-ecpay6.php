<?php
/**
 * Ecpay6 Payment Gateway
 * Plugin URI: http://www.ecpay.com.tw/
 * Description: ecpay 分6期 線上刷卡模組
 * Version: 1.0
 * Author URI: http://www.ecpay.com.tw/
 * Author: 綠界科技 Ecpay
 * Plugin Name:        Ecpay6
 * @class 		Ecpay6
 * @extends		WC_Payment_Gateway
 * @version		1.0
 * @author 		GreenWorld Roger    
 */
add_action('plugins_loaded', 'ecpay6_gateway_init', 0);

function ecpay6_gateway_init() {
//load_plugin_textdomain( 'ecpay6', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/'  ); 
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_ecpay6 extends WC_Payment_Gateway {

        /**
         * Constructor for the gateway.
         *
         * @access public
         * @return void
         */
        public function __construct() {
            $this->id = 'ecpay6';
            $this->icon = apply_filters('woocommerce_ecpay6_icon', plugins_url('icon/green_log_40.gif', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('ecpay6', 'woocommerce');

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
            $this->gateway = "https://ecpay.com.tw/form_Sc_to5_fn.php";

// Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action('woocommerce_thankyou_ecpay6', array($this, 'thankyou_page'));  //需與id名稱大小寫相同
            add_action('woocommerce_receipt_ecpay6', array($this, 'receipt_page'));
//add_action('init', array(&$this, 'check_ecpay6_response'));
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
                    'label' => __('啟動 Ecpay 分6期 線上刷卡模組', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('標題', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('客戶在結帳時所看到的標題', 'woocommerce'),
                    'default' => __('Ecpay 分6期 線上刷卡', 'woocommerce')
                ),
                'description' => array(
                    'title' => __('客戶訊息', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('', 'woocommerce'),
                    'default' => __(' 綠界 Ecpay -分6期 線上刷卡付款', 'woocommerce')
                ),
                'mer_id' => array(
                    'title' => __('商店代號', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('請填入您Ecpay 分期商店代號', 'woocommerce'),
                    'default' => __('0000', 'woocommerce')
                ),
                'check_code' => array(
                    'title' => __('商家驗證碼', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('請填入您Ecpay 分期商店的商家驗證碼', 'woocommerce'),
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
            <h3><?php _e('綠界 Ecpay 分6期 線上刷卡模組', 'woocommerce'); ?></h3>
            <p><?php _e('此模組可以讓您使用綠界科技的Ecpay 分6期線上刷卡付款功能', 'woocommerce'); ?></p>
            <table class="form-table">
                <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
                ?>
            </table><!--/.form-table-->
            <?php
        }

        /**
         * Get ecpay6 Args for passing to Ecbank
         *
         * @access public
         * @param mixed $order
         * @return array
         */
        function get_ecpay6_args($order) {
            global $woocommerce;

            $order_id = $order->id;

            if ($this->debug == 'yes') {
                $this->log->add('ecpay6', 'Generating payment form for order #' . $order_id . '. Notify URL: ' . $this->notify_url);
            }
            $buyer_name = $order->billing_last_name . $order->billing_first_name;

            $total_fee = $order->order_total;

            $ecpay6_args = array(
                "client" => $this->mer_id,
                "act" => 'auth',
                "od_sob" => $order_id,
                "amount" => round($order->get_order_total()),
                "stage" => "6",
                "roturl" => $this->get_return_url($order),
                'bk_posturl' => $this->get_return_url($order)
            );
            $ecpay6_args = apply_filters('woocommerce_ecpay6_args', $ecpay6_args);
            return $ecpay6_args;
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

            $checkcode = $this->check_code;
            $TOkSi = $_POST['process_time'] + $_POST['gwsr'] + $_POST['amount'];
            
            $a = substr($TOkSi, 0, 1) . substr($TOkSi, 2, 1) . substr($TOkSi, 4, 1);
            $b = substr($TOkSi, 1, 1) . substr($TOkSi, 3, 1) . substr($TOkSi, 5, 1);
            $my_spcheck = ( $checkcode % $TOkSi ) + $checkcode + $a + $b;

            if ($my_spcheck == $_POST['spcheck']) {
                if ($_POST['succ'] == 1) {
                    $result_msg = '交易成功，Ecpay 交易單號：' . $_REQUEST['gwsr'] . '，處理日期：' . $_REQUEST['process_date'] .'，時間：'. $_REQUEST['process_time'];   //交易成功
                    $order->update_status('processing', __('Payment received, awaiting fulfilment', 'ecpay6'));
					$woocommerce->cart->empty_cart();
                } else {
                    $result_msg = '交易授權失敗。';
                }
            } else {
                $result_msg = '驗證失敗，請非綠界相關人士不要進行惡意的駭客攻擊。';
            }
            echo $result_msg;
        }

        /**
         * Generate the ecpay6 button link (POST method)
         *
         * @access public
         * @param mixed $order_id
         * @return string
         */
        function generate_ecpay6_form($order_id) {///***********************************************************
            global $woocommerce;
            $order = new WC_Order($order_id);
            $ecpay6_args = $this->get_ecpay6_args($order);
            $ecpay6_gateway = $this->gateway;
            $ecpay6_args_array = array();
            foreach ($ecpay6_args as $key => $value) {
                $ecpay6_args_array[] = '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
            }

            $woocommerce->add_inline_js('
			jQuery("body").block({
					message: "<img src=\"' . esc_url(apply_filters('woocommerce_ajax_loader_url', $woocommerce->plugin_url() . '/assets/images/ajax-loader.gif')) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />' . __('感謝您的訂購，接下來畫面將導向到Ecpay的刷卡頁面', 'ecpay') . '",
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
			jQuery("#submit_ecpay6_payment_form").click();				
			');

            return '<form id="ecpay6" name="ecpay6" action=" ' . $ecpay6_gateway . ' " method="post" target="_top">' . implode('', $ecpay6_args_array) . '
				<input type="submit" class="button-alt" id="submit_ecpay6_payment_form" value="' . __('Pay via ecpay6', 'ecpay6') . '" />
				</form>' . "<script>document.forms['ecpay6'].submit();</script>";
        }

        /**
         * Output for the order received page.
         *
         * @access public
         * @return void
         */
        function receipt_page($order) {
            echo '<p>' . __('感謝您的訂購，接下來將導向到刷卡頁面，請稍後.', 'ecpay6') . '</p>';
            echo $this->generate_ecpay6_form($order);
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

          if ( $order->payment_method !== 'ecpay6') return;

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
    function add_ecpay6_gateway($methods) {
        $methods[] = 'WC_ecpay6';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_ecpay6_gateway');
}
?>