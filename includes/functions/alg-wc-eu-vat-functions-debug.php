<?php
/**
 * EU VAT for WooCommerce - Functions - Debug
 *
 * @version 4.3.2
 * @since   1.7.1
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ALG_WC_EU_VAT_DEBUG' ) ) {
	define( 'ALG_WC_EU_VAT_DEBUG', ( 'yes' === get_option( 'alg_wc_eu_vat_debug', 'no' ) ) );
}

if ( ! function_exists( 'alg_wc_eu_vat_debug_log' ) ) {
	/**
	 * alg_wc_eu_vat_log.
	 *
	 * @version 4.3.2
	 * @since   4.3.2
	 *
	 * @todo    (v4.3.2) `wc_get_logger()->error`, etc.
	 */
	function alg_wc_eu_vat_debug_log( $message, $data = array() ) {
		if ( ! ALG_WC_EU_VAT_DEBUG ) {
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
			array( 'source' => 'alg-wc-eu-vat-plugin' )
		);
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_log' ) ) {
	/**
	 * alg_wc_eu_vat_log.
	 *
	 * @version 4.2.4
	 * @since   1.6.0
	 *
	 * @todo    (v4.3.2) remove this and replace with the `alg_wc_eu_vat_debug_log()`
	 */
	function alg_wc_eu_vat_log( $country_code, $vat_number, $billing_company, $method, $message ) {
		if (
			ALG_WC_EU_VAT_DEBUG &&
			function_exists( 'wc_get_logger' ) &&
			( $log = wc_get_logger() )
		) {
			if (
				$country_code ||
				$vat_number ||
				$billing_company ||
				$method
			) {
				$message .= ' (' . sprintf(
					/* Translators: %1$s: Country code, %2$s: VAT number, %3$s: Billing company, %4$s: Method. */
					__( 'Country: [%1$s]; VAT ID: [%2$s]; Company: [%3$s]; Method: [%4$s]', 'eu-vat-for-woocommerce' ),
					$country_code,
					$vat_number,
					$billing_company,
					$method
				) . ')';
			}
			$log->log( 'info', $message, array( 'source' => 'alg-wc-eu-vat-plugin' ) );
		}
	}
}
