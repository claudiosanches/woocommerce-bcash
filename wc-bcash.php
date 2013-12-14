<?php
/**
 * Plugin Name: WooCommerce Bcash
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-bcash
 * Description: Bcash Payment Gateway for WooCommerce.
 * Author: claudiosanches
 * Author URI: http://claudiosmweb.com/
 * Version: 1.7.0
 * License: GPLv2 or later
 * Text Domain: woocommerce-bcash
 * Domain Path: /languages/
 */

/**
 * WooCommerce fallback notice.
 */
function wcbcash_woocommerce_fallback_notice() {
	echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Bcash Gateway depends on the last version of %s to work!', 'woocommerce-bcash' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>';
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
	load_plugin_textdomain( 'woocommerce-bcash', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/**
	 * Add the gateway to WooCommerce.
	 *
	 * @param  array $methods WooCommerce payment methods.
	 *
	 * @return array          Payment methods with Bcash.
	 */
	function wcbcash_add_gateway( $methods ) {
		$methods[] = 'WC_BCash_Gateway';

		return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'wcbcash_add_gateway' );

	// Include the WC_BCash_Gateway class.
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-bcash-gateway.php';
}

add_action( 'plugins_loaded', 'wcbcash_gateway_load', 0 );

/**
 * Hides the Bcash with payment method with the customer lives outside Brazil
 *
 * @param  array $available_gateways Default Available Gateways.
 *
 * @return array                     New Available Gateways.
 */
function wcbcash_hides_when_is_outside_brazil( $available_gateways ) {

	// Remove standard shipping option.
	if ( isset( $_REQUEST['country'] ) && 'BR' != $_REQUEST['country'] ) {
		unset( $available_gateways['bcash'] );
	}

	return $available_gateways;
}

add_filter( 'woocommerce_available_payment_gateways', 'wcbcash_hides_when_is_outside_brazil' );
