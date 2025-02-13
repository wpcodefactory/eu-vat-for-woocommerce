<?php
/**
 * EU VAT for WooCommerce - AJAX Class
 *
 * @version 4.2.8
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
	 * @version 4.0.0
	 * @since   1.0.0
	 */
	function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_ajax_alg_wc_eu_vat_validate_action',        array( $this, 'alg_wc_eu_vat_validate_action' ) );
		add_action( 'wp_ajax_nopriv_alg_wc_eu_vat_validate_action', array( $this, 'alg_wc_eu_vat_validate_action' ) );

		add_action( 'wp_ajax_alg_wc_eu_vat_validate_action_first_load',        array( $this, 'alg_wc_eu_vat_validate_action_first_load' ) );
		add_action( 'wp_ajax_nopriv_alg_wc_eu_vat_validate_action_first_load', array( $this, 'alg_wc_eu_vat_validate_action_first_load' ) );

	}

	/**
	 * enqueue_scripts.
	 *
	 * @version 4.2.8
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
			alg_wc_eu_vat()->plugin_url() . '/includes/js/alg-wc-eu-vat.js',
			array( 'jquery' ),
			alg_wc_eu_vat()->version,
			true
		);

		wp_localize_script(
			'alg-wc-eu-vat',
			'alg_wc_eu_vat_ajax_object',
			array(
				'ajax_url'                            => admin_url( 'admin-ajax.php' ),
				'add_progress_text'                   => get_option( 'alg_wc_eu_vat_add_progress_text', 'yes' ),
				'action_trigger'                      => get_option( 'alg_wc_eu_vat_validate_action_trigger', 'oninput' ),
				'hide_message_on_preserved_countries' => get_option( 'alg_wc_eu_vat_hide_message_on_preserved_countries', 'no' ),
				'preserve_countries'                  => $this->get_preserve_countries(),
				'do_check_company_name'               => ( 'yes' === apply_filters( 'alg_wc_eu_vat_check_company_name', 'no' ) ),
				'progress_text_validating'            => do_shortcode(
					get_option(
						'alg_wc_eu_vat_progress_text_validating',
						__( 'Validating VAT. Please wait...', 'eu-vat-for-woocommerce' )
					)
				),
				'progress_text_valid'                 => do_shortcode(
					get_option(
						'alg_wc_eu_vat_progress_text_valid',
						__( 'VAT is valid.', 'eu-vat-for-woocommerce' )
					)
				),
				'progress_text_not_valid'             => do_shortcode(
					get_option(
						'alg_wc_eu_vat_progress_text_not_valid',
						__( 'VAT is not valid.', 'eu-vat-for-woocommerce' )
					)
				),
				'progress_text_is_required'           => do_shortcode(
					get_option(
						'alg_wc_eu_vat_progress_text_is_required',
						__( 'VAT is required.', 'eu-vat-for-woocommerce' )
					)
				),
				'progress_text_validation_failed'     => do_shortcode(
					get_option(
						'alg_wc_eu_vat_progress_text_validation_failed',
						__( 'Validation failed. Please try again.', 'eu-vat-for-woocommerce' )
					)
				),
				'progress_text_validation_preserv'    => do_shortcode(
					(
						'yes' === get_option( 'alg_wc_eu_vat_validate_enable_preserve_message', 'no' ) ?
						get_option(
							'alg_wc_eu_vat_progress_text_validation_preserv',
							__( 'VAT preserved for this billing country', 'eu-vat-for-woocommerce' )
						) :
						get_option(
							'alg_wc_eu_vat_progress_text_valid',
							__( 'VAT is valid.', 'eu-vat-for-woocommerce' )
						)
					)
				),
				'text_shipping_billing_countries'     => do_shortcode(
					get_option(
						'alg_wc_eu_vat_shipping_billing_countries',
						__( 'Different shipping & billing countries.', 'eu-vat-for-woocommerce' )
					)
				),
				'progress_text_wrong_billing_country' => do_shortcode(
					get_option(
						'alg_wc_eu_vat_wrong_billing_country',
						__( 'Wrong billing country.', 'eu-vat-for-woocommerce' )
					)
				),
				'company_name_mismatch'               => do_shortcode(
					get_option(
						'alg_wc_eu_vat_company_name_mismatch',
						__( 'VAT is valid, but registered to %company_name%.', 'eu-vat-for-woocommerce' ) // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
					)
				),
				'vies_not_available'                  => do_shortcode(
					get_option(
						'alg_wc_eu_vat_progress_text_validation_vies_error',
						__( ' VAT accepted due to VIES error: %vies_error%. The admin will check the VAT validation again and proceed accordingly.', 'eu-vat-for-woocommerce' )
					)
				),
				'is_required'                         => get_option( 'alg_wc_eu_vat_field_required', 'no' ),
				'optional_text'                       => __( '(optional)', 'eu-vat-for-woocommerce' ),
				'autofill_company_name'               => get_option( 'alg_wc_eu_vat_advance_enable_company_name_autofill', 'no' ),
				'show_vat_details'                    => get_option( 'alg_wc_eu_vat_show_vat_details', 'no' ),
				'status_codes'                        => $this->get_return_status_codes(),
				'do_compatibility_fluid_checkout'     => ( 'yes' === get_option( 'alg_wc_eu_vat_compatibility_fluid_checkout', 'no' ) ),
				'do_always_show_zero_vat'             => ( 'yes' === get_option( 'alg_wc_eu_vat_always_show_zero_vat', 'no' ) ),
			)
		);

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
						'woocommerce_customer_default_location',
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
	 * alg_wc_eu_vat_validate_action_first_load.
	 *
	 * @version 2.12.13
	 * @since   2.12.13
	 */
	function alg_wc_eu_vat_validate_action_first_load( $param ) {
		$alg_wc_eu_vat_valid   = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_valid' );
		$return_data           = array();
		$return_data['status'] = 0;
		if ( true == $alg_wc_eu_vat_valid ) {
			$return_data['status'] = 1;
		}
		wp_send_json( $return_data );
	}

	/**
	 * do_keep_vat_in_selected_countries.
	 *
	 * @version 4.1.0
	 * @since   4.1.0
	 */
	function do_keep_vat_in_selected_countries() {

		$option = get_option( 'alg_wc_eu_vat_preserve_in_base_country', 'no' );

		if ( 'no' === $option ) {
			return false;
		}

		$selected_country = (
			isset( $_POST['billing_country'] ) ?
			sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) :
			''
		);

		switch ( $option ) {

			case 'list': // Comma separated list
				$locations = get_option( 'alg_wc_eu_vat_preserve_in_base_country_locations', '' );
				if ( '' === $locations ) {
					return false;
				}
				$locations = array_map(
					'strtoupper',
					array_map( 'trim', explode( ',', $locations ) )
				);
				return ( in_array( $selected_country, $locations ) );

			default: // 'yes' // Base (i.e., store) country
				$location = wc_get_base_location();
				if ( empty( $location['country'] ) ) {
					$location = wc_format_country_state_string(
						apply_filters(
							'woocommerce_customer_default_location',
							get_option( 'woocommerce_default_country' )
						)
					);
				}
				return ( strtoupper( $location['country'] ) === $selected_country );

		}

	}

	/**
	 * do_keep_vat_for_different_shipping_country.
	 *
	 * @version 4.2.1
	 * @since   4.2.1
	 */
	function do_keep_vat_for_different_shipping_country() {

		if ( 'no' === get_option( 'alg_wc_eu_vat_preserv_vat_for_different_shipping', 'no' ) ) {
			return false;
		}

		$billing_country = (
			isset( $_REQUEST['billing_country'] ) ?
			sanitize_text_field( wp_unslash( $_REQUEST['billing_country'] ) ) :
			''
		);

		$shipping_country = (
			isset( $_REQUEST['shipping_country'] ) ?
			sanitize_text_field( wp_unslash( $_REQUEST['shipping_country'] ) ) :
			''
		);

		return ( strtoupper( $billing_country ) !== strtoupper( $shipping_country ) );

	}

	/**
	 * get_return_status_codes.
	 *
	 * @version 4.2.6
	 * @since   4.2.1
	 */
	function get_return_status_codes() {
		return array_map(
			'strval',
			array(
				'VAT_NOT_VALID'             => 0,
				'VAT_VALID'                 => 1,
				'VAT_NOT_VALID_NULL'        => 2,
				'UNEXPECTED'                => 3,
				'KEEP_VAT_SHIPPING_COUNTRY' => 4,
				'COMPANY_NAME'              => 5,
				'EMPTY_VAT'                 => 6,
				'KEEP_VAT_COUNTRIES'        => 7,
				'VIES_UNAVAILABLE'          => 8,
				'WRONG_BILLING_COUNTRY'     => 9,
			)
		);
	}

	/**
	 * get_return_status.
	 *
	 * @version 4.2.6
	 * @since   4.1.0
	 */
	function get_return_status( $args ) {

		$status_codes = $this->get_return_status_codes();

		if (
			! empty( $args['eu_vat_number']['error_msg'] ) &&
			isset( $status_codes[ $args['eu_vat_number']['error_msg'] ] )
		) {

			// Error, e.g., `WRONG_BILLING_COUNTRY`
			$status = $status_codes[ $args['eu_vat_number']['error_msg'] ];

		} elseif ( empty( $args['eu_vat_number']['number'] ) ) {

			// Empty EU VAT
			$status = $status_codes['EMPTY_VAT'];

		} elseif ( true === $args['do_preserve_shipping_country'] ) {

			// Keep VAT if shipping country is different from billing country
			$status = $status_codes['KEEP_VAT_SHIPPING_COUNTRY'];

		} elseif ( true === $args['do_preserve_countries'] ) {

			// Keep VAT in selected countries
			$status = $status_codes['KEEP_VAT_COUNTRIES'];

		} elseif ( true === $args['vat_allow_vias_not_available'] ) {

			// VIES is not available
			$status = $status_codes['VIES_UNAVAILABLE'];
			$error  = alg_wc_eu_vat()->core->get_error_vies_unavailable();

		} elseif ( false === $args['is_valid'] && true === $args['company_name_status'] ) {

			// Company name
			$status = $status_codes['COMPANY_NAME'] . '|' . $args['company_name'];

		} elseif ( false === $args['is_valid'] ) {

			// Not valid
			$status = $status_codes['VAT_NOT_VALID'];

		} elseif ( true === $args['is_valid'] ) {

			// Valid
			$status = $status_codes['VAT_VALID'];

		} elseif ( null === $args['is_valid'] ) {

			// Not valid
			$status = $status_codes['VAT_NOT_VALID_NULL'];

		} else {

			// Unexpected
			$status = $status_codes['UNEXPECTED'];

		}

		return array(
			'status' => $status,
			'error'  => $error ?? '',
		);

	}

	/**
	 * alg_wc_eu_vat_validate_action.
	 *
	 * @version 4.2.8
	 * @since   1.0.0
	 *
	 * @todo    (dev) `bloock_api`: rename
	 * @todo    (dev) `checkout_block_first_load`?
	 * @todo    (dev) `if ( ! isset( $_POST['alg_wc_eu_vat_validate_action'] ) ) return;`?
	 */
	function alg_wc_eu_vat_validate_action( $param ) {
		$vat_number = '';
		if (
			isset( $_POST['alg_wc_eu_vat_to_check'] ) &&
			'' != $_POST['alg_wc_eu_vat_to_check']
		) {
			$vat_number = sanitize_text_field( wp_unslash( $_POST['alg_wc_eu_vat_to_check'] ) );
		}
		if (
			'checkout_block_first_load' == $vat_number &&
			version_compare( get_option( 'woocommerce_version', null ), '8.9.1', '>=' )
		) {
			$vat_number = WC()->customer->get_meta( alg_wc_eu_vat()->core->checkout_block->get_block_field_id() );
		}

		if (
			isset( $_POST['alg_wc_eu_vat_to_check'] ) &&
			'' != $_POST['alg_wc_eu_vat_to_check']
		) {
			$eu_vat_number = alg_wc_eu_vat_parse_vat(
				$vat_number,
				(
					isset( $_POST['billing_country'] ) ?
					sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) :
					''
				)
			);
			$billing_company = (
				isset( $_POST['billing_company'] ) ?
				sanitize_text_field( wp_unslash( $_POST['billing_company'] ) ) :
				''
			);
			if ( 'yes' === apply_filters( 'alg_wc_eu_vat_check_ip_location_country', 'no' ) ) {
				$country_by_ip   = alg_wc_eu_vat_get_customers_location_by_ip();
				$is_county_valid = ( $country_by_ip === $eu_vat_number['country'] );
				$is_valid        = (
					$is_county_valid ?
					alg_wc_eu_vat_validate_vat(
						$eu_vat_number['country'],
						$eu_vat_number['number'],
						$billing_company
					) :
					false
				);
				if ( ! $is_valid && ! $is_county_valid ) {
					alg_wc_eu_vat_log(
						$eu_vat_number['country'],
						$eu_vat_number['number'],
						$billing_company,
						'',
						sprintf(
							/* Translators: %s: Country. */
							__( 'Error: Country by IP does not match (%s)', 'eu-vat-for-woocommerce' ),
							$country_by_ip
						)
					);
				}
			} else {
				$is_valid = alg_wc_eu_vat_validate_vat(
					$eu_vat_number['country'],
					$eu_vat_number['number'],
					$billing_company
				);
			}
		} else {
			$is_valid = null;
		}

		$vat_allow_vias_not_available = false;
		if ( ! $is_valid ) {
			if ( null !== alg_wc_eu_vat()->core->get_error_vies_unavailable() ) {
				$is_valid = true;
				$vat_allow_vias_not_available = true;
			}
		}

		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid_before_preserve', $is_valid );

		$do_preserve_countries        = ( $is_valid && $this->do_keep_vat_in_selected_countries() );
		$do_preserve_shipping_country = ( $is_valid && $this->do_keep_vat_for_different_shipping_country() );

		if ( $do_preserve_shipping_country ) {
			$is_valid = null;
		}

		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid', $is_valid );
		if ( true === $do_preserve_shipping_country ) {
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', null );
		} else {
			alg_wc_eu_vat_session_set(
				'alg_wc_eu_vat_to_check',
				(
					isset( $_POST['alg_wc_eu_vat_to_check'] ) ?
					sanitize_text_field( wp_unslash( $_POST['alg_wc_eu_vat_to_check'] ) ) :
					''
				)
			);
		}

		$belgium_compatibility = (
			isset( $_POST['alg_wc_eu_vat_belgium_compatibility'] ) ?
			sanitize_text_field( wp_unslash( $_POST['alg_wc_eu_vat_belgium_compatibility'] ) ) :
			''
		);

		if ( 'yes' == $belgium_compatibility ) {
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid', $is_valid );
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', null );
		}

		$company_name_status = false;
		$company_name        = '';
		if ( true === alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check_company' ) ) {
			$company_name_status = true;
			$company_name        = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check_company_name' );
		}

		$return_status = $this->get_return_status( array(
			'eu_vat_number'                => $eu_vat_number ?? false,
			'do_preserve_shipping_country' => $do_preserve_shipping_country,
			'do_preserve_countries'        => $do_preserve_countries,
			'vat_allow_vias_not_available' => $vat_allow_vias_not_available,
			'company_name_status'          => $company_name_status,
			'company_name'                 => $company_name,
			'is_valid'                     => $is_valid,
		) );

		$return_company_name = '';
		$company_name        = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_return_company_name', null );
		if( ! empty( $company_name ) ) {
			if ( preg_match( "/[a-z]/i", $company_name ) ) {
				$return_company_name = $company_name;
			}
		}

		if (
			isset( $_POST['channel'] ) &&
			'bloock_api' == $_POST['channel'] &&
			! empty( WC()->customer )
		) {
			$is_exempt = (
				true === $is_valid &&
				! $do_preserve_countries
			);
			WC()->customer->set_is_vat_exempt( $is_exempt );
		}

		$return_data = array(
			'company'     => $return_company_name,
			'error'       => $return_status['error'],
			'status'      => $return_status['status'],
			'vat_details' => alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_details' ),
		);

		wp_send_json( $return_data );

		die();
	}

}

endif;

return new Alg_WC_EU_VAT_AJAX();
