<?php
/**
 * EU VAT for WooCommerce - AJAX Class
 *
 * @version 4.6.7
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_AJAX' ) ) :

class Alg_WC_EU_VAT_AJAX {

	/**
	 * Constructor.
	 *
	 * @version 4.6.7
	 * @since   1.0.0
	 */
	function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add nonce field to checkout page
		add_action( 'woocommerce_before_checkout_form', array( $this, 'add_nonce_field' ) );

		add_action( 'wp_ajax_alg_wc_eu_vat_validate_action',        array( $this, 'alg_wc_eu_vat_validate_action' ) );
		add_action( 'wp_ajax_nopriv_alg_wc_eu_vat_validate_action', array( $this, 'alg_wc_eu_vat_validate_action' ) );
	}

	/**
	 * enqueue_scripts.
	 *
	 * @version 4.6.4
	 * @since   1.0.0
	 *
	 * @todo    (dev) `... && function_exists( 'is_checkout' ) && is_checkout()`
	 */
	function enqueue_scripts() {

		if ( 'no' === get_option( 'alg_wc_eu_vat_validate', 'yes' ) ) {
			return;
		}

		if (
			! alg_wc_eu_vat_is_checkout() &&
			! alg_wc_eu_vat_is_cart() &&
			(
				! is_account_page() ||
				(
					'no' === get_option( 'alg_wc_eu_vat_validate_my_account', 'no' ) &&
					is_wc_endpoint_url( 'edit-address' )
				)
			)
		) {
			return;
		}

		wp_enqueue_script(
			'alg-wc-eu-vat',
			alg_wc_eu_vat()->plugin_asset_url( '/js/alg-wc-eu-vat.js' ),
			array( 'jquery' ),
			alg_wc_eu_vat()->version,
			true
		);

		wp_localize_script(
			'alg-wc-eu-vat',
			'alg_wc_eu_vat_ajax_object',
			array(
				'ajax_url'                             => admin_url( 'admin-ajax.php' ),
				'add_progress_text'                    => get_option( 'alg_wc_eu_vat_add_progress_text', 'yes' ),
				'action_trigger'                       => get_option( 'alg_wc_eu_vat_validate_action_trigger', 'oninput' ),
				'hide_message_on_preserved_countries'  => get_option( 'alg_wc_eu_vat_hide_message_on_preserved_countries', 'no' ),
				'preserve_countries'                   => $this->get_preserve_countries(),
				'do_check_company_name'                => ( 'yes' === apply_filters( 'alg_wc_eu_vat_check_company_name', 'no' ) ),
				'progress_text_validating'             => do_shortcode(
					get_option(
						'alg_wc_eu_vat_progress_text_validating',
						__( 'Validating VAT. Please wait...', 'eu-vat-for-woocommerce' )
					)
				),
				'is_required'                          => get_option( 'alg_wc_eu_vat_field_required', 'no' ),
				'optional_text'                        => __( '(optional)', 'eu-vat-for-woocommerce' ),
				'autofill_company_name'                => get_option( 'alg_wc_eu_vat_advance_enable_company_name_autofill', 'no' ),
				'show_vat_details'                     => get_option( 'alg_wc_eu_vat_show_vat_details', 'no' ),
				'do_compatibility_fluid_checkout'      => ( 'yes' === get_option( 'alg_wc_eu_vat_compatibility_fluid_checkout', 'no' ) ),
				'do_always_show_zero_vat'              => ( 'yes' === get_option( 'alg_wc_eu_vat_always_show_zero_vat', 'no' ) ),
				'do_show_hide_by_billing_company'      => ( 'yes' === get_option( 'alg_wc_eu_vat_show_hide_by_billing_company', 'no' ) ),
			)
		);

	}

	/**
	 * add_nonce_field.
	 *
	 * @version 4.6.7
	 * @since   4.6.7
	 */
	function add_nonce_field() {
		wp_nonce_field( 'alg_wc_eu_vat_nonce', 'alg_wc_eu_vat_nonce_field' );
	}

	/**
	 * get_preserve_countries.
	 *
	 * @version 4.0.0
	 * @since   2.12.13
	 */
	function get_preserve_countries() {
		$return = array();
		$preservecountries = get_option( 'alg_wc_eu_vat_preserve_in_base_country', 'no' );
		if ( 'yes' === $preservecountries ) {
			$location = wc_get_base_location();
			if ( empty( $location['country'] ) ) {
				$location = wc_format_country_state_string(
					apply_filters(
						'woocommerce_customer_default_location', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
						get_option( 'woocommerce_default_country' )
					)
				);
			}
			$return = array( strtoupper( $location['country'] ) );
		} elseif ( 'list' === $preservecountries ) {
			$locations = array_map(
				'strtoupper',
				array_map(
					'trim',
					explode(
						',',
						get_option( 'alg_wc_eu_vat_preserve_in_base_country_locations', '' )
					)
				)
			);
			$return = $locations;
		}
		return $return;
	}

	/**
	 * alg_wc_eu_vat_validate_action.
	 *
	 * @version 4.6.7
	 * @since   1.0.0
	 *
	 * @todo    (v4.4.7) `empty( $eu_vat_number['number'] )`: clear all other session variables, e.g., `alg_wc_eu_vat_response_data`
	 * @todo    (dev) `if ( ! isset( $_POST['alg_wc_eu_vat_validate_action'] ) ) return;`?
	 */
	function alg_wc_eu_vat_validate_action() {

		check_ajax_referer( 'alg_wc_eu_vat_nonce', 'nonce' );

		$post       = wp_unslash( $_POST );
		$vat_number = sanitize_text_field( $post['alg_wc_eu_vat_to_check'] ?? '' );

		if (
			'checkout_block_first_load' == $vat_number &&
			version_compare( get_option( 'woocommerce_version', null ), '8.9.1', '>=' )
		) {
			$vat_number = WC()->customer->get_meta(
				alg_wc_eu_vat()->core->checkout_block->get_block_field_id()
			);
		}

		$billing_country            = sanitize_text_field( $post['billing_country'] ?? '' );
		$shipping_country           = sanitize_text_field( $post['shipping_country'] ?? '' );
		$billing_company            = sanitize_text_field( $post['billing_company'] ?? '' );
		$vat_customer_decide        = sanitize_text_field( $post['alg_wc_eu_vat_customer_decide'] ?? false );
		$vat_valid_but_not_exempted = sanitize_text_field( $post['alg_wc_eu_vat_valid_vat_but_not_exempted'] ?? false );

		$data = array(
			'vat_number'                 => $vat_number,
			'billing_country'            => $billing_country,
			'shipping_country'           => $shipping_country,
			'billing_company'            => $billing_company,
			'vat_customer_decide'        => $vat_customer_decide,
			'vat_valid_but_not_exempted' => $vat_valid_but_not_exempted,
		);

		alg_wc_eu_vat_session_set(
			'alg_wc_eu_vat',
			wc_clean( $data['vat_number'] )
		);
		alg_wc_eu_vat_session_set(
			'alg_wc_eu_vat_customer_decide',
			wc_clean( $data['vat_customer_decide'] )
		);
		alg_wc_eu_vat_session_set(
			'alg_wc_eu_vat_valid_but_not_exempted',
			wc_clean( $data['vat_valid_but_not_exempted'] )
		);

		$result = alg_wc_eu_vat()->core->vat_validation( $data );
		wp_send_json( $result );
	}

}

endif;

return new Alg_WC_EU_VAT_AJAX();
