<?php
/**
 * Plugin Name: WooCommerce Bcash
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-bcash
 * Description: Gateway de pagamento Bcash para WooCommerce.
 * Author: claudiosanches
 * Author URI: http://claudiosmweb.com/
 * Version: 1.5.0
 * License: GPLv2 or later
 * Text Domain: wcbcash
 * Domain Path: /languages/
 */

/**
 * WooCommerce fallback notice.
 */
function wcbcash_woocommerce_fallback_notice() {
    echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Bcash Gateway depends on the last version of %s to work!', 'wcbcash' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>';
}

/**
 * Load functions.
 */
function wcbcash_gateway_load() {

    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        add_action( 'admin_notices', 'wcbcash_woocommerce_fallback_notice' );

        return;
    }

    /**
     * Load textdomain.
     */
    load_plugin_textdomain( 'wcbcash', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    /**
     * Add the gateway.
     *
     * @access public
     * @param array $methods
     * @return array
     */
    add_filter( 'woocommerce_payment_gateways', 'wcbcash_add_gateway' );

    function wcbcash_add_gateway( $methods ) {
        $methods[] = 'WC_BCash_Gateway';

        return $methods;
    }

    // Include the WC_BCash_Gateway class.
    require_once plugin_dir_path( __FILE__ ) . 'class-wc-bcash-gateway.php';
}

add_action( 'plugins_loaded', 'wcbcash_gateway_load', 0 );

/**
 * Adds support to legacy IPN.
 *
 * @return void
 */
function wcbcash_legacy_ipn() {
    if ( isset( $_POST['id_pedido'] ) && ! isset( $_GET['wc-api'] ) ) {
        global $woocommerce;

        $woocommerce->payment_gateways();

        do_action( 'woocommerce_api_wc_bcash_gateway' );
    }
}

add_action( 'init', 'wcbcash_legacy_ipn' );

/**
 * Hides the Bcash with payment method with the customer lives outside Brazil
 *
 * @param  array $available_gateways Default Available Gateways.
 *
 * @return array                     New Available Gateways.
 */
function wcbcash_hides_when_is_outside_brazil( $available_gateways ) {

    // Remove standard shipping option.
    if ( isset( $_REQUEST['country'] ) && $_REQUEST['country'] != 'BR' )
        unset( $available_gateways['bcash'] );

    return $available_gateways;
}

add_filter( 'woocommerce_available_payment_gateways', 'wcbcash_hides_when_is_outside_brazil' );

/**
 * Adds custom settings url in plugins page.
 *
 * @param  array $links Default links.
 *
 * @return array        Default links and settings link.
 */
function wcbcash_action_links( $links ) {

    $settings = array(
        'settings' => sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_BCash_Gateway' ),
            __( 'Settings', 'wcbcash' )
        )
    );

    return array_merge( $settings, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wcbcash_action_links' );
