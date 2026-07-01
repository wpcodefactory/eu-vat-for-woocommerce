<?php
/**
 * EU VAT for WooCommerce - Functions - Debug
 *
 * @version 4.7.0
 * @since   1.7.1
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WPFACTORY_WC_EU_VAT_DEBUG' ) ) {
	define( 'WPFACTORY_WC_EU_VAT_DEBUG', ( 'yes' === get_option( 'alg_wc_eu_vat_debug', 'no' ) ) );
}

if ( ! function_exists( 'wpfactory_wc_eu_vat_debug_log' ) ) {
	/**
	 * wpfactory_wc_eu_vat_debug_log.
	 *
	 * @version 4.7.0
	 * @since   4.3.2
	 *
	 * @todo    (v4.3.2) `wc_get_logger()->error`, etc.
	 */
	function wpfactory_wc_eu_vat_debug_log( $message, $data = array() ) {
		if ( ! WPFACTORY_WC_EU_VAT_DEBUG ) {
			return;
		}
		if ( ! empty( $data ) ) {
			foreach ( $data as $id => &$value ) {
				$value = "{$id}: [{$value}]";
			}
			$message .= ' (' . implode( '; ', $data ) . ')';
		}
		wc_get_logger()->info(
			$message,
			array( 'source' => 'wpfactory-wc-eu-vat-plugin' )
		);
	}
}
