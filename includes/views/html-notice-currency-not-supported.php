<?php
/**
 * Admin View: Notice - Currency not supported.
 *
 * @package WooCommerce_Bcash/Admin/Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error inline">
	<p><strong><?php _e( 'Bcash Disabled', 'woocommerce-bcash' ); ?></strong>: <?php printf( __( 'Currency <code>%s</code> is not supported. Works only with Brazilian Real.', 'woocommerce-bcash' ), get_woocommerce_currency() ); ?>
	</p>
</div>
