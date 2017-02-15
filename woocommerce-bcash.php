<?php
/**
 * Plugin Name: WooCommerce Bcash
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-bcash
 * Description: Bcash Payment Gateway for WooCommerce.
 * Author: Claudio Sanches
 * Author URI: http://claudiosmweb.com/
 * Version: 1.13.1
 * License: GPLv2 or later
 * Text Domain: woocommerce-bcash
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Bcash' ) ) :

/**
 * WooCommerce Bcash main class.
 */
class WC_Bcash {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.13.1';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin public actions.
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks with WooCommerce is installed.
		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			$this->includes();

			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'hides_when_is_outside_brazil' ) );
			add_filter( 'woocommerce_cancel_unpaid_order', array( $this, 'stop_cancel_unpaid_orders' ), 10, 2 );
		} else {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-bcash', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 */
	private function includes() {
		// Handle WooCommerce 2.7 compatibility.
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7.0', '<' ) ) {
			include_once dirname( __FILE__ ) . '/includes/class-wc-bcash-legacy-gateway.php';
		} else {
			include_once dirname( __FILE__ ) . '/includes/class-wc-bcash-gateway.php';
		}
	}

	/**
	 * Action links.
	 *
	 * @param  array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array();

		$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bcash' ) ) . '">' . __( 'Settings', 'woocommerce-bcash' ) . '</a>';

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Add the gateway to WooCommerce.
	 *
	 * @param   array $methods WooCommerce payment methods.
	 *
	 * @return  array          Payment methods with Bcash.
	 */
	public function add_gateway( $methods ) {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7.0', '<' ) ) {
			$methods[] = 'WC_Bcash_Legacy_Gateway';
		} else {
			$methods[] = 'WC_Bcash_Gateway';
		}

		return $methods;
	}

	/**
	 * Hides the Bcash with payment method with the customer lives outside Brazil.
	 *
	 * @param   array $available_gateways Default Available Gateways.
	 *
	 * @return  array                     New Available Gateways.
	 */
	public function hides_when_is_outside_brazil( $available_gateways ) {
		// Remove Bcash gateway.
		if ( isset( $_REQUEST['country'] ) && 'BR' != $_REQUEST['country'] ) {
			unset( $available_gateways['bcash'] );
		}

		return $available_gateways;
	}

	/**
	 * Stop cancel unpaid Bcash orders.
	 *
	 * @param  bool     $cancel
	 * @param  WC_Order $order
	 *
	 * @return bool
	 */
	public function stop_cancel_unpaid_orders( $cancel, $order ) {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7.0', '<' ) ) {
			$payment_method = $order->payment_method;
		} else {
			$payment_method = $order->get_payment_method();
		}

		if ( 'bcash' === $payment_method ) {
			return false;
		}

		return $cancel;
	}

	/**
	 * WooCommerce fallback notice.
	 *
	 * @return string
	 */
	public function woocommerce_missing_notice() {
		include dirname( __FILE__ ) . '/includes/admin/views/html-notice-missing-woocommerce.php';
	}
}

add_action( 'plugins_loaded', array( 'WC_Bcash', 'get_instance' ) );

endif;
