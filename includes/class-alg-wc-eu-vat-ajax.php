<?php
/**
 * EU VAT for WooCommerce - AJAX Class
 *
 * @version 1.7.0
 * @since   1.0.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_EU_VAT_AJAX' ) ) :

class Alg_WC_EU_VAT_AJAX {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		add_action( 'wp_enqueue_scripts',                           array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_alg_wc_eu_vat_validate_action',        array( $this, 'alg_wc_eu_vat_validate_action' ) );
		add_action( 'wp_ajax_nopriv_alg_wc_eu_vat_validate_action', array( $this, 'alg_wc_eu_vat_validate_action' ) );
	}

	/**
	 * enqueue_scripts.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 * @todo    [dev] (important) `... && function_exists( 'is_checkout' ) && is_checkout()`
	 */
	function enqueue_scripts() {
		if ( 'yes' === get_option( 'alg_wc_eu_vat_validate', 'yes' ) ) {
			wp_enqueue_script( 'alg-wc-eu-vat', alg_wc_eu_vat()->plugin_url() . '/includes/js/alg-wc-eu-vat.js', array(), alg_wc_eu_vat()->version, true );
			wp_localize_script( 'alg-wc-eu-vat', 'alg_wc_eu_vat_ajax_object', array(
				'ajax_url'                        => admin_url( 'admin-ajax.php' ),
				'add_progress_text'               => get_option( 'alg_wc_eu_vat_add_progress_text', 'no' ),
				'do_check_company_name'           => ( 'yes' === apply_filters( 'alg_wc_eu_vat_check_company_name', 'no' ) ),
				'progress_text_validating'        => do_shortcode( get_option( 'alg_wc_eu_vat_progress_text_validating',        __( 'Validating VAT. Please wait...', 'eu-vat-for-woocommerce' ) ) ),
				'progress_text_valid'             => do_shortcode( get_option( 'alg_wc_eu_vat_progress_text_valid',             __( 'VAT is valid.', 'eu-vat-for-woocommerce' ) ) ),
				'progress_text_not_valid'         => do_shortcode( get_option( 'alg_wc_eu_vat_progress_text_not_valid',         __( 'VAT is not valid.', 'eu-vat-for-woocommerce' ) ) ),
				'progress_text_validation_failed' => do_shortcode( get_option( 'alg_wc_eu_vat_progress_text_validation_failed', __( 'Validation failed. Please try again.', 'eu-vat-for-woocommerce' ) ) ),
			) );
		}
	}

	/**
	 * alg_wc_eu_vat_validate_action.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 * @todo    [dev] (maybe) better codes (i.e. not 0, 1, 2, 3)
	 * @todo    [dev] (maybe) `if ( ! isset( $_POST['alg_wc_eu_vat_validate_action'] ) ) return;`
	 */
	function alg_wc_eu_vat_validate_action( $param ) {
		if ( isset( $_POST['alg_wc_eu_vat_to_check'] ) && '' != $_POST['alg_wc_eu_vat_to_check'] ) {
			$eu_vat_number   = alg_wc_eu_vat_parse_vat( $_POST['alg_wc_eu_vat_to_check'], $_POST['billing_country'] );
			$billing_company = ( isset( $_POST['billing_company'] ) ? $_POST['billing_company'] : '' );
			if ( 'yes' === apply_filters( 'alg_wc_eu_vat_check_ip_location_country', 'no' ) ) {
				$country_by_ip   = alg_wc_eu_vat_get_customers_location_by_ip();
				$is_county_valid = ( $country_by_ip === $eu_vat_number['country'] );
				$is_valid        = $is_county_valid ? alg_wc_eu_vat_validate_vat( $eu_vat_number['country'], $eu_vat_number['number'], $billing_company ) : false;
				if ( ! $is_valid && ! $is_county_valid ) {
					alg_wc_eu_vat_maybe_log( $eu_vat_number['country'], $eu_vat_number['number'], $billing_company, '',
						sprintf( __( 'Error: Country by IP does not match (%s)', 'eu-vat-for-woocommerce' ), $country_by_ip ) );
				}
			} else {
				$is_valid = alg_wc_eu_vat_validate_vat( $eu_vat_number['country'], $eu_vat_number['number'], $billing_company );
			}
		} else {
			$is_valid = null;
		}
		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid',    $is_valid );
		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', ( isset( $_POST['alg_wc_eu_vat_to_check'] ) ? $_POST['alg_wc_eu_vat_to_check'] : '' ) );
		if ( false === $is_valid ) {
			echo '0';
		} elseif ( true === $is_valid ) {
			echo '1';
		} elseif ( null === $is_valid ) {
			echo '2';
		} else {
			echo '3'; // unexpected
		}
		die();
	}

}

endif;

return new Alg_WC_EU_VAT_AJAX();
