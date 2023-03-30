<?php
/**
 * EU VAT for WooCommerce - Functions - Debug
 *
 * @version 1.7.1
 * @since   1.7.1
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'ALG_WC_EU_VAT_DEBUG' ) ) {
	define( 'ALG_WC_EU_VAT_DEBUG', ( 'yes' === get_option( 'alg_wc_eu_vat_debug', 'no' ) ) );
}

if ( ! function_exists( 'alg_wc_eu_vat_maybe_log' ) ) {
	/**
	 * alg_wc_eu_vat_maybe_log.
	 *
	 * @version 1.7.0
	 * @since   1.6.0
	 */
	function alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, $method, $message ) {
		if ( ALG_WC_EU_VAT_DEBUG && function_exists( 'wc_get_logger' ) && ( $log = wc_get_logger() ) ) {
			if ( $country_code || $vat_number || $billing_company || $method ) {
				$message .= ' (' . sprintf( __( 'Country: [%s]; VAT ID: [%s]; Company: [%s]; Method: [%s]', 'eu-vat-for-woocommerce' ),
					$country_code, $vat_number, $billing_company, $method ) . ')';
			}
			$log->log( 'info', $message, array( 'source' => 'alg-wc-eu-vat-plugin' ) );
		}
	}
}
