<?php

class ControllerPaymentAllpayBarcode extends Controller {

    protected function index() {
        $this->language->load('payment/allpay_barcode');
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $this->data['total'] = intval(round($order_info['total']));

        $allpay_gateway = "https://payment.allpay.com.tw/Cashier/AioCheckOut";
        $hash_iv = $this->config->get('allpay_barcode_iv_key');
        $hash_key = $this->config->get('allpay_barcode_hash_key');
        $merchant_id = $this->config->get('allpay_barcode_account');
        $chose_payment = "BARCODE";
        $trade_date = date("Y/m/d H:i:s");
        $total_amt = intval(round($order_info['total']));
        $return_url = $this->url->link('payment/allpay_barcode/callback');
        $trade_desc = $this->config->get('config_name') . "_網路商店";
        $item_name = $this->config->get('config_name') . "_網路商品一批";
        $back_url = $this->url->link('common/home');
        $merchant_trade_no = $this->session->data['order_id'];
        $input_array = array(
            "MerchantID" => $this->config->get('allpay_barcode_account'),
            "ChoosePayment" => $chose_payment,
            "MerchantTradeDate" => $trade_date,
            "PaymentType" => "aio",
            "ReturnURL" => $return_url,
            "TotalAmount" => $total_amt,
            "TradeDesc" => $trade_desc,
            "MerchantTradeNo" => $merchant_trade_no,
            "ItemName" => $item_name,
            "ClientBackURL" => $back_url,
        );
        $input_array["ExpireDate"]="3";
        
        ksort($input_array);
        $checkvalue = "HashKey=$hash_key&" . urldecode(http_build_query($input_array)) . "&HashIV=$hash_iv";
        $checkvalue = strtolower(urlencode($checkvalue));
        $checkvalue = md5($checkvalue);
        $input_array["CheckMacValue"] = $checkvalue;

        $shipping_fee = $this->db->query("SELECT value from `" . DB_PREFIX . "order_total` WHERE order_id = '" . $merchant_trade_no . "' and title = '" . $order_info['shipping_method'] . "'");
        $products = $this->cart->getProducts();

        $this->data['def_url'] = "<form name='form1' style='text-align:center;' method='post' action='$allpay_gateway'>";
        foreach ($input_array as $keys => $value) {
            $this->data['def_url'].="<input type='hidden' name='$keys' value='$value'>";
        }
        $this->data['def_url'] .= "</form>";
        $this->data['button_confirm'] = $this->language->get('button_confirm');

        if (isset($this->session->data['doubleclick']))
            unset($this->session->data['doubleclick']);

        $this->data['text_payment'] = $this->language->get('text_payment');
        $this->data['text_instruction'] = $this->language->get('text_instruction');
        $this->data['text_total_error'] = $this->language->get('text_total_error');

        $this->data['allpay_barcode_description'] = nl2br($this->config->get('allpay_barcode_description_' . $this->config->get('config_language_id')));
        $this->data['continue'] = $this->url->link('checkout/allpay_barcode_success');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/allpay_barcode.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/allpay_barcode.tpl';
        } else {
            $this->template = 'default/template/payment/allpay_barcode.tpl';
        }
        //$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('allpay_barcode_order_status_id'), "");
        
        $this->render();
    }

    public function confirm() {
        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('allpay_barcode_order_status_id'), "");
        $this->cart->clear();
    }

    public function curl_work($url = "", $post_type = "POST", $parameter = "") {

        if ($post_type == "GET" && $parameter != "") {
            $url = $url . "?" . $parameter;
        }
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Google Bot",
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => FALSE
        );

        if ($post_type == "POST" && $parameter != "") {
            $curl_options[CURLOPT_POST] = "1";
            $curl_options[CURLOPT_POSTFIELDS] = $parameter;
        }


        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        $result = curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_errno($ch);
        curl_close($ch);

        $return_info = array(
            "http_status" => $retcode,
            "curl_error_no" => $curl_error,
            "web_info" => $result
        );
        return $return_info;
    }

    public function callback() {
        //$_REQUEST['MerchantTradeNo'] = "65";
        $this->load->model('checkout/order');
        //$query_url = "https://payment.allpay.com.tw/Cashier/QueryTradeInfo";
        $query_url = "https://payment.allpay.com.tw/Cashier/QueryTradeInfo";

        $timestamp = time();
        $hash_iv = $this->config->get('allpay_barcode_iv_key');
        $hash_key = $this->config->get('allpay_barcode_hash_key');
        $order_id = $_REQUEST['MerchantTradeNo'];
        $mer_id = $this->config->get('allpay_barcode_account');
        $order_finish_statu = $this->config->get('allpay_barcode_order_finish_status_id');
        

        $input_array = array(
            "MerchantID" => $mer_id,
            "MerchantTradeNo" => $order_id,
            "TimeStamp" => $timestamp
        );
        ksort($input_array);
        $checkvalue = "HashKey=$hash_key&" . urldecode(http_build_query($input_array)) . "&HashIV=$hash_iv";
        $checkvalue = strtolower(urlencode($checkvalue));
        $checkvalue = md5($checkvalue);
        $input_array["CheckMacValue"] = $checkvalue;
        $post_data=http_build_query($input_array);
        $result=$this->curl_work($query_url,"POST" ,$post_data);
        parse_str($result["web_info"],$query_result);
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $system_total_amt = intval(round($order_info['total']));

        
        if($query_result["TradeStatus"]=="1" && $query_result["TradeAmt"]==$system_total_amt){
            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '$order_finish_statu', date_modified = NOW() WHERE order_id = '" . $order_id . "'");
        }

    }

}

?>