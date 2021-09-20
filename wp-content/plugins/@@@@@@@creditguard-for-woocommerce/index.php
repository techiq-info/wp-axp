<?php
/*
Plugin Name: CreditGuard for WooCommerce
Description: CreditGuard Payment gateway for WooCommerce
Plugin URI: https://www.directpay.co.il
Author URI: https://www.directpay.co.il
Author: DirectPay E-commerce Plugins
License: One Public Domain
Version: 3.0.0
* WC requires at least: 3.3.0
* WC tested up to: 4.9.0
* Tested up to: 5.6

*/

add_action('plugins_loaded', 'woocommerce_cg_init');

function woocommerce_cg_init() {
    load_plugin_textdomain('creditguard', false, dirname(plugin_basename(__FILE__)) . '/lang/');
}

include('helpers/main.php');

if (!class_exists('wp_auto_update')) {
	require_once ('helpers/wp_autoupdate.php');
}

$plugin_current_version = '3.0.0';
$plugin_remote_path = 'https://update.maxstore.co.il/creditguard-for-woocommerce.php';
$plugin_slug = plugin_basename(__FILE__);
new wp_auto_update ($plugin_current_version, $plugin_remote_path, $plugin_slug);
?>