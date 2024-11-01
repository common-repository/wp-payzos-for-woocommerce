<?php
defined('ABSPATH') || exit('No Direct Access.');

/**
 * [Description WP_PAYZOS_FOR_WOOCOMMERCE_RestApi]
 */
class WP_PAYZOS_FOR_WOOCOMMERCE_RestApi
{
    /**
     * @var [type]
     */
    private $api;

    /**
     */
    public function __construct()
    {
        $this->api = new WP_PAYZOS_FOR_WOOCOMMERCE_Payzos_Api();
    }

    /**
     * @return [type]
     */
    public function init()
    {
        register_rest_route('wp_payzos_wc/v3', 'back_url', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_handle_back_url'],
        ]);
    }

    /**
     * @param mixed $_message
     *
     * @return [type]
     */
    private function error_api_output($_message)
    {
        $output = [];
        $output['ok'] = false;
        $output['message'] = $_message;
        return $output;
    }

    /**
     * @param mixed $_data
     *
     * @return [type]
     */
    private function data_api_output($_data)
    {
        $output = [];
        $output['ok'] = true;
        $output['data'] = $_data;
        return $output;
    }

    /**
     * route  : wp_payzos_wc/v3/back_url?order_id=STRING
     * request : POST
     * inputs : {"payment_id" : string (payzos payment_id) }, 
     * $_GET["order_id"] : string (woocommerce order_id)
     * 
     * 
     * @return string(json)
     */
    public function rest_handle_back_url()
    {
        $data = file_get_contents('php://input');
        if (empty($data) || !is_string($data)) {
            return $this->error_api_output('unvalid request');
        }
        $data = json_decode($data, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            return $this->error_api_output('unvalid input');
        }
        if (!isset($data['payment_id']) || !isset($_GET['order_id'])) {
            return $this->error_api_output('fill values');
        }
        $payment_id = sanitize_text_field($data['payment_id']);
        $order_id = sanitize_text_field($_GET['order_id']);
        if (!is_numeric($order_id)) {
            return $this->error_api_output('unvalid order_id');
        }
        $api_response = $this->api->get_payment($payment_id);
        if (!$api_response) {
            return $this->error_api_output('wrong payment');
        }
        if (!isset($api_response['status']) || $api_response['status'] !== 'approved') {
            return $this->error_api_output('wrong payment');
        }
        $payzos_page_id = get_option('WP_PAYZOS_FOR_WOOCOMMERCE_page_id', false);
        if (!$payzos_page_id) {
            return false;
        }
        $page_url = get_permalink($payzos_page_id);
        $url = add_query_arg(
            [
                'payment_id' => $payment_id,
                'order_id' => $order_id,
            ],
            $page_url
        );
        return ["redirect_url" => esc_url_raw($url)];
    }
}
