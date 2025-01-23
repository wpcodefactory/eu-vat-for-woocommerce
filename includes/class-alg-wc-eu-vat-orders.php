<?php
/**
 * EU VAT for WooCommerce - Orders
 *
 * @version 4.1.0
 * @since   4.1.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Orders' ) ) :

class Alg_WC_EU_VAT_Orders {

	/**
	 * Constructor.
	 *
	 * @version 4.1.0
	 * @since   4.1.0
	 */
	function __construct() {

		// REST
		add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'alg_wc_eu_vat_filter_order_response' ), PHP_INT_MAX, 3 );

		// Save VAT details
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_vat_details_to_order' ) );

	}

	/**
	 * alg_wc_eu_vat_filter_order_response.
	 *
	 * @version 2.9.21
	 * @since   2.9.21
	 */
	function alg_wc_eu_vat_filter_order_response( $response, $post, $request ) {

		if ( 'yes' === get_option( 'alg_wc_eu_vat_remove_country_rest_api_enable', 'no' ) ) {

			$i = 0;
			$meta_data_count = count( $response->data["meta_data"] );
			while ( $i < $meta_data_count ) {

				if ( '_billing_eu_vat_number' == $response->data['meta_data'][ $i ]->get_data()['key'] ) {

					$value = $response->data['meta_data'][ $i ]->get_data()['value'];

					$vat_clean   = preg_replace( '/[^a-zA-Z0-9]/', '', $value );
					$vat_code    = substr( $vat_clean, 2, 15 );
					$vat_country = substr( $vat_clean, 0, 2 );

					$response->data['meta_data'][ $i ]->__set( 'value', $vat_code );
					$response->data['meta_data'][ $i ]->__set( 'vat_country', $vat_country );
					$response->data['meta_data'][ $i ]->apply_changes();
				}

				$i++;
			}
		}

		return $response;
	}

	/**
	 * Save VAT details to the order meta during checkout.
	 *
	 * @param   int  $order_id  The ID of the order being processed.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function save_vat_details_to_order( int $order_id ) {
		if ( ! ( empty( $_POST[ alg_wc_eu_vat_get_field_id() ] ) ) ) {
			// Get response data from the session
			$vat_response_data = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_details' );
			$order             = wc_get_order( $order_id );
			$order->update_meta_data( alg_wc_eu_vat_get_field_id() . '_details', $vat_response_data );
			$order->save();
		}
	}

}

endif;

return new Alg_WC_EU_VAT_Orders();
