<?php
/**
 * EU VAT for WooCommerce - Core Class
 *
 * @version 4.0.0
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Core' ) ) :

class Alg_WC_EU_VAT_Core {

	/**
	 * is_wc_version_below_3_0_0.
	 */
	public $is_wc_version_below_3_0_0 = null;

	/**
	 * eu_vat_ajax_instance.
	 */
	public $eu_vat_ajax_instance = null;

	/**
	 * required_in_countries.
	 */
	public $required_in_countries = null;

	/**
	 * show_in_countries.
	 */
	public $show_in_countries = null;

	/**
	 * checkout_block.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	public $checkout_block;

	/**
	 * Constructor.
	 *
	 * @version 4.0.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) "eu vat number" to "eu vat"?
	 * @todo    (feature) `add_eu_vat_verify_button` (`woocommerce_form_field_text`) (`return ( alg_wc_eu_vat_get_field_id() === $key ) ? $field . '<span style="font-size:smaller !important;">' . '[<a name="billing_eu_vat_number_verify" href="">' . __( 'Verify', 'eu-vat-for-woocommerce' ) . '</a>]' . '</span>' : $field;`)
	 */
	function __construct() {

		// Properties
		$this->is_wc_version_below_3_0_0 = version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' );

		// Functions
		require_once plugin_dir_path( __FILE__ ) . 'functions/alg-wc-eu-vat-functions-validation.php';

		// Shortcodes
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-shortcodes.php';

		// AJAX
		$this->eu_vat_ajax_instance = require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-ajax.php';

		// Admin
		require_once plugin_dir_path( __FILE__ ) . 'admin/class-alg-wc-eu-vat-admin.php';

		// Session
		add_action( 'init', array( $this, 'start_session' ) );

		// Exclusion
		add_action( 'init', array( $this, 'maybe_exclude_vat' ), PHP_INT_MAX );
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'maybe_exclude_vat' ), PHP_INT_MAX );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'maybe_exclude_vat' ), 99 );
		add_action( 'woocommerce_before_checkout_billing_form', array( $this, 'maybe_exclude_vat' ), PHP_INT_MAX );

		// Validation
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'checkout_validate_vat' ), PHP_INT_MAX );

		// Customer meta
		add_filter( 'woocommerce_customer_meta_fields', array( $this, 'add_eu_vat_number_customer_meta_field' ) );

		// Default value
		add_filter( 'default_checkout_' . alg_wc_eu_vat_get_field_id(), array( $this, 'add_default_checkout_billing_eu_vat_number' ), PHP_INT_MAX, 2 );

		// Frontend
		if ( 'yes' !== get_option( 'alg_wc_eu_vat_hide_eu_vat', 'no' ) ) {
			add_filter( 'woocommerce_checkout_fields', array( $this, 'add_eu_vat_checkout_field_to_frontend' ), 99 );
		}

		// Display
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-display.php';

		// Show zero VAT
		if ( 'yes' === get_option( 'alg_wc_eu_vat_always_show_zero_vat', 'no' ) ) {
			add_filter( 'woocommerce_cart_tax_totals', array( $this, 'always_show_zero_vat' ), PHP_INT_MAX, 2 );
		}

		// Show in countries, Required in countries
		add_filter( 'alg_wc_eu_vat_show_in_countries', array( $this, 'show_in_countries' ) );
		add_filter( 'alg_wc_eu_vat_required_in_countries', array( $this, 'required_in_countries' ) );

		$this->required_in_countries = apply_filters( 'alg_wc_eu_vat_required_in_countries', '' );
		$this->show_in_countries = apply_filters( 'alg_wc_eu_vat_show_in_countries', '' );

		// Show field for selected countries only
		$eu_vat_required = get_option( 'alg_wc_eu_vat_field_required', 'no' );

		if (
			'' != $this->show_in_countries ||
			'' != $this->required_in_countries ||
			'yes_for_company' === $eu_vat_required
		) {
			add_filter( 'woocommerce_get_country_locale', array( $this, 'set_eu_vat_country_locale' ), PHP_INT_MAX, 3 );
			add_filter( 'woocommerce_get_country_locale_default', array( $this, 'set_eu_vat_country_locale_default' ), PHP_INT_MAX );
			add_filter( 'woocommerce_country_locale_field_selectors', array( $this, 'set_eu_vat_country_locale_field_selectors' ), PHP_INT_MAX );
		}

		// "Place order" button confirmation
		if ( 'yes' === get_option( 'alg_wc_eu_vat_field_confirmation', 'no' ) ) {
			add_filter( 'wp_enqueue_scripts', array( $this, 'add_place_order_button_confirmation_script' ) );
		}

		add_action( 'wp_footer', array( $this, 'eu_vat_wp_footer'), PHP_INT_MAX );

		// Payment gateways
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'filter_available_payment_gateways_allowed' ), PHP_INT_MAX );

		add_filter( 'alg_wc_eu_vat_maybe_exclude_vat', array( $this, 'maybe_exclude_vat_free' ) );
		add_filter( 'alg_wc_eu_vat_set_eu_vat_country_locale', array( $this, 'set_eu_vat_country_locale_core' ), PHP_INT_MAX, 3 );

		add_filter( 'woocommerce_billing_fields', array( $this, 'add_frontend_edit_billing_fields' ), 10 );

		// REST
		add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'alg_wc_eu_vat_filter_order_response' ), PHP_INT_MAX, 3 );

		// Save VAT details
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_vat_details_to_order' ) );

		// Sign-up form
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-sign-up-form.php';

		// Keep VAT for individual product
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-keep-vat-individual-product.php';

		// Checkout block
		$this->checkout_block = require_once plugin_dir_path( __FILE__ ) . 'blocks/class-alg-wc-eu-vat-checkout-block.php';

		// Compatibility
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-compatibility.php';

	}

	/**
	 * add_frontend_edit_billing_fields.
	 *
	 * @version 3.2.2
	 * @since   2.12.14
	 */
	function add_frontend_edit_billing_fields( $fields ) {

		$user_roles = apply_filters( 'alg_wc_eu_vat_show_for_user_roles', array() );
		if (
			! empty( $user_roles ) &&
			! $this->check_current_user_roles( $user_roles )
		) {
			return $fields;
		}

		$field_id = alg_wc_eu_vat_get_field_id();

		$fields[ $field_id ] = array(
			'label'       => do_shortcode( get_option( 'alg_wc_eu_vat_field_label', __( 'EU VAT Number', 'eu-vat-for-woocommerce' ) ) ),
			'placeholder' => do_shortcode( get_option( 'alg_wc_eu_vat_field_placeholder', __( 'EU VAT Number', 'eu-vat-for-woocommerce' ) ) ),
			'required'    => false,
			'clear'       => false,
			'type'        => 'text',
			'class'       => array( 'alg-wc-frontend-billing-edit' ),
			'priority'    => get_option( 'alg_wc_eu_vat_field_priority', 200 ),
		);

		return $fields;
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
	 * set_eu_vat_country_locale.
	 *
	 * @version 4.0.0
	 * @since   1.7.0
	 */
	function set_eu_vat_country_locale_core( $country_locales, $show_in_countries, $required_in_countries ) {

		if ( has_block( 'woocommerce/checkout' ) ) {
			return $country_locales;
		}

		$show_eu_vat_field_countries     = array_map( 'strtoupper', array_map( 'trim', explode( ',', $show_in_countries ) ) );
		$required_eu_vat_field_countries = array_map( 'strtoupper', array_map( 'trim', explode( ',', $required_in_countries ) ) );

		$eu_vat_required = get_option( 'alg_wc_eu_vat_field_required', 'no' );

		$original_hidden = false;

		// Enable field in selected locales
		$original_required = ( 'yes' === $eu_vat_required );

		if ( ! empty( $show_eu_vat_field_countries ) ) {
			$country_locales_keys = array_keys( $country_locales );
			$ky2                  = $country_locales_keys;
			$wc_countries         = new WC_Countries();
			$w_countries          = $wc_countries->get_countries();
			$ky1                  = array_keys( $w_countries );
			$arr_dif              = array_diff( $ky1, $ky2 );
		}

		if (
			'yes_for_company'   === $eu_vat_required ||
			'yes_for_countries' === $eu_vat_required ||
			'no_for_countries'  === $eu_vat_required ||
			! empty( $show_eu_vat_field_countries )
		) {
			if ( 'yes_for_company' === $eu_vat_required ) {
				if ( ! empty( WC()->checkout()->get_value( 'billing_company' ) ) ) {
					$is_required = true;
				}
			}
			foreach ( $country_locales as $country_code => &$country_locale ) {

				$is_required = $original_required;
				$hidden = $original_hidden;

				if ( 'yes_for_countries' ===  $eu_vat_required ) {
					if ( in_array( $country_code, $required_eu_vat_field_countries ) ) {
						$is_required = true;
					}
				} elseif ( 'no_for_countries' === $eu_vat_required ) {
					if ( in_array( $country_code, $required_eu_vat_field_countries ) ) {
						$is_required = false;
					} else {
						$is_required = true;
					}
				}

				if ( ! empty( $show_eu_vat_field_countries[0] ) ) {
					if ( in_array( $country_code, $show_eu_vat_field_countries ) ) {
						$hidden = false;
					} else {
						$hidden = true;
					}
				} else {
					$hidden = false;
				}

				if ( 'yes_for_company' === $eu_vat_required ) {
					$is_required = false;
				}

				$country_locale[ alg_wc_eu_vat_get_field_id( true ) ] = array(
					'required' => $is_required,
					'hidden'   => $hidden,
				);
			}

			if ( ! empty( $show_eu_vat_field_countries[0] ) ) {
				foreach ( $show_eu_vat_field_countries as $count_code ) {
					$country_locales[ $count_code ][ alg_wc_eu_vat_get_field_id( true ) ] = array(
						'hidden' => false,
					);
				}
			}

			$hidden = $original_hidden;

			if ( ! empty( $arr_dif ) ) {
				foreach ( $arr_dif as $con ) {
					if ( ! empty( $show_eu_vat_field_countries[0] ) ) {
						if ( in_array( $con, $show_eu_vat_field_countries ) ) {
							$hidden = false;
						} else {
							$hidden = true;
						}
					} else {
						$hidden = false;
					}
					$country_locales[ $con ][ alg_wc_eu_vat_get_field_id( true ) ] = array(
						'hidden'   => $hidden,
						'required' => $is_required,
					);
				}
			}

			$hidden = $original_hidden;

			if ( ! empty( $required_eu_vat_field_countries ) ) {
				foreach ( $required_eu_vat_field_countries as $country_code_re ) {

					$is_required = $original_required;

					if ( 'yes_for_countries' === $eu_vat_required ) {
						$is_required = true;
					} elseif ( 'no_for_countries' === $eu_vat_required ) {
						$is_required = false;
					}

					if ( ! empty( $show_eu_vat_field_countries[0] ) ) {
						if ( in_array( $country_code_re, $show_eu_vat_field_countries ) ) {
							$hidden = false;
						}else{
							$hidden = true;
						}
					}else{
						$hidden = false;
					}

					if ( 'yes_for_company' === $eu_vat_required ) {
						$is_required = false;

					}

					$country_locales[ $country_code_re ][ alg_wc_eu_vat_get_field_id( true ) ] = array(
						'required' => $is_required,
						'hidden'   => $hidden,
					);
				}
			}
		}

		return $country_locales;
	}

	/**
	 * show_in_countries.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 *
	 * @todo    (dev) remove (Pro)?
	 */
	function show_in_countries( $value ) {
		return get_option( 'alg_wc_eu_vat_show_in_countries', '' );
	}

	/**
	 * required_in_countries.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function required_in_countries( $value ) {
		if (
			'yes_for_countries' === get_option( 'alg_wc_eu_vat_field_required', 'no' ) ||
			'no_for_countries'  === get_option( 'alg_wc_eu_vat_field_required', 'no' )
		) {
			$arr = get_option( 'alg_wc_eu_vat_field_required_countries', array() );
			if ( ! empty( $arr ) ) {
				return implode( ',', $arr );
			} else {
				return '';
			}
		}
		return '';
	}

	/**
	 * maybe_exclude_vat_free.
	 *
	 * @version 4.0.0
	 * @since   1.7.0
	 */
	function maybe_exclude_vat_free( $value ) {
		$selected_country_at_checkout = '';
		$preserve_base_country_check_passed = true;
		if ( 'no' != ( $preserve_option_value = get_option( 'alg_wc_eu_vat_preserve_in_base_country', 'no' ) ) ) {
			$selected_country = substr( alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' ), 0, 2 );

			if ( ! ctype_alpha( $selected_country ) ) {
				$selected_country = '';
				if ( 'yes' === get_option( 'alg_wc_eu_vat_allow_without_country_code', 'no' ) ) {
					// Getting country from POST, or from the customer object
					if ( ! ctype_alpha( $selected_country ) ) {
						$selected_country = WC()->checkout()->get_value( 'billing_country' );
					}
					// Fallback #1
					if ( ! ctype_alpha( $selected_country ) && ! empty( $_REQUEST['post_data'] ) ) {
						parse_str( $_REQUEST['post_data'], $post_data_args );
						if ( ! empty( $post_data_args['billing_country'] ) ) {
							$selected_country = sanitize_text_field( $post_data_args['billing_country'] );
						}
					}
					// Fallback #2
					if ( ! ctype_alpha( $selected_country ) && ! empty( $_REQUEST['billing_country'] ) ) {
						$selected_country = sanitize_text_field( $_REQUEST['billing_country'] );
					}
					// Fallback #3
					if ( ! ctype_alpha( $selected_country ) && ! empty( $_REQUEST['country'] ) ) {
						$selected_country = sanitize_text_field( $_REQUEST['country'] );
					}
				}
				if ( ! ctype_alpha( $selected_country ) ) {
					return false;
				}
			}
			$selected_country = strtoupper( $selected_country );
			if ( 'EL' === $selected_country ) {
				$selected_country = 'GR';
			}

			$selected_country_at_checkout = WC()->checkout()->get_value( 'billing_country' );

			if ( 'yes' === $preserve_option_value ) {
				$location = wc_get_base_location();
				if ( empty( $location['country'] ) ) {
					$location = wc_format_country_state_string( apply_filters( 'woocommerce_customer_default_location', get_option( 'woocommerce_default_country' ) ) );
				}

				$preserve_base_country_check_passed = ( strtoupper( $location['country'] ) !== $selected_country_at_checkout );
			} elseif ( '' != get_option( 'alg_wc_eu_vat_preserve_in_base_country_locations', '' ) ) { // `list`
				$locations = array_map( 'strtoupper', array_map( 'trim', explode( ',', get_option( 'alg_wc_eu_vat_preserve_in_base_country_locations', '' ) ) ) );
				$preserve_base_country_check_passed = ( ! in_array( $selected_country_at_checkout, $locations ) );
			}
		}

		if (
			'no' != ( $preserve_option_value = get_option( 'alg_wc_eu_vat_preserv_vat_for_different_shipping', 'no' ) ) &&
			! $preserve_base_country_check_passed
		) {

			$billing_country  = ( $_REQUEST['billing_country']  ?? '' );
			$shipping_country = ( $_REQUEST['shipping_country'] ?? '' );

			$is_country_not_same = ( strtoupper( $billing_country ) !== strtoupper( $shipping_country) );

			if ( $is_country_not_same ) {
				$preserve_base_country_check_passed = true;
			}
		}

		return $preserve_base_country_check_passed;
	}

	/**
	 * current_url.
	 *
	 * @version 2.11.9
	 * @since   1.4.1
	 */
	function current_url() {
		if ( ! defined( 'WP_CLI' ) ) {
			if (
				array_key_exists( 'SERVER_NAME', $_SERVER ) &&
				array_key_exists( 'SERVER_PORT', $_SERVER ) &&
				array_key_exists( 'REQUEST_URI', $_SERVER )
			) {

				if ( array_key_exists( 'HTTPS', $_SERVER ) ) {
					$current_url = (
						'on' !== $_SERVER["HTTPS"] ?
						'http://'  . $_SERVER["SERVER_NAME"] :
						'https://' . $_SERVER["SERVER_NAME"]
					);
				} else {
					$current_url = 'http://' . $_SERVER["SERVER_NAME"];
				}

				$current_url .= (
					$_SERVER["SERVER_PORT"] != 80 && $_SERVER["SERVER_PORT"] != 443 ?
					":" . $_SERVER["SERVER_PORT"] :
					""
				);
				$current_url .= $_SERVER["REQUEST_URI"];

				if (
					false !== strpos( $current_url, 'wp-json/siteground-optimizer/v1/test-url-cache' ) ||
					false !== strpos( $current_url, 'wp-json/siteground-optimizer' )
				) {
					return 'test-url-cache';
				} else {
					return $current_url;
				}

			}
		}
		return get_option( 'siteurl' );
	}

	/**
	 * add_place_order_button_confirmation_script.
	 *
	 * @version 3.0.0
	 * @since   1.4.1
	 */
	function add_place_order_button_confirmation_script() {

		if ( function_exists( 'is_checkout' ) && is_checkout() ) {

			wp_enqueue_script(
				'alg-wc-eu-vat-place-order',
				trailingslashit( alg_wc_eu_vat()->plugin_url() ) . 'includes/js/alg-wc-eu-vat-place-order.js',
				array( 'jquery' ),
				alg_wc_eu_vat()->version,
				true
			);
			wp_localize_script(
				'alg-wc-eu-vat-place-order',
				'place_order_data',
				array(
					'confirmation_text' => do_shortcode(
						get_option(
							'alg_wc_eu_vat_field_confirmation_text',
							__( 'You didn\'t set your VAT ID. Are you sure you want to continue?', 'eu-vat-for-woocommerce' )
						)
					),
					'yes_text'          => __( 'Yes', 'eu-vat-for-woocommerce' ),
					'no_text'           => __( 'No', 'eu-vat-for-woocommerce' ),
					'yesBg'             => apply_filters( 'alg_wc_eu_vat_confirmation_bg_yes', 'green' ),
					'noBg'              => apply_filters( 'alg_wc_eu_vat_confirmation_bg_no', 'red' ),
				)
			);

			wp_enqueue_script(
				'alg-wc-eu-vat-confirmation',
				alg_wc_eu_vat()->plugin_url() . '/includes/js/alg-wc-eu-vat-confirmo.js',
				array('jquery'),
				alg_wc_eu_vat()->version,
				true
			);

			wp_enqueue_style(
				'alg-wc-eu-vat-confirmation-styles',
				alg_wc_eu_vat()->plugin_url() . '/includes/css/alg-wc-eu-vat-confirmo.css',
				array(),
				alg_wc_eu_vat()->version,
				false
			);

		}
	}

	/**
	 * set_eu_vat_country_locale_field_selectors.
	 *
	 * @version 1.4.1
	 * @since   1.4.0
	 */
	function set_eu_vat_country_locale_field_selectors( $locale_fields ) {
		$locale_fields[ alg_wc_eu_vat_get_field_id( true ) ] = '#' . alg_wc_eu_vat_get_field_id() . '_field';
		return $locale_fields;
	}

	/**
	 * set_eu_vat_country_locale_default.
	 *
	 * @version 4.0.0
	 * @since   1.4.0
	 */
	function set_eu_vat_country_locale_default( $default_locale ) {

		if ( has_block( 'woocommerce/checkout' ) ) {
			return $default_locale;
		}

		// Disable field in default locale

		$eu_vat_required = get_option( 'alg_wc_eu_vat_field_required', 'no' );
		$required = true;

		if ( $eu_vat_required == 'yes' ) {
			$required = true;
		} elseif ( $eu_vat_required == 'no' ){
			$required = false;
		} elseif ( $eu_vat_required == 'yes_for_countries' ) {
			$required = false;
		} elseif ( $eu_vat_required == 'no_for_countries' ) {
			$required = true;
		}

		$default_locale[ alg_wc_eu_vat_get_field_id( true ) ] = array(
			'required' => $required,
			'hidden'   => false,
		);
		return $default_locale;
	}

	/**
	 * set_eu_vat_country_locale.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 */
	function set_eu_vat_country_locale( $country_locales ) {
		return apply_filters( 'alg_wc_eu_vat_set_eu_vat_country_locale', $country_locales, $this->show_in_countries, $this->required_in_countries );
	}

	/**
	 * always_show_zero_vat.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @todo    (dev) remove `$zero_tax->amount`, `$zero_tax->tax_rate_id`, `$zero_tax->is_compound` (as they are not really used in `review-order` template)?
	 */
	function always_show_zero_vat( $tax_totals, $cart ) {
		if ( empty( $tax_totals ) && is_checkout() ) {
			$zero_tax = new stdClass();
			$zero_tax->amount           = 0.00;
			$zero_tax->tax_rate_id      = 0;
			$zero_tax->is_compound      = false;
			$zero_tax->label            = esc_html( WC()->countries->tax_or_vat() );
			$zero_tax->formatted_amount = wc_price( 0.00 );
			$tax_totals['TAX-1'] = $zero_tax;
		}
		return $tax_totals;
	}

	/**
	 * get_customer_decide_field_data.
	 *
	 * @version 1.7.2
	 * @since   1.3.0
	 *
	 * @todo    (dev) rethink `$is_required` (check filters: `woocommerce_default_address_fields`, `woocommerce_billing_fields`)
	 * @todo    (dev) `default`?
	 * @todo    (dev) `autocomplete`?
	 * @todo    (dev) `value`?
	 */
	function get_customer_decide_field_data() {
		return array(
			'type'     => 'checkbox',
			'label'    => do_shortcode(
				get_option(
					'alg_wc_eu_vat_field_let_customer_decide_label',
					__( 'I don\'t have a VAT ID', 'eu-vat-for-woocommerce' )
				)
			),
			'class'    => array( 'form-row-wide' ),
			'priority' => get_option( 'alg_wc_eu_vat_field_priority', 200 ),
		);
	}

	/**
	 * belgium_compatibility_field_data.
	 *
	 * @version 1.7.2
	 * @since   1.3.0
	 *
	 * @todo    (dev) rethink `$is_required` (check filters: `woocommerce_default_address_fields`, `woocommerce_billing_fields`)
	 * @todo    (dev) `default`?
	 * @todo    (dev) `autocomplete`?
	 * @todo    (dev) `value`?
	 */
	function belgium_compatibility_field_data() {
		return array(
			'type'      => 'checkbox',
			'label'     => do_shortcode( get_option( 'alg_wc_eu_vat_belgium_compatibility_label', __('I have a valid VAT but not exempted', 'eu-vat-for-woocommerce') ) ),
			'class'     => array('form-row-wide'),
			'priority'  => get_option( 'alg_wc_eu_vat_field_priority', 200 ),
		);
	}

	/**
	 * get_field_data.
	 *
	 * @version 4.0.0
	 * @since   1.3.0
	 *
	 * @todo    (dev) rethink `$is_required` (check filters: `woocommerce_default_address_fields`, `woocommerce_billing_fields`)
	 * @todo    (dev) `default`?
	 * @todo    (dev) `autocomplete`?
	 * @todo    (dev) `value`?
	 */
	function get_field_data() {

		$eu_vat_required = get_option( 'alg_wc_eu_vat_field_required', 'no' );

		$is_required = ( 'yes' === $eu_vat_required );

		if ( ! empty( WC()->checkout() ) ) {

			if ( '' != $this->required_in_countries ) {
				$required_eu_vat_field_countries = array_map( 'strtoupper', array_map( 'trim', explode( ',', $this->required_in_countries ) ) );
				if ( 'yes_for_countries' === $eu_vat_required ) {
					if ( in_array( WC()->checkout()->get_value( 'billing_country' ), $required_eu_vat_field_countries ) ) {
						$is_required = true;
					}
				} elseif ( 'no_for_countries' === $eu_vat_required ) {
					if ( in_array( WC()->checkout()->get_value( 'billing_country' ), $required_eu_vat_field_countries ) ) {
						$is_required = false;
					} else {
						$is_required = true;
					}
				} else {
					if ( ! in_array( WC()->checkout()->get_value( 'billing_country' ), $required_eu_vat_field_countries ) ) {
						$is_required = false;
					}
				}
			}

			if ( 'yes_for_company' === $eu_vat_required ) {
				if ( ! empty( WC()->checkout()->get_value( 'billing_company' ) ) ) {
					$is_required = true;
				}
				$is_required = false;
			}

			if ( 'yes' === get_option( 'alg_wc_eu_vat_field_let_customer_decide', 'no' ) ) {
				$field_id = alg_wc_eu_vat_get_field_id();
				if (
					isset( $_POST[ $field_id . '_customer_decide' ] ) &&
					1 == $_POST[ $field_id . '_customer_decide' ]
				) {
					$is_required = false;
				}
			}

		}

		return array(
			'type'              => 'text',
			'label'             => do_shortcode( get_option( 'alg_wc_eu_vat_field_label', __( 'EU VAT Number', 'eu-vat-for-woocommerce' ) ) ),
			'description'       => do_shortcode( get_option( 'alg_wc_eu_vat_field_description', '' ) ),
			'placeholder'       => do_shortcode( get_option( 'alg_wc_eu_vat_field_placeholder', __( 'EU VAT Number', 'eu-vat-for-woocommerce' ) ) ),
			'required'          => $is_required,
			'custom_attributes' => ( 0 != ( $maxlength = get_option( 'alg_wc_eu_vat_field_maxlength', 0 ) ) ? array( 'maxlength' => $maxlength ) : array() ),
			'clear'             => ( 'yes' === get_option( 'alg_wc_eu_vat_field_clear', 'yes' ) ),
			'class'             => array( get_option( 'alg_wc_eu_vat_field_class', 'form-row-wide' ) ),
			'label_class'       => array( get_option( 'alg_wc_eu_vat_field_label_class', '' ) ),
			'validate'          => ( 'yes' === get_option( 'alg_wc_eu_vat_validate', 'yes' ) ? array( 'eu-vat-number' ) : array() ),
			'priority'          => get_option( 'alg_wc_eu_vat_field_priority', 200 ),
		);
	}

	/**
	 * is_tax_status_none.
	 *
	 * @version 2.11.5
	 * @since   2.9.18
	 */
	function is_tax_status_none(){
		if ( function_exists( 'WC' ) ) {
			if ( isset( WC()->cart ) ) {
				foreach( WC()->cart->get_cart() as $cart_item ) {

					$product_in_cart = $cart_item['product_id'];
					$product_info    = wc_get_product( $product_in_cart );
					$tax_status      = $product_info->get_tax_status();
					if ( 'none' == $tax_status ) {
						return true;
					}

				}
			}
		}
		return false;
	}

	/**
	 * add_eu_vat_checkout_field_to_frontend.
	 *
	 * @version 2.9.19
	 * @since   1.0.0
	 */
	function add_eu_vat_checkout_field_to_frontend( $fields ) {

		if ( 'yes' === get_option( 'alg_wc_eu_vat_field_hide_tax_status_none', 'no' ) ) {
			if ( $this->is_tax_status_none() ) {
				return $fields;
			}
		}

		$user_roles = apply_filters( 'alg_wc_eu_vat_show_for_user_roles', array() );
		if (
			! empty( $user_roles ) &&
			! $this->check_current_user_roles( $user_roles )
		) {
			return $fields;
		}

		$is_required = ( 'yes' === get_option( 'alg_wc_eu_vat_field_required', 'no' ) );
		if ( $is_required && 'yes' === get_option( 'alg_wc_eu_vat_field_let_customer_decide', 'no' ) ) {
			$fields['billing'][ alg_wc_eu_vat_get_field_id() . '_customer_decide' ] = $this->get_customer_decide_field_data();
		}

		$fields['billing'][ alg_wc_eu_vat_get_field_id() ] = $this->get_field_data();

		if ( 'yes' === get_option( 'alg_wc_eu_vat_belgium_compatibility', 'no' ) ) {
			$fields['billing'][ alg_wc_eu_vat_get_field_id() . '_belgium_compatibility' ] = $this->belgium_compatibility_field_data();
		}

		return $fields;
	}

	/**
	 * add_default_checkout_billing_eu_vat_number.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function add_default_checkout_billing_eu_vat_number( $default_value, $field_key ) {
		if ( '' != ( $eu_vat_number_to_check = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' ) ) ) {
			return $eu_vat_number_to_check;
		} elseif ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			if ( $meta = get_user_meta( $current_user->ID, alg_wc_eu_vat_get_field_id(), true ) ) {
				return $meta;
			}
		}
		return $default_value;
	}

	/**
	 * add_eu_vat_number_customer_meta_field.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	function add_eu_vat_number_customer_meta_field( $fields ) {
		$fields['billing']['fields'][ alg_wc_eu_vat_get_field_id() ] = array(
			'label'       => do_shortcode( get_option( 'alg_wc_eu_vat_field_label', __( 'EU VAT Number', 'eu-vat-for-woocommerce' ) ) ),
			'description' => ''
		);

		return $fields;
	}

	/**
	 * start_session.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function start_session() {
		$curl = rtrim($this->current_url(),'/');
		$home = rtrim(home_url(),'/');
		if(!( $curl == $home )){

			$checkout_page_url = rtrim(wc_get_checkout_url(), '/');
			$cart_url = rtrim(get_permalink( wc_get_page_id( 'cart' ) ), '/');

			if($curl == $cart_url || $curl == $checkout_page_url){
				alg_wc_eu_vat_session_start();

				$args = array();
				if ( isset( $_POST['post_data'] ) ) {
					parse_str( $_POST['post_data'], $args );
					if ( isset( $args[ alg_wc_eu_vat_get_field_id() ] ) && alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' ) != $args[ alg_wc_eu_vat_get_field_id() ] ) {
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid', null );
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', null );
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check_company', null );
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check_company_name', null );
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid_before_preserve', null );
					}
				}
			}
		}else{
			if('yes' === get_option( 'alg_wc_eu_vat_sitepress_optimizer_dynamic_caching', 'no' ))
			{
				if($curl == 'test-url-cache'){
					$return = array(
						'status'  => 200,
						'data'    => array(),
						'message' => 'La URL está en la caché'
					);

					wp_send_json($return);
				}
			}
		}
	}

	/**
	 * handle_user_roles.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function handle_user_roles( $role ) {
		return ( '' == $role ? 'guest' : ( 'super_admin' == $role ? 'administrator' : $role ) );
	}

	/**
	 * check_current_user_roles.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 *
	 * @todo    (dev) assign `array( 'guest' )` if `wp_get_current_user()` does not exist?
	 */
	function check_current_user_roles( $user_roles_to_check ) {
		if ( ! empty( $user_roles_to_check ) ) {
			if ( ! isset( $this->current_user ) ) {
				if ( ! function_exists( 'wp_get_current_user' ) ) {
					return false;
				}
				$this->current_user = wp_get_current_user();
				if ( ! isset( $this->current_user->roles ) || empty( $this->current_user->roles ) ) {
					$this->current_user->roles = array( 'guest' );
				}
				$this->current_user->roles = array_map( array( $this, 'handle_user_roles' ), $this->current_user->roles );
			}
			$user_roles_to_check = array_map( array( $this, 'handle_user_roles' ), $user_roles_to_check );
			$intersect           = array_intersect( $this->current_user->roles, $user_roles_to_check );
			return ( ! empty( $intersect ) );
		}
		return false;
	}

	/**
	 * is_cart_or_checkout_or_ajax.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function is_cart_or_checkout_or_ajax() {
		return (
			is_checkout() ||
			is_cart() ||
			defined( 'WOOCOMMERCE_CHECKOUT' ) ||
			defined( 'WOOCOMMERCE_CART' ) ||
			(
				defined( 'DOING_AJAX' ) &&
				DOING_AJAX
			)
		);
	}

	/**
	 * is_validate_and_exempt.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function is_validate_and_exempt() {
		return (
			'yes' === get_option( 'alg_wc_eu_vat_validate', 'yes' ) &&
			'yes' === get_option( 'alg_wc_eu_vat_disable_for_valid', 'yes' )
		);
	}

	/**
	 * get_error_vies_unavailable.
	 *
	 * @version 2.11.11
	 * @since   2.11.11
	 */
	function get_error_vies_unavailable() {
		if ( 'yes' === get_option( 'alg_wc_eu_vat_validate_vies_not_available', 'no' ) ) {
			$vies_error_message = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_vies_error_message', null );
			if ( null !== $vies_error_message ) {
				$error_msg = strval( $vies_error_message );
				$error_msg = trim( $error_msg );
				return $error_msg;
			}
		}
		return null;
	}

	/**
	 * check_and_save_eu_vat.
	 *
	 * @version 1.7.1
	 * @since   1.7.1
	 *
	 * @todo    (dev) use in `Alg_WC_EU_VAT_AJAX::alg_wc_eu_vat_validate_action()`
	 */
	function check_and_save_eu_vat( $eu_vat_to_check, $billing_country, $billing_company ) {
		$eu_vat_number = alg_wc_eu_vat_parse_vat( $eu_vat_to_check, $billing_country );
		if ( 'yes' === apply_filters( 'alg_wc_eu_vat_check_ip_location_country', 'no' ) ) {
			$country_by_ip   = alg_wc_eu_vat_get_customers_location_by_ip();
			$is_county_valid = ( $country_by_ip === $eu_vat_number['country'] );
			$is_valid        = $is_county_valid ? alg_wc_eu_vat_validate_vat( $eu_vat_number['country'], $eu_vat_number['number'], $billing_company ) : false;
			if ( ! $is_county_valid ) {
				alg_wc_eu_vat_maybe_log(
					$eu_vat_number['country'],
					$eu_vat_number['number'],
					$billing_company,
					'',
					sprintf(
						__( 'Error: Country by IP does not match (%s)', 'eu-vat-for-woocommerce' ),
						$country_by_ip
					)
				);
			}
		} else {
			$is_valid = alg_wc_eu_vat_validate_vat( $eu_vat_number['country'], $eu_vat_number['number'], $billing_company );
		}

		if (
			! $is_valid &&
			null !== $this->get_error_vies_unavailable()
		) {
			$is_valid = true;
		}

		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid',    $is_valid );
		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', $eu_vat_to_check );
		return $is_valid;
	}

	/**
	 * is_valid_and_exists.
	 *
	 * @version 1.7.1
	 * @since   1.7.0
	 */
	function is_valid_and_exists() {
		$is_valid = ( true === alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_valid' ) && null !== alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' ) );
		return $is_valid;
	}

	/**
	 * maybe_exclude_vat.
	 *
	 * @version 3.0.0
	 * @since   1.0.0
	 *
	 * @todo    (fix) mini cart!
	 */
	function maybe_exclude_vat() {
		if ( empty( WC()->customer ) || ! $this->is_cart_or_checkout_or_ajax() ) {
			return;
		}

		if ( $this->check_current_user_roles( get_option( 'alg_wc_eu_vat_exempt_for_user_roles', array() ) ) ) {
			$is_exempt = true;
		} elseif ( $this->check_current_user_roles( get_option( 'alg_wc_eu_vat_not_exempt_for_user_roles', array() ) ) ) {
			$is_exempt = false;
		} elseif ( $this->is_validate_and_exempt() && $this->is_valid_and_exists() ) {
			$is_exempt = apply_filters( 'alg_wc_eu_vat_maybe_exclude_vat', true );
		} else {
			$is_exempt = false;
		}

		if ( 'yes' === get_option( 'alg_wc_eu_vat_validate_force_page_reload', 'no' ) ) {
			if ( ( is_checkout() || is_cart() ) && ! $is_exempt ) {
				$billing_eu_vat_number = WC()->customer->get_meta( 'billing_eu_vat_number' );
				$billing_country       = WC()->customer->get_meta( 'billing_country' );
				$billing_company       = WC()->customer->get_meta( 'billing_company' );
				if ( ! empty( $billing_eu_vat_number ) ) {
					$is_valid = $this->check_and_save_eu_vat( $billing_eu_vat_number, $billing_country, $billing_company );
					if ( $is_valid ) {
						$is_exempt = apply_filters( 'alg_wc_eu_vat_maybe_exclude_vat', true );
					}
				}
			}
		}

		if ( true === alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_i_am_company' ) ) {
			$is_exempt = true;
		}

		WC()->customer->set_is_vat_exempt( $is_exempt );

		do_action( 'alg_wc_eu_vat_exempt_applied', $is_exempt );
	}

	/**
	 * filter_available_payment_gateways_allowed.
	 *
	 * @version 2.12.4
	 * @since   1.0.0
	 *
	 * @todo    [fix] (important) mini cart
	 */
	function filter_available_payment_gateways_allowed( $available_gateways ) {

		$gateways = $available_gateways;
		$is_vat_valid = false;

		if ( $this->check_current_user_roles( get_option( 'alg_wc_eu_vat_exempt_for_user_roles', array() ) ) ) {
			$is_exempt = true;
		} elseif ( $this->check_current_user_roles( get_option( 'alg_wc_eu_vat_not_exempt_for_user_roles', array() ) ) ) {
			$is_exempt = false;
		} elseif ( $this->is_validate_and_exempt() && $this->is_valid_and_exists() ) {
			$is_exempt = apply_filters( 'alg_wc_eu_vat_maybe_exclude_vat_on_available_gateway', true );
		} else {
			$is_exempt = false;
		}

		if ( true === alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_valid_before_preserve' ) ) {
			$is_vat_valid = true;
		}

		$alg_wc_eu_vat_allowed_payment_gateway = get_option( 'alg_wc_eu_vat_allowed_payment_gateway', array() );
		$alg_wc_eu_vat_allow_specific_payment = get_option( 'alg_wc_eu_vat_allow_specific_payment', 'no' );

		if ( 'yes' == $alg_wc_eu_vat_allow_specific_payment ) {
			if ( ! empty( $alg_wc_eu_vat_allowed_payment_gateway ) && ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway_id => $gateway ) {
					if ( in_array($gateway_id, $alg_wc_eu_vat_allowed_payment_gateway ) ) {
						unset( $available_gateways[ $gateway_id ] );
					}
				}

				if ( $is_exempt || $is_vat_valid ) {
					return $gateways;
				} else {
					return $available_gateways;
				}
			} else {
				return $available_gateways;
			}
		} else {
			return $available_gateways;
		}
	}

	/**
	 * checkout_validate_vat.
	 *
	 * @version 3.1.4
	 * @since   1.0.0
	 *
	 * @todo    [dev] (important) simplify the code
	 */
	function checkout_validate_vat( $_posted ) {
		$is_required = ( 'yes' === get_option( 'alg_wc_eu_vat_field_required', 'no' ) );

		$eu_vat_required = get_option( 'alg_wc_eu_vat_field_required', 'no' );

		$field_id = alg_wc_eu_vat_get_field_id();

		if( $is_required && 'yes' === get_option( 'alg_wc_eu_vat_field_let_customer_decide', 'no' ) ){
			if(isset($_posted[$field_id . '_customer_decide']) && $_posted[$field_id . '_customer_decide']==1){
				return;
			}
		}

		if( 'yes' === get_option( 'alg_wc_eu_vat_belgium_compatibility', 'no' ) ){
			if(isset($_posted[$field_id . '_belgium_compatibility']) && $_posted[$field_id . '_belgium_compatibility']==1){
				alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid', false );
				alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', null );
				return;
			}
		}

		$show_eu_vat_field_countries = array();

		if ( ! empty( $this->show_in_countries ) ) {
			$show_eu_vat_field_countries = array_map( 'strtoupper', array_map( 'trim', explode( ',', $this->show_in_countries ) ) );
		}

		$country_code = isset( $_posted['billing_country'] ) ? esc_attr( $_posted['billing_country'] ) : '';

		if (
			'yes_for_company' === $eu_vat_required &&
			! empty( $_posted['billing_company'] ) &&
			isset( $_posted[ alg_wc_eu_vat_get_field_id() ] ) &&
			empty( $_posted[ alg_wc_eu_vat_get_field_id() ] )
		) {

			$is_valid = false;

			if (
				! empty( $show_eu_vat_field_countries[0] ) &&
				! in_array( $country_code, $show_eu_vat_field_countries )
			) {
				$is_valid = true;
			}

			if ( ! $is_valid ) {
				wc_add_notice(
					str_replace( '%eu_vat_number%', esc_attr( $_posted[ alg_wc_eu_vat_get_field_id() ] ),
						do_shortcode(
							get_option(
								'alg_wc_eu_vat_not_valid_message',
								__( '<strong>EU VAT Number</strong> is not valid.', 'eu-vat-for-woocommerce' )
							)
						)
					),
					'error'
				);
			}

		}

		if ( 'yes' === get_option( 'alg_wc_eu_vat_validate', 'yes' ) ) {
			if (
				( '' != $_posted[ alg_wc_eu_vat_get_field_id() ] ) &&
				(
					null === alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_valid' ) ||
					false == alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_valid' ) ||
					null === alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' ) ||
					$_posted[ alg_wc_eu_vat_get_field_id() ] != alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' )
				)
			) {

				$is_valid = false;
				if (
					'yes' === get_option( 'alg_wc_eu_vat_force_checkout_recheck', 'no' ) &&
					$_posted[ alg_wc_eu_vat_get_field_id() ] != alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' )
				) {
					$is_valid = $this->check_and_save_eu_vat(
						$_posted[ alg_wc_eu_vat_get_field_id() ],
						( isset( $_posted['billing_country'] ) ? esc_attr($_posted['billing_country']) : '' ),
						( isset( $_posted['billing_company'] ) ? esc_attr($_posted['billing_company']) : '' )
					);
				} else {

					$vat_number = esc_attr($_posted[ alg_wc_eu_vat_get_field_id() ]);
					$billing_country = isset( $_posted['billing_country'] ) ? esc_attr($_posted['billing_country']) : '';
					$billing_company = isset( $_posted['billing_company'] ) ? esc_attr($_posted['billing_company']) : '';
					$vat_number = preg_replace('/\s+/', '', $vat_number);
					$eu_vat_number = alg_wc_eu_vat_parse_vat( $vat_number, $billing_country );

					// VAT validate manually pre-saved number
					if( 'yes' === get_option( 'alg_wc_eu_vat_manual_validation_enable', 'no' ) ) {
						if( '' != ( $manual_validation_vat_numbers = get_option( 'alg_wc_eu_vat_manual_validation_vat_numbers', '' ) ) ) {
							$prevalidated_VAT_numbers = array();
							$prevalidated_VAT_numbers = explode( ',', $manual_validation_vat_numbers );
							$sanitized_vat_numbers = array_map('trim', $prevalidated_VAT_numbers);

							$conjuncted_vat_number = $billing_country . '' . $eu_vat_number['number'];
							if ( isset( $sanitized_vat_numbers[0] ) ){
								if ( in_array( $conjuncted_vat_number, $sanitized_vat_numbers ) ) {
									alg_wc_eu_vat_maybe_log(
										$eu_vat_number['country'],
										$eu_vat_number['number'],
										$billing_company,
										'',
										__( 'Success (checkout): VAT ID valid. Matched with prevalidated VAT numbers.', 'eu-vat-for-woocommerce' )
									);
									$is_valid = true;
								}
							}
						}
					}
				}

				if ( 'no' != ( $preserve_option_value = get_option( 'alg_wc_eu_vat_preserv_vat_for_different_shipping', 'no' ) ) ) {
					$billing_country = isset( $_REQUEST['billing_country'] ) ? esc_attr($_REQUEST['billing_country']) : '';
					$shipping_country = isset( $_REQUEST['shipping_country'] ) ? esc_attr($_REQUEST['shipping_country']) : '';

					$is_country_same = ( strtoupper( $billing_country ) !== strtoupper( $shipping_country) );
					if ( ! $is_country_same && ! $is_valid ) {
						$is_valid = true;
					}
				}

				// Checks if company name autofill is enabled
				if ( 'no' !== get_option( 'alg_wc_eu_vat_advance_enable_company_name_autofill', 'no' ) ) {
					$company_name        = sanitize_text_field( alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check_company_name' ) );
					$posted_company_name = sanitize_text_field( $_posted['billing_company'] );

					// Check if the company names match and if it's not valid yet
					if ( $company_name === $posted_company_name && ! $is_valid ) {
						$is_valid = true;
					} elseif ( ! empty( $company_name ) ) {
						// If company names don't match, show an error notice
						wc_add_notice(
							str_replace( '%company_name%', esc_html( $company_name ),
								do_shortcode(
									get_option(
										'alg_wc_eu_vat_company_name_mismatch',
										__( ' VAT is valid, but registered to %company_name%.', 'eu-vat-for-woocommerce' )
									)
								)
							),
							'error'
						);
					}
				}

				$is_valid = apply_filters( 'alg_wc_eu_vat_is_valid_vat_at_checkout', $is_valid );
				if ( ! $is_valid ) {
					wc_add_notice(
						str_replace( '%eu_vat_number%', esc_attr($_posted[ alg_wc_eu_vat_get_field_id() ]),
							do_shortcode( get_option( 'alg_wc_eu_vat_not_valid_message', __( '<strong>EU VAT Number</strong> is not valid.', 'eu-vat-for-woocommerce' ) ) ) ),
						'error'
					);
					alg_wc_eu_vat_maybe_log(
						( isset( $_posted['billing_country'] ) ? esc_attr($_posted['billing_country']) : '' ),
						esc_attr($_posted[ alg_wc_eu_vat_get_field_id() ]),
						( isset( $_posted['billing_company'] ) ? esc_attr($_posted['billing_company']) : '' ),
						'',
						__( 'Error: VAT is not valid (checkout)', 'eu-vat-for-woocommerce' )
					);
				}
			}
		}
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

	/**
	 * eu_vat_wp_footer.
	 *
	 * @version 2.12.11
	 * @since   2.12.11
	 */
	function eu_vat_wp_footer() {

		if ( 'yes' === get_option( 'alg_wc_eu_vat_remove_validation_color', 'no' ) ) {
			?>
			<style>
				.form-row.woocommerce-invalid input#billing_eu_vat_number {
					box-shadow: inset 2px 0 0 transparent;
				}
			</style>
			<?php
		}

		?>
		<style>
			div.woocommerce-MyAccount-content .alg-wc-frontend-billing-edit {
				display: block !important;
			}
		</style>
		<?php

	}

}

endif;

return new Alg_WC_EU_VAT_Core();
