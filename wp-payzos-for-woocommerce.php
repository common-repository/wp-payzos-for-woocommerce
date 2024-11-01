<?php
/*
Plugin name: wp-payzos-for-woocommerce
Plugin URI: https://gitlab.com/payzos/wp-payzos-payment-woocommerce
Description:  Payzos is Tezos payment service
Version: 3.1.0
Author: Johan Peterson
Author URI: https://gitlab.com/payzos
Text Domain: -e('wp payzos for woocommerce','wp-payzos-for-woocommerce')
 */
defined('ABSPATH') || exit('No Direct Access.');
define('WP_PAYZOS_FOR_WOOCOMMERCE_DIR', plugin_dir_path(__FILE__));
define('WP_PAYZOS_FOR_WOOCOMMERCE_URL', plugin_dir_url(__FILE__));
define(
    'WP_PAYZOS_FOR_WOOCOMMERCE_ASSETS_URL',
    trailingslashit(WP_PAYZOS_FOR_WOOCOMMERCE_URL . 'assets')
);
define('WP_PAYZOS_FOR_WOOCOMMERCE_APP_DIR', trailingslashit(WP_PAYZOS_FOR_WOOCOMMERCE_DIR . 'app'));
define('WP_PAYZOS_FOR_WOOCOMMERCE_VERSION', '3.1.0');
require WP_PAYZOS_FOR_WOOCOMMERCE_APP_DIR . 'controller/WP_PAYZOS_FOR_WOOCOMMERCE_Controller.php';
add_action('init', 'WP_PAYZOS_FOR_WOOCOMMERCE_load');

function WP_PAYZOS_FOR_WOOCOMMERCE_load()
{
    $app = new WP_PAYZOS_FOR_WOOCOMMERCE_Controller();
}

register_activation_hook(__FILE__, 'WP_PAYZOS_FOR_WOOCOMMERCE_Installation');

function WP_PAYZOS_FOR_WOOCOMMERCE_Installation()
{
    WP_PAYZOS_FOR_WOOCOMMERCE_Controller::install();
}
