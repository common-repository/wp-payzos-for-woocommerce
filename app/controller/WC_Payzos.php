<?php
defined('ABSPATH') || exit('No Direct Access.');

/**
 * woocommerce payment configure class
 */
class WC_Payzos extends WC_Payment_Gateway
{
    /**
     * @var [type]
     */
    private $payzos_api;

    /**
     * Undocumented function
     */
    public function __construct()
    {
        $this->payzos_api = new WP_PAYZOS_FOR_WOOCOMMERCE_Payzos_Api();

        $this->id = 'tezos_payment_gateway';
        // $this->method_title = __("Payzos", 'wp-payzos-for-woocommerce');
        $this->method_description = __(
            'Payzos is Tezos payment service',
            'wp-payzos-for-woocommerce'
        );
        $this->title = __('Payzos', 'wp-payzos-for-woocommerce');
        $this->description = __('Payzos is Tezos payment service', 'wp-payzos-for-woocommerce');
        $this->icon = WP_PAYZOS_FOR_WOOCOMMERCE_ASSETS_URL . 'img/logo_40_40.png';
        $this->has_fields = true;
        $this->init_form_fields();
        $this->init_settings();

        // Turn these settings into variables we can use
        foreach ($this->settings as $setting_key => $value) {
            $this->$setting_key = $value;
        }

        // Save settings
        if (is_admin()) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [
                $this,
                'process_wallet_hash',
            ]);
        }
    }

    /**
     * will override of init_form_fields
     * wath this : https://docs.woocommerce.com/wc-apidocs/class-WC_Settings_API.html#_init_form_fields
     *
     * @return array
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title' => __('Enable / Disable', 'wp-payzos-for-woocommerce'),
                'label' => __('Enable this payment gateway', 'wp-payzos-for-woocommerce'),
                'type' => 'checkbox',
                'default' => 'no',
            ],
            'wallet_hash' => [
                'title' => __('XTZ address', 'wp-payzos-for-woocommerce'),
                'type' => 'text',
                'desc_tip' => __(
                    'we need public hash of your Tezos wallet to make a payment gateway',
                    'wp-payzos-for-woocommerce'
                ),
            ],
        ];
    }

    /**
     *
     * i did this because we must to validate tezos wallet hash.
     *
     * @return boolean
     */
    public function process_wallet_hash()
    {
        $my_form = $this->get_post_data();
        $wallet_hash = $my_form['woocommerce_tezos_payment_gateway_wallet_hash'];
        if (!is_string($wallet_hash)) {
            $this->add_error(__('wrong input', 'wp-payzos-for-woocommerce'));
            $this->display_errors();
            return false;
        }
        if (!$this->payzos_api->validate_wallet_hash($wallet_hash)) {
            $this->add_error(__('unvalid wallet_hash', 'wp-payzos-for-woocommerce'));
            $this->display_errors();
            return false;
        }
        return $this->process_admin_options();
    }

    /**
     * Undocumented function
     *
     * @param int $_order_id
     * @return void|array [result, redirect]
     */
    public function process_payment($_order_id)
    {
        if (!is_numeric($_order_id)) {
            wc_add_notice(__("unvalid order_id", 'wp-payzos-for-woocommerce'), 'error');
            return false;
        }
        $page_url = get_rest_url(null, 'wp_payzos_wc/v3/back_url');
        $url = add_query_arg('order_id', $_order_id, $page_url);

        $customer_order = new WC_Order($_order_id);
        if (!$customer_order || is_wp_error($customer_order) || empty($customer_order)) {
            wc_add_notice(__("unvalid order", 'wp-payzos-for-woocommerce'), 'error');
            return false;
        }
        $payment = $this->payzos_api->make_payment(
            $this->wallet_hash,
            $customer_order->get_total(),
            $customer_order->get_currency(),
            esc_url_raw($url)
        );
        if (!$payment) {
            wc_add_notice(__("Payzos can't make Payment", 'wp-payzos-for-woocommerce'), 'error');
            return false;
        }
        if (!isset($payment["url"]) || !isset($payment["payment_id"])) {
            wc_add_notice(__("Payzos : unvalid payment", 'wp-payzos-for-woocommerce'), 'error');
            return false;
        }
        return [
            'result' => 'success',
            'redirect' => esc_url_raw($payment["url"]),
        ];
    }
}
