(function ( $ ) {
	'use strict';

	$( function () {

		/**
		 * Awitch user data for sandbox and production.
		 *
		 * @param {String} checked
		 */
		function bCashSwitchUserData( checked ) {
			var email = $( '#woocommerce_bcash_email' ).closest( 'tr' ),
				token = $( '#woocommerce_bcash_token' ).closest( 'tr' ),
				sandboxEmail = $( '#woocommerce_bcash_sandbox_email' ).closest( 'tr' ),
				sandboxToken = $( '#woocommerce_bcash_sandbox_token' ).closest( 'tr' );

			if ( checked ) {
				email.hide();
				token.hide();
				sandboxEmail.show();
				sandboxToken.show();
			} else {
				email.show();
				token.show();
				sandboxEmail.hide();
				sandboxToken.hide();
			}
		}

		bCashSwitchUserData( $( '#woocommerce_bcash_sandbox' ).is( ':checked' ) );
		$( 'body' ).on( 'change', '#woocommerce_bcash_sandbox', function () {
			bCashSwitchUserData( $( this ).is( ':checked' ) );
		});
	});

}( jQuery ));
