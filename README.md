[![Payzos logo](/.gitassets/img/logo.png "Payzos logo")](https://payzos.io)

# Wordpress Payzos payment for Woocommerce

[Payzos](https://payzos.io) is payment service for Tezos network.

# Installation

you must to put our plugin in `WORDPRESS/wp-content/plugins/`

then activeate it from wordpress dashboard

you can clone from git or ~~install from wordpress official repository~~ (coming soon).

## Pre release (git)

```
git clone https://gitlab.com/payzos/wp-payzos-payment-woocommerce.git  WORDPRESS_PATH/wp-content/plugins/wp-payzos-for-woocommerce
```

and if you want to install on remote host. just clone from git and make a zip of plugin folder. then you can upload and install it to wordpress.\

Also you can download last realease zip file from [gitlab repository release page](https://gitlab.com/payzos/wp-payzos-payment-woocommerce/-/releases)

## from wordpress official repository

just install from [Payzos payment for Woocommerce](https://wordpress.org/plugins/wp-payzos-for-woocommerce/)

# plugin configuration

-   before activate plugin make sure your woocommerce is working.
-   in `woocommerce > settings > payments > payzos > setting` add your **wallet hash**
-   enable payment
-   make sure you have page with `Payzos` title
-   if there is no payzos page after activation done you must to make it by yourself and put `[wp-payzos-for-woocommerce]` shortcode on it.

# issues

if you found any problem or you have any question please contant us.

-   [gitlab issues](https://gitlab.com/payzos/wp-payzos-payment-woocommerce/-/issues)
-   mailto : `info[at]payzos.io`

# License

GPL-v3
