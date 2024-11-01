<?php
defined('ABSPATH') || exit('No Direct Access.');

class WP_PAYZOS_FOR_WOOCOMMERCE_Payzos_Api
{
    private $endpoint = 'https://api.payzos.io';

    /**
     * constructor
     */
    public function __construct()
    {
    }

    /**
     * @param string $_destination_hash
     * @param float $_total
     * @param string $_currency (string with 3 char like : USD)
     * @param mixed $_back_url
     *
     * @return boolean|array(payment_id : string, url : string)
     */
    public function make_payment($_destination_hash, $_total, $_currency, $_back_url)
    {
        $url = $this->endpoint . '/api/make_payment';
        if (!$this->sanitize_wallet_hash($_destination_hash)) {
            return false;
        }
        if (
            !is_numeric($_total) ||
            !$this->validate_currency($_currency) ||
            !is_string($_back_url)
        ) {
            return false;
        }
        $data = [
            'destination_hash' => $_destination_hash,
            'total' => floatval($_total),
            'currency' => $_currency,
            'back_url' => esc_url_raw($_back_url),
        ];
        $response = wp_remote_post($url, [
            'body' => json_encode($data),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        if (is_wp_error($response)) {
            return false;
        }
        $response = json_decode(wp_remote_retrieve_body($response), true);
        if (
            !isset($response['ok']) | !$response['ok'] ||
            !isset($response['data']) ||
            !is_array($response['data'])
        ) {
            return false;
        }
        if (!isset($response['data']['payment_id']) || !isset($response['data']['url'])) {
            return false;
        }
        $payment_id = $this->sanitize_payment_id($response['data']['payment_id']);
        if (!$payment_id) {
            return false;
        }
        return [
            "payment_id" => $payment_id,
            "url" => esc_url_raw($response['data']['url']),
        ];
    }

    /**
     * @param string $_payment_id (payzos payment id start with 'PZ')
     *
     * @return boolean|array(
     *  id : int,
     *  payment_id : string (will not start with 'PZ' it's just contain 16 random number),
     *  back_url : string(url),
     *  website : string,
     *  amount : int,
     *  real_amount : string (like 1 USD)
     *  destination_hash : string (start with 'tz1'),
     *  origin_hash : null|string (start with 'tz1'),
     *  transaction_hash : string,
     *  start_time : int (unix time),
     *  update_time : int (unix time),
     *  status : string (approved - canceled - pending)
     * )
     */
    public function get_payment($_payment_id)
    {
        $payment_id = $this->sanitize_payment_id($_payment_id);

        if (!$payment_id) {
            return false;
        }
        $url = $this->endpoint . '/api/payment/' . $payment_id;
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return false;
        }
        $result = json_decode(wp_remote_retrieve_body($response), true);
        if (
            !isset($result['ok']) ||
            !$result['ok'] ||
            !isset($result['data']) ||
            !is_array($result['data'])
        ) {
            return false;
        }
        $output = filter_var_array($result['data'], [
            'id' => [
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'flags' => FILTER_NULL_ON_FAILURE,
            ],
            "payment_id" => [
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_NULL_ON_FAILURE,
            ],
            "back_url" => [
                'filter' => FILTER_SANITIZE_URL,
                'flags' => FILTER_NULL_ON_FAILURE,
            ],
            "website" => [
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_NULL_ON_FAILURE,
            ],
            "amount" => [
                'filter' => FILTER_VALIDATE_INT,
                'flags' => FILTER_NULL_ON_FAILURE,
            ],
            "real_amount" => [
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_NULL_ON_FAILURE,
            ],
            "destination_hash" => [
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_NULL_ON_FAILURE,
            ],
            "origin_hash" => [
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_NULL_ON_FAILURE,
            ],
            "transaction_hash" => [
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_NULL_ON_FAILURE,
            ],
            "start_time" => [
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'flags' => FILTER_NULL_ON_FAILURE,
            ],
            "update_time" => [
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'flags' => FILTER_NULL_ON_FAILURE,
            ],
            "status" => [
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_NULL_ON_FAILURE,
            ],
        ]);
        return $output;
    }

    /**
     * check the public_key in tezos network
     *
     * @param string $_public_key (start with 'tz1')
     *
     * @return boolean
     */
    public function validate_wallet_hash($_public_key)
    {
        if (!$this->sanitize_wallet_hash($_public_key)) {
            return false;
        }

        $url = $this->endpoint . '/api/wallet/validation/' . $_public_key;
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return false;
        }
        $result = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($result['ok']) && $result['ok']) {
            return true;
        }
        return false;
    }

    /**
     * @param string  $_payment_id (start with 'PZ')
     *
     * @return string
     */
    private function sanitize_payment_id($_payment_id)
    {
        if ($_payment_id[0] !== 'P' || $_payment_id[1] !== 'Z') {
            return false;
        }
        return filter_var($_payment_id, FILTER_SANITIZE_STRING);
    }

    /**
     * check is user input hash a valid tezos wallet hash ?
     *
     * @param string $_wallet_hash
     * @return boolean
     */
    public function sanitize_wallet_hash($_wallet_hash)
    {
        if ($_wallet_hash[0] !== 't' || $_wallet_hash[1] !== 'z') {
            return false;
        }
        return true;
    }

    /**
     * check user input is valid currency type (must have 3 word) more sanitize will happen in server side when make new payment (we can support certein type of currencies)
     *
     * @param string $_string
     * @return boolean
     */
    public function validate_currency($_string)
    {
        if (strlen($_string) !== 3) {
            return false;
        }
        return true;
    }
}
