<?php
/**
 * EU VAT for WooCommerce - AJAX Class
 *
 * @version 2.12.14
 * @since   1.0.0
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

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
		
		add_action( 'wp_ajax_alg_wc_eu_vat_validate_action_first_load',        array( $this, 'alg_wc_eu_vat_validate_action_first_load' ) );
		add_action( 'wp_ajax_nopriv_alg_wc_eu_vat_validate_action_first_load', array( $this, 'alg_wc_eu_vat_validate_action_first_load' ) );
		
		add_action( 'wp_ajax_exempt_vat_from_admin',        array( $this, 'alg_wc_eu_vat_exempt_vat_from_admin' ) );
		add_action( 'wp_ajax_nopriv_exempt_vat_from_admin', array( $this, 'alg_wc_eu_vat_exempt_vat_from_admin' ) );
	}

	/**
	 * enqueue_scripts.
	 *
	 * @version 2.12.14
	 * @since   1.0.0
	 * @todo    [dev] (important) `... && function_exists( 'is_checkout' ) && is_checkout()`
	 */
	function enqueue_scripts() {
		if ( 'yes' === get_option( 'alg_wc_eu_vat_validate', 'yes' ) ) {
			if ( ( function_exists( 'is_checkout' ) && is_checkout() ) || ( is_account_page() && ! is_wc_endpoint_url( 'edit-address' ) ) ) {
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
					'progress_text_validation_preserv' => do_shortcode( get_option( 'alg_wc_eu_vat_progress_text_validation_preserv', __( 'VAT preserved for this billing country', 'eu-vat-for-woocommerce' ) ) ),
					'text_shipping_billing_countries' => do_shortcode( get_option( 'alg_wc_eu_vat_shipping_billing_countries', __( 'Different shipping & billing countries.', 'eu-vat-for-woocommerce' ) ) ),
					'company_name_mismatch' 		  => do_shortcode( get_option( 'alg_wc_eu_vat_company_name_mismatch', __( ' VAT is valid, but registered to %company_name%.', 'eu-vat-for-woocommerce' ) ) ),
					'vies_not_available' 		  => do_shortcode( get_option( 'alg_wc_eu_vat_progress_text_validation_vies_error', __( ' VAT accepted due to VIES error: %vies_error%. The admin will check the VAT validation again and proceed accordingly.', 'eu-vat-for-woocommerce' ) ) ),
					'is_required' => get_option( 'alg_wc_eu_vat_field_required', 'no' ),
					'optional_text'        			  => __( '(optional)', 'eu-vat-for-woocommerce' ),
					'autofill_company_name'      => get_option( 'alg_wc_eu_vat_advance_enable_company_name_autofill', 'no' ),
				) );
			}
		}
	}
	
	/**
	 * get_preserve_countrues.
	 *
	 * @version 2.12.13
	 * @since   2.12.13
	 */
	 
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
	 * alg_wc_eu_vat_validate_action_first_load.
	 *
	 * @version 2.12.13
	 * @since   2.12.13
	 */
	function alg_wc_eu_vat_validate_action_first_load( $param ) {
		$alg_wc_eu_vat_valid = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_valid' );
		
		$return_data = array();
		$return_data['status'] = 0;
		if($alg_wc_eu_vat_valid == true) {
			$return_data['status'] = 1;
		}
		wp_send_json($return_data);
	}

	/**
	 * alg_wc_eu_vat_validate_action.
	 *
	 * @version 2.12.13
	 * @since   1.0.0
	 * @todo    [dev] (maybe) better codes (i.e. not 0, 1, 2, 3)
	 * @todo    [dev] (maybe) `if ( ! isset( $_POST['alg_wc_eu_vat_validate_action'] ) ) return;`
	 */
	 
	function alg_wc_eu_vat_validate_action( $param ) {
		$vat_number = '';
		if ( isset( $_POST['alg_wc_eu_vat_to_check'] ) && '' != $_POST['alg_wc_eu_vat_to_check'] ) {
			$vat_number = esc_attr($_POST['alg_wc_eu_vat_to_check']);
		}
		if($vat_number == 'checkout_block_first_load') {
			if ( version_compare( get_option( 'woocommerce_version', null ), '8.9.1', '>=' ) ) {
				$vat_number = WC()->customer->get_meta('_wc_other/alg_eu_vat/billing_eu_vat_number');
			}
		}
		
		if ( isset( $_POST['alg_wc_eu_vat_to_check'] ) && '' != $_POST['alg_wc_eu_vat_to_check'] ) {
			$eu_vat_number   = alg_wc_eu_vat_parse_vat( $vat_number, esc_attr($_POST['billing_country']) );
			$billing_company = ( isset( $_POST['billing_company'] ) ? esc_attr($_POST['billing_company']) : '' );
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
		
		
		$vat_allow_vias_not_available = false;
		if( !$is_valid ){
			if( alg_wc_eu_vat()->core->get_error_vies_unavailable() !== null ) {
				$is_valid = true;
				$vat_allow_vias_not_available = true;
			}
		}
		
		
		
		$is_shipping_diff = false;
		$is_preserv = false;
		
		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid_before_preserve',    $is_valid );
		
		if ( $is_valid && 'no' != ( $preserve_option_value_base_country = get_option( 'alg_wc_eu_vat_preserve_in_base_country', 'no' ) ) ) {
			
			$selected_country_at_checkout = esc_attr($_POST['billing_country']);
			
			if ( 'yes' === $preserve_option_value_base_country ) {
				$location = wc_get_base_location();
				if ( empty( $location['country'] ) ) {
					$location = wc_format_country_state_string( apply_filters( 'woocommerce_customer_default_location', get_option( 'woocommerce_default_country' ) ) );
				}
				
				$is_preserv = ( strtoupper( $location['country'] ) === $selected_country_at_checkout );
			} elseif ( '' != get_option( 'alg_wc_eu_vat_preserve_in_base_country_locations', '' ) ) { // `list`
				$locations = array_map( 'strtoupper', array_map( 'trim', explode( ',', get_option( 'alg_wc_eu_vat_preserve_in_base_country_locations', '' ) ) ) );
				$is_preserv = ( in_array( $selected_country_at_checkout, $locations ) );
			}
		}
		
		if ( 'yes' != get_option( 'alg_wc_eu_vat_validate_enable_preserve_message', 'no' ) ) {
			$is_preserv = false;
		}
		
		if($is_preserv) {
			$is_valid = null;
		}
		
		if ( $is_valid && 'no' != ( $preserve_option_value = get_option( 'alg_wc_eu_vat_preserv_vat_for_different_shipping', 'no' ) ) ) {
			$billing_country = $_REQUEST['billing_country'];
			$shipping_country = $_REQUEST['shipping_country'];
			$is_country_not_same = ( strtoupper( $billing_country ) !== strtoupper( $shipping_country) );

			if($is_country_not_same){
				$is_shipping_diff = true;
				$is_valid = null;
			}
		}
			
		
		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid',    $is_valid );
		if ( true === $is_shipping_diff ) {
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', null );
		}else if ( true === $is_preserv ) {
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', null );
		}else{
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', ( isset( $_POST['alg_wc_eu_vat_to_check'] ) ? esc_attr($_POST['alg_wc_eu_vat_to_check']) : '' ) );
		}
		
		$alg_wc_eu_vat_belgium_compatibility = ( isset( $_POST['alg_wc_eu_vat_belgium_compatibility'] ) ? esc_attr($_POST['alg_wc_eu_vat_belgium_compatibility']) : '' );
		
		if( $alg_wc_eu_vat_belgium_compatibility == 'yes' ){
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid',    $is_valid );
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', null );
		}
		
		$company_name_status = false;
		$company_name = '';
		if( true === alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check_company' )){
			$company_name_status = true;
			$company_name = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check_company_name' );
		}
		
		$return_status = '';
		$return_error = '';
		
		if(!isset($eu_vat_number['number']) || empty($eu_vat_number['number'])){
			$return_status = '6';
		}  else if ( true === $is_shipping_diff ) {
			$return_status =  '4';
		}  else if ( true === $is_preserv ) {
			$return_status =  '7';
		} else if ( true === $vat_allow_vias_not_available ) {
			$return_status =  '8';
			$return_error = alg_wc_eu_vat()->core->get_error_vies_unavailable();
		}  else if( false === $is_valid && true === $company_name_status ){
			$return_status =  '5|' . $company_name;
		}  else if ( false === $is_valid ) {
			$return_status =  '0';
		} elseif ( true === $is_valid ) {
			$return_status =  '1';
		} elseif ( null === $is_valid ) {
			$return_status =  '2';
		} else {
			$return_status =  '3'; // unexpected
		}
		
		$return_company_name = '';
		$company_name = alg_wc_eu_vat_session_get('alg_wc_eu_vat_to_return_company_name', null);
		if( isset($company_name) && !empty($company_name) ) {
			if( preg_match("/[a-z]/i", $company_name)){
				$return_company_name = $company_name;
			}
		}
		
		
		
		if ( isset( $_POST['channel'] ) && 'bloock_api' == $_POST['channel'] ) {
			$return_data = array();
			$return_data['status'] = $return_status;
			$return_data['company'] = $return_company_name;
			$return_data['error'] = $return_error;
			if ( !empty( WC()->customer ) && ( true === $is_valid) )  {
				$is_exempt = true;
				WC()->customer->set_is_vat_exempt( $is_exempt );
			} else {
				$is_exempt = false;
				WC()->customer->set_is_vat_exempt( $is_exempt );
			}
			$alg_wc_eu_vat_valid = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_valid' );
			wp_send_json($return_data);
		}else{
			// echo $return_status;
			wp_send_json(array( 'res' => $return_status, 'company'=>$return_company_name, 'error' => $return_error ) );
		}
		
		die();
	}
	
	/**
	 * alg_wc_eu_vat_exempt_vat_from_admin.
	 *
	 * @version 2.12.14
	 * @since   2.12.13
	 */
	function alg_wc_eu_vat_exempt_vat_from_admin( $param ){
		
		if ( ! current_user_can('manage_options') || ! wp_verify_nonce( $_POST['nonce'], 'alg-wc-eu-vat-ajax-nonce' ) ) {
			exit;
		}
		
		if ( isset( $_POST['order_id'] ) && '' != $_POST['order_id'] ) {
			$orderid = esc_attr($_POST['order_id']);
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
