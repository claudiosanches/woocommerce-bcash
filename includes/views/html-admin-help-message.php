<?php
/**
 * Admin help message.
 *
 * @package WooCommerce_Bcash/Admin/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( apply_filters( 'woocommerce_bcash_help_message', true ) ) : ?>
	<div class="updated inline woocommerce-message">
		<p><?php echo esc_html( sprintf( __( 'Help us keep the %s plugin free making a donation or rate %s on WordPress.org. Thank you in advance!', 'woocommerce-bcash' ), __( 'Claudio Sanches - Bcash for WooCommerce', 'woocommerce-bcash' ), '&#9733;&#9733;&#9733;&#9733;&#9733;' ) ); ?></p>
		<p><a href="http://claudiosmweb.com/doacoes/" target="_blank" class="button button-primary"><?php esc_html_e( 'Make a donation', 'woocommerce-bcash' ); ?></a> <a href="https://wordpress.org/support/view/plugin-reviews/woocommerce-bcash?filter=5#postform" target="_blank" class="button button-secondary"><?php esc_html_e( 'Make a review', 'woocommerce-bcash' ); ?></a></p>
	</div>
<?php endif;
