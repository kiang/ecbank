<?php
/**
 * Plugin Name: Ecbank_虛擬帳號
 * Plugin URI: http://www.ecbank.com.tw
 * Description: Ecbank虛擬帳號收款模組
 * Author: 綠界科技 Ecbank
 * Author URI: http://www.ecbank.com.tw
 * Version: 1.0
 * 
 * Vacc Payment Gateway
 * 綠界科技虛擬帳號 收款模組
 * Provides a vacc Standard Payment Gateway.
 * Plugin Name:                EcBank_Vacc
 * @class 		EcBank_Vacc
 * @extends		WC_Payment_Gateway
 * @version		1.0
 * @author 		GreenWorld Roger    
 */
add_action('plugins_loaded', 'ecbankvacc_gateway_init', 0);

function ecbankvacc_gateway_init() {
//load_plugin_textdomain( 'ecbankvacc', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/'  ); 
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_EcbankVacc extends WC_Payment_Gateway {

        /**
         * Constructor for the gateway.
         *
         * @access public
         * @return void
         */
        public function __construct() {
            $this->id = 'EcbankVacc';
            $this->icon = apply_filters('woocommerce_ecbankvacc_icon', plugins_url('icon/green_log_40.gif', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('EcbankVacc', 'woocommerce');

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
            //add_action('init', array(&$this, 'check_EcbankVacc_response'));
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action('woocommerce_thankyou_EcbankVacc', array($this, 'thankyou_page'));  //需與id名稱大小寫相同
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
                    'label' => __('啟動 Ecbank虛擬帳號 收款模組', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('標題', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('客戶在結帳時所看到的標題', 'woocommerce'),
                    'default' => __('Ecbank虛擬帳號 收款', 'woocommerce')
                ),
                'description' => array(
                    'title' => __('客戶訊息', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('', 'woocommerce'),
                    'default' => __(' 綠界 ECBank - <font color=red>虛擬帳號 繳費</font>', 'woocommerce')
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
            <h3><?php _e('綠界 Ecbank 虛擬帳號 收款模組', 'woocommerce'); ?></h3>
            <p><?php _e('此模組可以讓您使用綠界科技的Ecbank 虛擬帳號收款功能', 'woocommerce'); ?></p>
            <table class="form-table">
                <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
                ?>
            </table><!--/.form-table-->
            <?php
        }
        /**
         * Get EcbankVacc Args for passing to Ecbank
         *
         * @access public
         * @param mixed $order
         * @return array
         */
        function get_ecbankvacc_args($order) {
            global $woocommerce;

            $paymethod = 'vacc';
            $order_id = $order->id;

            if ($this->debug == 'yes') {
                $this->log->add('ecbankvacc', 'Generating payment form for order #' . $order_id . '. Notify URL: ' . $this->notify_url);
            }

            $buyer_name = $order->billing_last_name . $order->billing_first_name;

            $total_fee = $order->order_total;

            $ecbankvacc_args = array(
                "mer_id" => $this->mer_id,
                "payment_type" => $paymethod,
                "od_sob" => $order_id,
                'setbank' => "ESUN",
                'enc_key' => $this->check_code,
                'expire_day' => "7",
                "amt" => round($order->get_order_total()),
                "ok_url" => $this->get_return_url($order)
            );
            $ecbankvacc_args = apply_filters('woocommerce_ecbankvacc_args', $ecbankvacc_args);
            return $ecbankvacc_args;
        }

        /**
         * Output for the order received page.
         * 取號動作
         * @access public
         * @return void
         */
        function thankyou_page($order_id) {  //接收回傳參數驗證  與  虛擬帳號取號
            global $woocommerce;
            if ($description = $this->get_description())
                echo wpautop(wptexturize($description));

            if ($_REQUEST['payment_type'] == 'vacc') {  //接收回傳參數驗證
                //驗證碼
                $checkcode = $this->check_code;
                // 組合字串
                $serial = trim($_REQUEST['proc_date'] . $_REQUEST['proc_time'] . $_REQUEST['tsr']);
                // 回傳的交易驗證壓碼
                $tac = trim($_REQUEST['tac']);
                $od_sob = trim($_REQUEST['od_sob']);
                $order = &new WC_Order($od_sob);
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
                    if ($_REQUEST['succ'] == '1') {
                        echo 'ok';
                        $order->update_status('processing', __('Payment received, awaiting fulfilment', 'EcbankVacc'));
                    }
                }
            } else {                                                                        //虛擬帳號取號
                $ecbank_gateway = $this->gateway;
                $order = &new WC_Order($order_id);
                $ecbankvacc_args = $this->get_ecbankvacc_args($order);
                //ECBank 虛擬帳號取號參數串接
                $post_str = 'mer_id=' . $ecbankvacc_args['mer_id'] .
                        '&payment_type=' . $ecbankvacc_args['payment_type'] .
                        '&setbank=' . $ecbankvacc_args['setbank'] .
                        '&enc_key=' . $ecbankvacc_args['enc_key'] .
                        '&od_sob=' . $ecbankvacc_args['od_sob'] .
                        '&amt=' . $ecbankvacc_args['amt'] .
                        '&expire_day=' . $ecbankvacc_args['expire_day'] .
                        '&ok_url=' . urlencode($ecbankvacc_args['ok_url']);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $ecbank_gateway);
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
                $strAuth = curl_exec($ch);
                if (curl_errno($ch)) {
                    $strAuth = false;
                }
                curl_close($ch);
                if ($strAuth) {
                    // 分解字串
                    parse_str($strAuth, $res);
                    // 判斷取號結果
                    if (!isset($res['error']) || $res['error'] != '0') {
                        $msg = "取號錯誤，錯誤代碼為=" . $res['error'];
                    } else {
                        $msg = '恭喜您的訂單已成立，請根據下列訊息完成繳費手續<br>轉帳銀行代碼:(<font color=blue size=+2>' . $res["bankcode"] . '</font>)' .
                                '轉帳銀行帳戶:(<font color=blue size=+2>' . $res["vaccno"] . '</font>)' .
                                "<br>持玉山銀行金融卡轉帳可免付跨行交易手續費，其它銀行依該行跨行手續費規定扣繳<br>
                        <a href=https://netbank.esunbank.com.tw/webatm/> 玉山銀行 WEB-ATM https://netbank.esunbank.com.tw/webatm/</a>";
                        $note = '綠界 ECBank交易流水号:' . $res['tsr'];
                        $msg = $msg . "<br>" . $note;
                        $woocommerce->cart->empty_cart();
                    }
                } else {
                    $msg = "取號失敗";
                }
                echo $msg;
            }
        }

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
            return array(
                'result' => 'success',
                'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('thanks'))))
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
    function add_ecbankvacc_gateway($methods) {
        $methods[] = 'WC_EcbankVacc';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_ecbankvacc_gateway');
}
?>