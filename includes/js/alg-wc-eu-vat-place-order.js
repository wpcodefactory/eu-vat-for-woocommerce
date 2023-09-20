/**
 * alg-wc-eu-vat-place-order.js
 *
 * @version 2.9.13
 * @since   1.4.1
 * @author  WPFactory
 * @todo    [dev] replace `billing_eu_vat_number` with `alg_wc_eu_vat_get_field_id()`
 * @todo    [dev] (maybe) also `return false;` when not confirmed
 */

jQuery( document ).ready( function() {

	var checkout_form = $( 'form.checkout' );
	var yn_status = false;
	checkout_form.on( 'checkout_place_order', function() {
		if ( jQuery( '#billing_eu_vat_number' ).is( ':visible' ) && '' == jQuery( '#billing_eu_vat_number' ).val() ) {
			
			confirmo.init({
				yesBg:'green',
				noBg:'red'
			});
			confirmo.show({
				msg: place_order_data.confirmation_text,
				callback_yes: function (){
					yn_status = true;
					jQuery('#place_order').click();
				},
				callback_no:function (){
					yn_status = false;
				}
			});
			return yn_status;
		}
	} );

	/*
	jQuery( '[name="checkout"]' ).on( 'submit', function( e ) {
		if ( jQuery( '#billing_eu_vat_number' ).is( ':visible' ) && '' == jQuery( '#billing_eu_vat_number' ).val() ) {
			if ( ! confirm( place_order_data.confirmation_text ) ) {
				e.preventDefault();
				e.preventPropagation();
			}
		}
	});
	*/
} );
