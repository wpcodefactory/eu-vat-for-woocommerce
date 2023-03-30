/**
 * alg-wc-eu-vat-place-order.js
 *
 * @version 1.6.0
 * @since   1.4.1
 * @author  WPFactory
 * @todo    [dev] replace `billing_eu_vat_number` with `alg_wc_eu_vat_get_field_id()`
 * @todo    [dev] (maybe) also `return false;` when not confirmed
 */

jQuery( document ).ready( function() {
	jQuery( '[name="checkout"]' ).on( 'submit', function( e ) {
		if ( jQuery( '#billing_eu_vat_number' ).is( ':visible' ) && '' == jQuery( '#billing_eu_vat_number' ).val() ) {
			if ( ! confirm( place_order_data.confirmation_text ) ) {
				e.preventDefault();
				e.preventPropagation();
			}
		}
	} );
} );
