<?php //-->
/*
    Plugin Name: Paymuna for WooCommerce
    Plugin URI: http://paymuna.com
    Description: Paymuna Payment Gateway for WooCommerce
    Version: 1.0
    Author: Openovate Labs
    Author URI: http://www.openovatelabs.com
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'plugins_loaded', 'load_paymuna_plugin' );

/**
 * Loads up the Paymuna Class Integration
 */
function load_paymuna_plugin() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        add_action( 'admin_notices', 'woocommerce_paymuna_fallback_notice' );
        return;
    }

    function paymuna_custom_add_gateway( $methods ) {
        $methods[] = 'WC_Paymuna_Gateway';
        return $methods;
    }

    add_filter( 'woocommerce_payment_gateways', 'paymuna_custom_add_gateway' );

    // include class file
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-gateway-paymuna.php';
}

/**
 * WooCommerce Fallback Notice
 */
function woocommerce_paymuna_fallback_notice() {
    echo '<div class="error"><p>' . sprintf( __( 'Paymuna WooCommerce Payment Gateway depends on the last version of %s to work!', 'paymuna' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>';
}
