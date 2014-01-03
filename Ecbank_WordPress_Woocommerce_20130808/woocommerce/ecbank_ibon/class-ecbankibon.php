<?php
/**
 * Plugin Name: Ecbank_7-11 Ibon 繳費
 * Plugin URI: http://www.ecbank.com.tw
 * Description: Ecbank7-11 Ibon 收款模組
 * Author: 綠界科技 Ecbank
 * Author URI: http://www.ecbank.com.tw
 * Version: 1.0
 * 
 * ibon Payment Gateway
 * 綠界科技7-11 Ibon 收款模組
 * Provides a ibon Standard Payment Gateway.
 * Plugin Name:                EcBank_ibon
 * @class 		EcBank_ibon
 * @extends		WC_Payment_Gateway
 * @version		1.0
 * @author 		GreenWorld Roger    
 */
add_action('plugins_loaded', 'ecbankibon_gateway_init', 0);

function ecbankibon_gateway_init() {
//load_plugin_textdomain( 'ecbankibon', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/'  ); 
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_Ecbankibon extends WC_Payment_Gateway {

        /**
         * Constructor for the gateway.
         *
         * @access public
         * @return void
         */
        public function __construct() {
            $this->id = 'Ecbankibon';
            $this->icon = apply_filters('woocommerce_ecbankibon_icon', plugins_url('icon/green_log_40.gif', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('Ecbankibon', 'woocommerce');

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
            //add_action('init', array(&$this, 'check_Ecbankibon_response'));
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action('woocommerce_thankyou_Ecbankibon', array($this, 'thankyou_page'));  //需與id名稱大小寫相同
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
                    'label' => __('啟動 Ecbank7-11 Ibon 收款模組', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('標題', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('客戶在結帳時所看到的標題', 'woocommerce'),
                    'default' => __('Ecbank7-11 Ibon 收款', 'woocommerce')
                ),
                'description' => array(
                    'title' => __('客戶訊息', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('', 'woocommerce'),
                    'default' => __(' 綠界 ECBank - <font color=red>7-11 Ibon 繳費</font>', 'woocommerce')
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
            <h3><?php _e('綠界 Ecbank 7-11 Ibon 收款模組', 'woocommerce'); ?></h3>
            <p><?php _e('此模組可以讓您使用綠界科技的Ecbank 7-11 Ibon收款功能', 'woocommerce'); ?></p>
            <table class="form-table">
                <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
                ?>
            </table><!--/.form-table-->
            <?php
        }
        /**
         * Get Ecbankibon Args for passing to Ecbank
         *
         * @access public
         * @param mixed $order
         * @return array
         */
        function get_ecbankibon_args($order) {
            global $woocommerce;

            $paymethod = 'ibon';
            $order_id = $order->id;

            $ecbankibon_args = array(
                "mer_id" => $this->mer_id,
                "payment_type" => $paymethod,
                "od_sob" => $order_id,
                'enc_key' => $this->check_code,
                "amt" => round($order->get_order_total()),
                "prd_desc" => "綠界科技 7-11 ibon收款服務",
                "ok_url" => $this->get_return_url($order)
            );
            $ecbankibon_args = apply_filters('woocommerce_ecbankibon_args', $ecbankibon_args);
            return $ecbankibon_args;
        }

        /**
         * Output for the order received page.
         * @access public
         * @return void
         */
        function thankyou_page($order_id) {  //接收回傳參數驗證  與  7-11 Ibon取號
            global $woocommerce;
            if ($description = $this->get_description())
                echo wpautop(wptexturize($description));

            if ($_REQUEST['payment_type'] == 'ibon') {  //接收回傳參數驗證
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
                    if (($_REQUEST['succ'] == '1') && (round($order->get_total()) == round($amt))) {
                        echo 'ok';
                        $order->update_status('processing', __('Payment received, awaiting fulfilment', 'Ecbankibon'));
                    }
                }
            } else {                                                                        //7-11 Ibon取號
                $ecbank_gateway = $this->gateway;
                $order = &new WC_Order($order_id);
                $ecbankibon_args = $this->get_ecbankibon_args($order);
                //ECBank 7-11 Ibon帳號取號參數串接
                $post_str = 'mer_id=' . $ecbankibon_args['mer_id'] .
                        '&payment_type=' . $ecbankibon_args['payment_type'] .
                        '&prd_desc=' . $ecbankibon_args['prd_desc'] .
                        '&enc_key=' . $ecbankibon_args['enc_key'] .
                        '&od_sob=' . $ecbankibon_args['od_sob'] .
                        '&amt=' . $ecbankibon_args['amt'] .
                        '&ok_url=' . urlencode($ecbankibon_args['ok_url']);
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
                        $msg = "取號錯誤，錯誤代碼=".$res['error'];
                    } else {
                        $msg = '恭喜您的訂單已成立，請根據下列訊息完成繳費手續<br>'.
                               '[統一超商7-Eleven]超商繳費代碼:(<font color=blue size=+2>'.$res['payno'].'</font>)<br><br>'.
                                '<a href=http://www.ecbank.com.tw/expenses-ibon.htm target=_blank>統一超商7-Eleven ibon 門市操作步驟</a><br>'.
                             '請記下上列超商繳費代碼,至最近之統一超商7-Eleven便利商店,操作代碼繳費機台, 於列印出有調碼之繳款單後,至櫃台支付,
                                 便可完成繳費,繳費之收據請留存以供備核,繳費之後才算完成購物流程'.
                             '<br><br>本線上金流機制採用&lt; <a href=http://www.ecbank.com.tw target=_blank>綠界科技 ECBank 線上支付平台</a> &gt;';
                       $note = '綠界 ECBank 交易流水号:'.$res['tsr'];		
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
    function add_ecbankibon_gateway($methods) {
        $methods[] = 'WC_Ecbankibon';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_ecbankibon_gateway');
}
?>