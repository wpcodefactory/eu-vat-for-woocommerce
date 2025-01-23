<?php
/**
 * EU VAT for WooCommerce - Functions - Debug
 *
 * @version 4.1.0
 * @since   1.7.1
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ALG_WC_EU_VAT_DEBUG' ) ) {
	define( 'ALG_WC_EU_VAT_DEBUG', ( 'yes' === get_option( 'alg_wc_eu_vat_debug', 'no' ) ) );
}

if ( ! function_exists( 'alg_wc_eu_vat_maybe_log' ) ) {
	/**
	 * alg_wc_eu_vat_maybe_log.
	 *
	 * @version 4.1.0
	 * @since   1.6.0
	 */
	function alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, $method, $message ) {
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
