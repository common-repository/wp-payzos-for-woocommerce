=== wp Payzos for Woocommerce ===
Contributors: payzos
Donate link: https://gitlab.com/payzos/
Tags: payzos, tezos, payment, woocommerce, cryptocurrency
Requires at least: 5.0
Tested up to: 5.4.1
Stable tag: 3.1.0
Requires PHP: 7.0
License: GPLv3
License URI: https://gitlab.com/payzos/wp-payzos-payment-woocommerce/-/blob/master/LICENSE

Payzos is Tezos payment service. now available for woocommerce

== Description ==

**Accept XTZ today**.

Payzos is set of plugins for different E-commerce platforms that lets you set up Tezos as a payment method for your online store.

**Features :**
- **No Third Party** - All Payzos payments will be directly sent to your own address. No intermediaries.
- **Zero cost** - Low fees and fast confirmation because we use the Tezos blockchain
- **UI / UX** - The pleasant experience of working with a modern payment service


== Installation ==

2. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Use the WooCommerce->Settings->payments->manage to enable payment and add more info
5. put your wallet public key here.
6. make sure you have a page with title `Payzos` and `[wp-payzos-for-woocommerce]` content ( this page will appear when user's payment done )


== Screenshots ==

1. Preview of Payzos payment setting page
2. Priview of Payzos payment alongside of other payments in order checkout
3. Preview Payzos payment page
4. Preview of succesfull payment page 

== Changelog ==

= V0.1.0 =

-   init payzos payment plugin

= V1.0.0 Beta =

-   a good ui for payment page (with react, watch [payment-ui](https://gitlab.com/payzos/payment-ui))
-   check a wallet address to verify a payment with a time and amount.
-   wordpress rest api for manage payment.
-   check is valid wallet hash in payment setting (default woocommerce payments page)

= V1.0.0 =

-   fix critical error when woocommerce is disable
-   fix payment-ui on someof templates
-   empty cart and refresh woocommerce session after payment mark as success.

= V2.0.0 =

-   add transaction page in admin dashboard
-   make "PZ{18RANDOM NUMBER}" structure for payment_id
-   smart installation

= V3.0.0 =

-   connect to Payzos.io (instead of huge proccesses in plugin)
-   remove payment-ui

= V3.1.0 =

-   change name from wp-payzos-plugin-woocommerce to wp-payzos-for-woocommerce
-   fix some security issue
-   better documentation

== Support ==

-   [gitlab issues](https://gitlab.com/payzos/wp-payzos-payment-woocommerce/-/issues)
-   mailto : `info[at]payzos.io`
-   [payzos.io](https://payzos.io)
