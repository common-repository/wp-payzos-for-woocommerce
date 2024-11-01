<?php
defined('ABSPATH') || exit('No Direct Access.');

include WP_PAYZOS_FOR_WOOCOMMERCE_APP_DIR . 'controller/WP_PAYZOS_FOR_WOOCOMMERCE_RestApi.php';
include WP_PAYZOS_FOR_WOOCOMMERCE_APP_DIR . 'controller/WP_PAYZOS_FOR_WOOCOMMERCE_Payzos_Api.php';

class WP_PAYZOS_FOR_WOOCOMMERCE_Controller
{
    /**
     * @var [type]
     */
    private $model;
    /**
     * @var [type]
     */
    private $payzos_api;

    /**
     * constructor.
     * define and register some configure
     */
    public function __construct()
    {
        // Check if woocommerce is active
        if (!class_exists('WooCommerce')) {
            return false;
        }

        $this->payzos_api = new WP_PAYZOS_FOR_WOOCOMMERCE_Payzos_Api();
        $this->rest_api = new WP_PAYZOS_FOR_WOOCOMMERCE_RestApi();
        /**
         * initialize woocommerce payment gateway
         */
        add_filter('woocommerce_payment_gateways', [$this, 'payzos_gateway_init']);
        // add_action('plugins_loaded', [$this, 'init']);
        $this->init();
        /**
         * initialize wordpress text domain (multilanguage)
         */
        add_action('plugins_loaded', [$this, 'textdomain']);
        /**
         * initialize wordpress rest api for manage and validate our payment
         */
        add_action('rest_api_init', [$this, 'payzos_rest_api']);
        /**
         * initialize shortcode for make payment page
         */
        add_shortcode('wp-payzos-for-woocommerce', [$this, 'payzos_payment_page']);
    }

    /**
     * init payzos payment gateway for woocommerce
     * 
     * @param mixed $methods
     *
     * @return [type]
     */
    public function payzos_gateway_init($methods)
    {
        $methods[] = 'WC_Payzos';
        return $methods;
    }

    /** 
     * include WC_Payzos after payment initialize get done
     * @return [type]
     */
    public function init()
    {
        include WP_PAYZOS_FOR_WOOCOMMERCE_APP_DIR . 'controller/WC_Payzos.php';
    }

    /**
     * initialize textdomain
     */
    public function textdomain()
    {
        load_plugin_textdomain(
            'wp-payzos-for-woocommerce',
            false,
            WP_PAYZOS_FOR_WOOCOMMERCE_DIR . 'languages/'
        );
    }

    /**
     * initialize rest api to handle back_url 
     */
    public function payzos_rest_api()
    {
        $this->rest_api->init();
    }

    /**
     * @param array $_data
     * @param string $_key
     * @param string $_value
     * @param bool $_return
     *
     * @return array|null (on null will show success page with HTML output)
     */
    private function payment_success_view($_data, $_key, $_value, $_return = false)
    {
        if (!isset($_data['error'])) {
            $_data[$_key] = $_value;
        }
        if ($_return) {
            include WP_PAYZOS_FOR_WOOCOMMERCE_APP_DIR .
                'view/WP_PAYZOS_FOR_WOOCOMMERCE_Success_Page.php';
            return;
        }
        return $_data;
    }

    /**
     * @return [type]
     */
    public function payzos_payment_page()
    {
        $data = [];
        if (!isset($_GET['payment_id']) || !isset($_GET['order_id'])) {
            return $this->payment_success_view($data, "error", "Not Found", true);
        }
        $order_id = sanitize_text_field($_GET['order_id']);
        $payment_id = sanitize_text_field($_GET['payment_id']);
        $payment = $this->payzos_api->get_payment($payment_id);
        if (!$payment) {
            return $this->payment_success_view($data, "error", "Payment is unvalid", true);
        }
        if (
            !isset($payment["payment_id"]) ||
            !isset($payment["transaction_hash"]) ||
            !isset($payment["update_time"]) ||
            !isset($payment["real_amount"]) ||
            !isset($payment["status"]) ||
            !isset($payment["amount"])
        ) {
            return $this->payment_success_view($data, "error", "Payment is unvalid", true);
        }
        if ($payment["status"] !== "approved") {
            return $this->payment_success_view($data, "error", "Payment is not completed", true);
        }
        $order = new WC_Order($order_id);
        $order_real_amount = $order->get_total() . " " . $order->get_currency();
        if ($order_real_amount !== $payment["real_amount"]) {
            return $this->payment_success_view($data, "error", "unvalid payment total", true);
        }
        $order->payment_complete();
        if (function_exists('wc_empty_cart')) {
            WC()->cart->empty_cart(true);
        }
        $data = $this->payment_success_view($data, 'payment_id', $payment["payment_id"]);
        $data = $this->payment_success_view(
            $data,
            'transaction_hash',
            $payment["transaction_hash"]
        );
        $date_time = date('M-D-Y h:m:s A', $payment['update_time'] - date('Z')) . ' UTC';
        $data = $this->payment_success_view($data, 'date', $date_time);
        $data = $this->payment_success_view(
            $data,
            'amount',
            $payment['real_amount'] . " | " . $payment['amount'] / 1000000 . " XTZ"
        );
        $this->payment_success_view($data, 'error', false, true);
    }

    /**
     * Install method run when plugin going to enable.
     *
     * @return boolean
     */
    public static function install()
    {
        $my_post = get_page_by_path('payzos', ARRAY_A);
        if (is_array($my_post)) {
            if ($my_post['post_content'] === '[wp-payzos-for-woocommerce]') {
                update_option('WP_PAYZOS_FOR_WOOCOMMERCE_page_id', $my_post['ID'], '', 'no');
                return true;
            }
            $update_result = wp_update_post([
                'ID' => $my_post['ID'],
                'post_content' => '[wp-payzos-for-woocommerce]',
            ]);
            if ($update_result) {
                update_option('WP_PAYZOS_FOR_WOOCOMMERCE_page_id', $update_result, '', 'no');
                return $update_result;
            }
        }
        $post_id = wp_insert_post([
            'comment_status' => 'closed',
            'post_name' => 'payzos',
            'post_title' => 'payzos',
            'post_content' => '[wp-payzos-for-woocommerce]',
            'post_status' => 'publish',
            'post_type' => 'page',
        ]);

        if ($post_id) {
            update_option('WP_PAYZOS_FOR_WOOCOMMERCE_page_id', $post_id, '', 'no');
            return true;
        }
        return false;
    }
}
