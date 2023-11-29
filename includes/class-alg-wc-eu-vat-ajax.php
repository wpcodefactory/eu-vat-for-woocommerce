<?php
/**
 * EU VAT for WooCommerce - AJAX Class
 *
 * @version 2.9.16
 * @since   1.0.0
 * @author  WPFactory
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
		
		add_action( 'wp_ajax_exempt_vat_from_admin',        array( $this, 'alg_wc_eu_vat_exempt_vat_from_admin' ) );
		add_action( 'wp_ajax_nopriv_exempt_vat_from_admin', array( $this, 'alg_wc_eu_vat_exempt_vat_from_admin' ) );
	}

	/**
	 * enqueue_scripts.
	 *
	 * @version 2.9.16
	 * @since   1.0.0
	 * @todo    [dev] (important) `... && function_exists( 'is_checkout' ) && is_checkout()`
	 */
	function enqueue_scripts() {
		if ( 'yes' === get_option( 'alg_wc_eu_vat_validate', 'yes' ) ) {
			if ( ( function_exists( 'is_checkout' ) && is_checkout() ) || is_account_page() ) {
				wp_enqueue_script( 'alg-wc-eu-vat', alg_wc_eu_vat()->plugin_url() . '/includes/js/alg-wc-eu-vat.js', array('jquery'), alg_wc_eu_vat()->version, true );
				wp_localize_script( 'alg-wc-eu-vat', 'alg_wc_eu_vat_ajax_object', array(
					'ajax_url'                        => admin_url( 'admin-ajax.php' ),
					'add_progress_text'               => get_option( 'alg_wc_eu_vat_add_progress_text', 'yes' ),
					'action_trigger'               	  => get_option( 'alg_wc_eu_vat_validate_action_trigger', 'oninput' ),
					'hide_message_on_preserved_countries'               => get_option( 'alg_wc_eu_vat_hide_message_on_preserved_countries', 'no' ),
					'preserve_countries' => $this->get_preserve_countrues(),
					'do_check_company_name'           => ( 'yes' === apply_filters( 'alg_wc_eu_vat_check_company_name', 'no' ) ),
					'progress_text_validating'        => do_shortcode( get_option( 'alg_wc_eu_vat_progress_text_validating',        __( 'Validating VAT. Please wait...', 'eu-vat-for-woocommerce' ) ) ),
					'progress_text_valid'             => do_shortcode( get_option( 'alg_wc_eu_vat_progress_text_valid',             __( 'VAT is valid.', 'eu-vat-for-woocommerce' ) ) ),
					'progress_text_not_valid'         => do_shortcode( get_option( 'alg_wc_eu_vat_progress_text_not_valid',         __( 'VAT is not valid.', 'eu-vat-for-woocommerce' ) ) ),
					'progress_text_validation_failed' => do_shortcode( get_option( 'alg_wc_eu_vat_progress_text_validation_failed', __( 'Validation failed. Please try again.', 'eu-vat-for-woocommerce' ) ) ),
					'text_shipping_billing_countries' => do_shortcode( get_option( 'alg_wc_eu_vat_shipping_billing_countries', __( 'Different shipping & billing countries.', 'eu-vat-for-woocommerce' ) ) ),
					'company_name_mismatch' 		  => do_shortcode( get_option( 'alg_wc_eu_vat_company_name_mismatch', __( 'VAT is valid, but registered to %company_name%.', 'eu-vat-for-woocommerce' ) ) ),
					'is_required' => get_option( 'alg_wc_eu_vat_field_required', 'no' ),
				) );
			}
		}
	}
	
	function get_preserve_countrues(){
		$return = array();
		$preservecountries = get_option( 'alg_wc_eu_vat_preserve_in_base_country', 'no' );
		if($preservecountries === 'yes'){
			$location = wc_get_base_location();
			if ( empty( $location['country'] ) ) {
				$location = wc_format_country_state_string( apply_filters( 'woocommerce_customer_default_location', get_option( 'woocommerce_default_country' ) ) );
			}
			$return = array(strtoupper( $location['country'] ));
		}else if($preservecountries === 'list'){
			$locations = array_map( 'strtoupper', array_map( 'trim', explode( ',', get_option( 'alg_wc_eu_vat_preserve_in_base_country_locations', '' ) ) ) );
			$return = $locations;
		}
		return $return;
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
		
		$is_shipping_diff = false;
		
		
		if ( $is_valid && 'no' != ( $preserve_option_value = get_option( 'alg_wc_eu_vat_preserv_vat_for_different_shipping', 'no' ) ) ) {
			$billing_country = $_REQUEST['billing_country'];
			$shipping_country = $_REQUEST['shipping_country'];
			$is_country_same = ( strtoupper( $billing_country ) !== strtoupper( $shipping_country) );

			if($is_country_same){
				$is_shipping_diff = true;
				$is_valid = null;
			}
		}
			
		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid',    $is_valid );
		if ( true === $is_shipping_diff ) {
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', null );
		}else{
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', ( isset( $_POST['alg_wc_eu_vat_to_check'] ) ? $_POST['alg_wc_eu_vat_to_check'] : '' ) );
		}
		
		$alg_wc_eu_vat_belgium_compatibility = ( isset( $_POST['alg_wc_eu_vat_belgium_compatibility'] ) ? $_POST['alg_wc_eu_vat_belgium_compatibility'] : '' );
		
		if($alg_wc_eu_vat_belgium_compatibility=='yes'){
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid',    $is_valid );
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', null );
		}
		
		$company_name_status = false;
		$company_name = '';
		if( true === alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check_company' )){
			$company_name_status = true;
			$company_name = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check_company_name' );
		}
		
		if(!isset($eu_vat_number['number']) || empty($eu_vat_number['number'])){
			echo '6';
		}  else if ( true === $is_shipping_diff ) {
			echo '4';
		}  else if( false === $is_valid && true === $company_name_status ){
			echo '5|' . $company_name;
		}  else if ( false === $is_valid ) {
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
	
	function alg_wc_eu_vat_exempt_vat_from_admin( $param ){
		if ( isset( $_POST['order_id'] ) && '' != $_POST['order_id'] ) {
			$orderid = $_POST['order_id'];
			if(isset( $_POST['status'] ) && 'yes' == $_POST['status'] ){
				update_post_meta($orderid, 'exempt_vat_from_admin', 'never');
				echo "never";
				die;
			}else if(isset( $_POST['status'] ) && 'never' == $_POST['status'] ){
				update_post_meta($orderid, 'exempt_vat_from_admin', 'yes');
				echo "yes";
				die;
			}
		}
		echo "never";
		die;
	}

}

endif;

return new Alg_WC_EU_VAT_AJAX();
