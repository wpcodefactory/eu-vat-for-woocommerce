<?php
/**
 * EU VAT for WooCommerce - Core Class
 *
 * @version 4.6.2
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
	 * country_locale.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	public $country_locale;

	/**
	 * checkout_block.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	public $checkout_block;

	/**
	 * vat_details_data.
	 *
	 * @version 4.2.5
	 * @since   4.2.5
	 */
	public $vat_details_data;

	/**
	 * eu_vat_response_data.
	 *
	 * @version 4.2.7
	 * @since   4.2.7
	 */
	public $eu_vat_response_data;

	/**
	 * display.
	 *
	 * @version 4.2.9
	 * @since   4.2.9
	 */
	public $display;

	/**
	 * Constructor.
	 *
	 * @version 4.5.9
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
		add_action( 'init',                                     array( $this, 'maybe_exclude_vat' ), PHP_INT_MAX );
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'maybe_exclude_vat' ), PHP_INT_MAX );
		add_action( 'woocommerce_before_calculate_totals',      array( $this, 'maybe_exclude_vat' ), 99 );
		add_action( 'woocommerce_before_checkout_billing_form', array( $this, 'maybe_exclude_vat' ), PHP_INT_MAX );

		// Block
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'maybe_vat_validation' ), 99 );

		// Validation
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'checkout_validate_vat' ), PHP_INT_MAX );

		// Default value
		add_filter( 'default_checkout_' . alg_wc_eu_vat_get_field_id(), array( $this, 'add_default_checkout_billing_eu_vat_number' ), PHP_INT_MAX, 2 );

		// Frontend; Billing fields
		if ( 'yes' !== get_option( 'alg_wc_eu_vat_hide_eu_vat', 'no' ) ) {
			add_filter( 'woocommerce_checkout_fields', array( $this, 'add_eu_vat_checkout_field_to_frontend' ), 99 );
			add_filter( 'woocommerce_billing_fields', array( $this, 'add_frontend_edit_billing_fields' ), 10 );
		}

		// Display
		$this->display = require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-display.php';

		// Show zero VAT
		if ( 'yes' === get_option( 'alg_wc_eu_vat_always_show_zero_vat', 'no' ) ) {
			add_filter( 'woocommerce_cart_tax_totals', array( $this, 'always_show_zero_vat' ), PHP_INT_MAX, 2 );
		}

		// Country locale
		$this->country_locale = require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-country-locale.php';

		// "Place order" button confirmation
		if ( 'yes' === get_option( 'alg_wc_eu_vat_field_confirmation', 'no' ) ) {
			add_filter( 'wp_enqueue_scripts', array( $this, 'add_place_order_button_confirmation_script' ) );
		}

		// CSS
		add_action( 'wp_footer', array( $this, 'eu_vat_wp_footer'), PHP_INT_MAX );

		// Keep VAT in selected countries; Keep VAT if shipping country is different from billing country
		add_filter( 'alg_wc_eu_vat_maybe_exclude_vat', array( $this, 'maybe_exclude_vat_free' ) );

		// Keep shipping VAT
		add_action( 'alg_wc_eu_vat_exempt_applied', array( $this, 'keep_shipping_vat' ) );

		// Customer meta field
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-customer-meta-field.php';

		// Orders
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-orders.php';

		// Sign-up form
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-sign-up-form.php';

		// Keep VAT for individual product
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-keep-vat-individual-product.php';

		// Checkout block
		$this->checkout_block = require_once plugin_dir_path( __FILE__ ) . 'blocks/class-alg-wc-eu-vat-checkout-block.php';

		// Compatibility
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-compatibility.php';

		// Force price display including tax
		if ( 'yes' === get_option( 'alg_wc_eu_vat_force_price_display_incl_tax', 'no' ) ) {
			add_filter( 'woocommerce_get_price_html', array( $this, 'force_price_display_incl_tax' ), PHP_INT_MAX, 2 );
		}

	}

	/**
	 * keep_shipping_vat.
	 *
	 * @version 4.3.9
	 * @since   4.3.9
	 */
	function keep_shipping_vat( $is_exempt ) {

		if ( ! $is_exempt ) {
			return;
		}

		if ( 'no' === get_option( 'alg_wc_eu_vat_keep_shipping_vat', 'no' ) ) {
			return;
		}

		if ( ! did_action( 'wp_loaded' ) ) {
			return;
		}

		// Disable VAT exemption for the customer
		WC()->customer->set_is_vat_exempt( false );

		// Set all cart product tax statuses to "None"
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product             = $cart_item['data'];
			$price_excluding_tax = wc_get_price_excluding_tax( $product );
			$product->set_price( $price_excluding_tax );
			$product->set_tax_status( 'none' );
		}

	}

	/**
	 * force_price_display_incl_tax.
	 *
	 * @version 4.2.7
	 * @since   4.2.7
	 *
	 * @todo    (dev) limit to certain pages only, e.g., `is_product()`?
	 */
	function force_price_display_incl_tax( $price_html, $product ) {
		if (
			! empty( WC()->customer ) &&
			WC()->customer->get_is_vat_exempt()
		) {
			WC()->customer->set_is_vat_exempt( false );
			remove_filter( 'woocommerce_get_price_html', array( $this, 'force_price_display_incl_tax' ), PHP_INT_MAX );
			$price_html = $product->get_price_html();
			add_filter( 'woocommerce_get_price_html', array( $this, 'force_price_display_incl_tax' ), PHP_INT_MAX, 2 );
			WC()->customer->set_is_vat_exempt( true );
		}
		return $price_html;
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
	 * maybe_exclude_vat_free.
	 *
	 * @version 4.5.5
	 * @since   1.7.0
	 */
	function maybe_exclude_vat_free( $value ) {
		$preserve_base_country_check_passed = true;

		// Keep VAT in selected countries
		$preserve_option_value = get_option( 'alg_wc_eu_vat_preserve_in_base_country', 'no' );
		if ( 'no' !== $preserve_option_value ) {
			$selected_country = substr(
				alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' ),
				0,
				2
			);

			if ( ! ctype_alpha( $selected_country ) ) {
				$selected_country = '';
				if ( 'yes' === get_option( 'alg_wc_eu_vat_allow_without_country_code', 'no' ) ) {
					// Getting country from POST, or from the customer object
					if ( ! ctype_alpha( $selected_country ) ) {
						$selected_country = WC()->checkout()->get_value( 'billing_country' );
					}
					// Fallback #1
					if (
						! ctype_alpha( $selected_country ) &&
						! empty( $_REQUEST['post_data'] )
					) {
						parse_str( wp_unslash( $_REQUEST['post_data'] ), $post_data_args );
						if ( ! empty( $post_data_args['billing_country'] ) ) {
							$selected_country = sanitize_text_field( $post_data_args['billing_country'] );
						}
					}
					// Fallback #2
					if (
						! ctype_alpha( $selected_country ) &&
						! empty( $_REQUEST['billing_country'] )
					) {
						$selected_country = sanitize_text_field( wp_unslash( $_REQUEST['billing_country'] ) );
					}
					// Fallback #3
					if (
						! ctype_alpha( $selected_country ) &&
						! empty( $_REQUEST['country'] )
					) {
						$selected_country = sanitize_text_field( wp_unslash( $_REQUEST['country'] ) );
					}
				}
				if ( ! ctype_alpha( $selected_country ) ) {
					return false;
				}
			}

			$country_type = get_option( 'alg_wc_eu_vat_preserve_country_type', 'billing_country' );
			$selected_country_at_checkout = WC()->checkout()->get_value( $country_type );

			// AJAX checkout update fallback
			if ( ! empty( $_REQUEST['post_data'] ) ) {
				parse_str( wp_unslash( $_REQUEST['post_data'] ), $post_data_args );

				if ( ! empty( $post_data_args[ $country_type ] ) ) {
					$selected_country_at_checkout = sanitize_text_field( $post_data_args[ $country_type ] );
				}
			}

			if ( 'yes' === $preserve_option_value ) {
				$location = wc_get_base_location();
				if ( empty( $location['country'] ) ) {
					$location = wc_format_country_state_string(
						apply_filters(
							'woocommerce_customer_default_location',
							get_option( 'woocommerce_default_country' )
						)
					);
				}
				$preserve_base_country_check_passed = ( strtoupper( $location['country'] ) !== $selected_country_at_checkout );
			} elseif ( '' != get_option( 'alg_wc_eu_vat_preserve_in_base_country_locations', '' ) ) { // `list`
				$locations = array_map(
					'strtoupper',
					array_map(
						'trim',
						explode( ',', get_option( 'alg_wc_eu_vat_preserve_in_base_country_locations', '' ) )
					)
				);
				$preserve_base_country_check_passed = ( ! in_array( $selected_country_at_checkout, $locations ) );
			}
		}

		// Keep VAT if shipping country is different from billing country
		if (
			! $preserve_base_country_check_passed &&
			'no' !== get_option( 'alg_wc_eu_vat_preserv_vat_for_different_shipping', 'no' )
		) {

			$billing_country  = (
				isset( $_REQUEST['billing_country'] ) ?
				sanitize_text_field( wp_unslash( $_REQUEST['billing_country'] ) ) :
				''
			);
			$shipping_country = (
				isset( $_REQUEST['shipping_country'] ) ?
				sanitize_text_field( wp_unslash( $_REQUEST['shipping_country'] ) ) :
				''
			);

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
	 * @version 4.1.0
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
						'http://'  . sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) ) :
						'https://' . sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) )
					);
				} else {
					$current_url = 'http://' . sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
				}

				$current_url .= (
					$_SERVER["SERVER_PORT"] != 80 && $_SERVER["SERVER_PORT"] != 443 ?
					":" . sanitize_text_field( wp_unslash( $_SERVER["SERVER_PORT"] ) ) :
					""
				);
				$current_url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );

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
	 * @version 4.6.0
	 * @since   1.4.1
	 */
	function add_place_order_button_confirmation_script() {

		if ( alg_wc_eu_vat_is_checkout() ) {

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
					'vatField'          => alg_wc_eu_vat_get_field_id(),
					'buttonSelector'    => get_option( 'alg_wc_eu_vat_field_confirmation_extra_buttons', '' ),
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
	 * always_show_zero_vat.
	 *
	 * @version 4.2.5
	 * @since   1.4.0
	 *
	 * @todo    (dev) remove `$zero_tax->amount`, `$zero_tax->tax_rate_id`, `$zero_tax->is_compound` (as they are not really used in `review-order` template)?
	 */
	function always_show_zero_vat( $tax_totals, $cart ) {
		if (
			empty( $tax_totals ) &&
			alg_wc_eu_vat_is_checkout()
		) {

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
	 * get_valid_vat_but_not_exempted_field_data.
	 *
	 * @version 4.4.0
	 * @since   1.3.0
	 *
	 * @todo    (dev) rethink `$is_required` (check filters: `woocommerce_default_address_fields`, `woocommerce_billing_fields`)
	 * @todo    (dev) `default`?
	 * @todo    (dev) `autocomplete`?
	 * @todo    (dev) `value`?
	 */
	function get_valid_vat_but_not_exempted_field_data() {
		return array(
			'type'      => 'checkbox',
			'label'     => do_shortcode(
				get_option(
					'alg_wc_eu_vat_belgium_compatibility_label',
					__( 'I have a valid VAT but not exempted', 'eu-vat-for-woocommerce' )
				)
			),
			'class'     => array( 'form-row-wide' ),
			'priority'  => get_option( 'alg_wc_eu_vat_field_priority', 200 ),
		);
	}

	/**
	 * get_field_data.
	 *
	 * @version 4.4.0
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

			if ( '' != $this->country_locale->required_in_countries ) {
				$required_eu_vat_field_countries = array_map( 'strtoupper', array_map( 'trim', explode( ',', $this->country_locale->required_in_countries ) ) );
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

			// Required if customer fills the company field
			if ( 'yes_for_company' === $eu_vat_required ) {
				if ( ! empty( WC()->checkout()->get_value( 'billing_company' ) ) ) {
					$is_required = true;
				}
				$is_required = false;
			}

			// Let Customer Decide
			if ( $is_required && 'yes' === get_option( 'alg_wc_eu_vat_field_let_customer_decide', 'no' ) ) {
				$field_id = alg_wc_eu_vat_get_field_id();
				if (
					isset( $_POST[ $field_id . '_customer_decide' ] ) &&
					1 == $_POST[ $field_id . '_customer_decide' ]
				) {
					$is_required = false;
				}
			}

		}

		return apply_filters( 'alg_wc_eu_vat_get_field_data', array(
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
		) );
	}

	/**
	 * is_tax_status_none.
	 *
	 * @version 4.2.7
	 * @since   2.9.18
	 */
	function is_tax_status_none(){
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
		return false;
	}

	/**
	 * add_eu_vat_checkout_field_to_frontend.
	 *
	 * @version 4.4.0
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
			$fields['billing'][ alg_wc_eu_vat_get_field_id() . '_valid_vat_but_not_exempted' ] = $this->get_valid_vat_but_not_exempted_field_data();
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
	 * start_session.
	 *
	 * @version 4.1.0
	 * @since   1.0.0
	 */
	function start_session() {
		$curl = rtrim( $this->current_url(), '/' );
		$home = rtrim( home_url(), '/' );
		if ( ! ( $curl == $home ) ) {

			$checkout_page_url = rtrim( wc_get_checkout_url(), '/' );
			$cart_url          = rtrim( get_permalink( wc_get_page_id( 'cart' ) ), '/' );

			if ( $curl == $cart_url || $curl == $checkout_page_url ) {
				alg_wc_eu_vat_session_start();

				$args = array();
				if ( isset( $_POST['post_data'] ) ) {
					parse_str( sanitize_text_field( wp_unslash( $_POST['post_data'] ) ), $args );
					if (
						isset( $args[ alg_wc_eu_vat_get_field_id() ] ) &&
						alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' ) != $args[ alg_wc_eu_vat_get_field_id() ]
					) {
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid',                 null );
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check',              null );
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check_company',      null );
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check_company_name', null );
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid_before_preserve', null );
					}
				}
			}
		} else {
			if ( 'yes' === get_option( 'alg_wc_eu_vat_sitepress_optimizer_dynamic_caching', 'no' ) ) {
				if ( 'test-url-cache' == $curl ) {
					$return = array(
						'status'  => 200,
						'data'    => array(),
						'message' => 'La URL está en la caché',
					);
					wp_send_json( $return );
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
	 * @version 4.2.5
	 * @since   1.7.0
	 */
	function is_cart_or_checkout_or_ajax() {
		return (
			alg_wc_eu_vat_is_checkout() ||
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
	 * @version 4.5.0
	 * @since   1.7.1
	 *
	 * @todo    (dev) use in `Alg_WC_EU_VAT_AJAX::alg_wc_eu_vat_validate_action()`
	 */
	function check_and_save_eu_vat( $eu_vat_to_check, $billing_country, $billing_company ) {
		$eu_vat_number = alg_wc_eu_vat_parse_vat( $eu_vat_to_check, $billing_country );

		if ( 'yes' === apply_filters( 'alg_wc_eu_vat_check_ip_location_country', 'no' ) ) {
			$country_by_ip    = alg_wc_eu_vat_get_customers_location_by_ip();
			$is_country_valid = ( $country_by_ip === $eu_vat_number['country'] );
			$is_valid         = (
				$is_country_valid ?
				alg_wc_eu_vat_validate_vat(
					$eu_vat_number['country'],
					$eu_vat_number['number'],
					$billing_company
				) :
				false
			);
			if ( ! $is_country_valid ) {
				alg_wc_eu_vat_log(
					$eu_vat_number['country'],
					$eu_vat_number['number'],
					$billing_company,
					'',
					sprintf(
						/* Translators: %s: Country code. */
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
		$is_valid = (
			true === alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_valid' ) &&
			null !== alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' )
		);
		return $is_valid;
	}

	/**
	 * maybe_exclude_vat.
	 *
	 * @version 4.2.5
	 * @since   1.0.0
	 *
	 * @todo    (dev) remove `alg_wc_eu_vat_i_am_company`?
	 * @todo    (dev) remove `alg_wc_eu_vat_is_checkout()` check?
	 * @todo    (fix) mini cart!
	 */
	function maybe_exclude_vat() {

		if (
			empty( WC()->customer ) ||
			! $this->is_cart_or_checkout_or_ajax()
		) {
			return;
		}

		// Is exempt
		if (
			$this->check_current_user_roles(
				get_option( 'alg_wc_eu_vat_exempt_for_user_roles', array() )
			)
		) {

			$is_exempt = true;

		} elseif (
			$this->check_current_user_roles(
				get_option( 'alg_wc_eu_vat_not_exempt_for_user_roles', array() )
			)
		) {

			$is_exempt = false;

		} elseif (
			$this->is_validate_and_exempt() &&
			$this->is_valid_and_exists()
		) {

			$is_exempt = apply_filters( 'alg_wc_eu_vat_maybe_exclude_vat', true );

		} else {

			$is_exempt = false;

		}

		// Force validate on cart and checkout page load/reload
		if ( 'yes' === get_option( 'alg_wc_eu_vat_validate_force_page_reload', 'no' ) ) {
			if (
				(
					alg_wc_eu_vat_is_checkout() ||
					is_cart()
				) &&
				! $is_exempt
			) {
				$billing_eu_vat_number = WC()->customer->get_meta( 'billing_eu_vat_number' );
				$billing_country       = WC()->customer->get_meta( 'billing_country' );
				$billing_company       = WC()->customer->get_meta( 'billing_company' );
				if ( ! empty( $billing_eu_vat_number ) ) {
					$is_valid = $this->check_and_save_eu_vat(
						$billing_eu_vat_number,
						$billing_country,
						$billing_company
					);
					if ( $is_valid ) {
						$is_exempt = apply_filters( 'alg_wc_eu_vat_maybe_exclude_vat', true );
					}
				}
			}
		}

		// `alg_wc_eu_vat_i_am_company`
		if ( true === alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_i_am_company' ) ) {
			$is_exempt = true;
		}

		// Set customer "is vat exempt"
		WC()->customer->set_is_vat_exempt( $is_exempt );

		// `alg_wc_eu_vat_exempt_applied` action
		do_action( 'alg_wc_eu_vat_exempt_applied', $is_exempt );

	}

	/**
	 * checkout_validate_vat.
	 *
	 * @version 4.5.3
	 * @since   1.0.0
	 *
	 * @todo    (dev) simplify the code!
	 */
	function checkout_validate_vat( $_posted ) {

		$field_id         = alg_wc_eu_vat_get_field_id();
		$vat_number       = (
			isset( $_posted[ $field_id ] ) ?
			sanitize_text_field( wp_unslash( $_posted[ $field_id ] ) ) :
			''
		);
		$billing_country  = (
			isset( $_posted['billing_country'] ) ?
			strtoupper( sanitize_text_field( wp_unslash( $_posted['billing_country'] ) ) ) :
			''
		);
		$shipping_country = (
			isset( $_REQUEST['shipping_country'] ) ?
			strtoupper( sanitize_text_field( wp_unslash( $_REQUEST['shipping_country'] ) ) ) :
			''
		);
		$billing_company  = (
			isset( $_posted['billing_company'] ) ?
			sanitize_text_field( wp_unslash( $_posted['billing_company'] ) ) :
			''
		);
		$is_required      = ( 'yes' === get_option( 'alg_wc_eu_vat_field_required', 'no' ) );
		$eu_vat_required  = get_option( 'alg_wc_eu_vat_field_required', 'no' );

		// 1. Let customer decide to skip
		if (
			$is_required &&
			'yes' === get_option( 'alg_wc_eu_vat_field_let_customer_decide', 'no' )
		) {
			if (
				isset( $_posted[ $field_id . '_customer_decide' ] ) &&
				1 == $_posted[ $field_id . '_customer_decide' ]
			) {
				return;
			}
		}

		// 2. Belgium compatibility: valid VAT but not exempt
		if ( 'yes' === get_option( 'alg_wc_eu_vat_belgium_compatibility', 'no' ) ) {
			if (
				isset( $_posted[ $field_id . '_valid_vat_but_not_exempted' ] ) &&
				1 == $_posted[ $field_id . '_valid_vat_but_not_exempted' ]
			) {
				alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid', false );
				alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', null );

				return;
			}
		}

		// 3. If VAT is required for company but not filled
		if (
			'yes_for_company' === $eu_vat_required &&
			! empty( $billing_company ) &&
			empty( $vat_number )
		) {

			$show_eu_vat_field_countries = (
				! empty( $this->country_locale->show_in_countries ) ?
				array_map(
					'strtoupper',
					array_map( 'trim', explode( ',', $this->country_locale->show_in_countries ) )
				) :
				array()
			);

			$is_valid = (
				! empty( $show_eu_vat_field_countries ) &&
				! in_array( $billing_country, $show_eu_vat_field_countries, true )
			);

			if ( ! $is_valid ) {
				$this->add_vat_error_notice( $vat_number );
			}

			return;
		}

		// 4. Validate VAT number
		if ( 'yes' !== get_option( 'alg_wc_eu_vat_validate', 'yes' ) ) {
			return;
		}

		$valid_vat_number = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' );
		$is_valid         = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_valid' );

		if (
			'' != $vat_number &&
			(
				null === $is_valid ||
				false === $is_valid ||
				null === $valid_vat_number ||
				$vat_number != $valid_vat_number
			)
		) {

			$is_valid = false;

			if (
				'yes' === get_option( 'alg_wc_eu_vat_force_checkout_recheck', 'no' ) &&
				$vat_number != $valid_vat_number
			) {
				$is_valid = $this->check_and_save_eu_vat(
					$vat_number,
					$billing_country,
					$billing_company,
				);
			} else {
				$vat_number    = preg_replace( '/\s+/', '', $vat_number );
				$eu_vat_number = alg_wc_eu_vat_parse_vat( $vat_number, $billing_country );

				// VAT validate manually pre-saved number
				if ( 'yes' === get_option( 'alg_wc_eu_vat_manual_validation_enable', 'no' ) ) {

					$manual_validation_vat_numbers = get_option( 'alg_wc_eu_vat_manual_validation_vat_numbers', '' );
					if ( '' != $manual_validation_vat_numbers ) {

						$prevalidated_VAT_numbers = explode( ',', $manual_validation_vat_numbers );
						$sanitized_vat_numbers    = array_map( 'trim', $prevalidated_VAT_numbers );

						$conjuncted_vat_number = $billing_country . $eu_vat_number['number'];
						if ( isset( $sanitized_vat_numbers[0] ) ) {
							if ( in_array( $conjuncted_vat_number, $sanitized_vat_numbers ) ) {
								alg_wc_eu_vat_log(
									esc_attr( $eu_vat_number['country'] ),
									esc_attr( $eu_vat_number['number'] ),
									esc_attr( $billing_company ),
									'',
									__( 'Success (checkout): VAT ID valid. Matched with prevalidated VAT numbers.', 'eu-vat-for-woocommerce' )
								);
								$is_valid = true;
							}
						}
					}
				}
			}

			if ( 'no' != get_option( 'alg_wc_eu_vat_preserv_vat_for_different_shipping', 'no' ) ) {
				$is_country_same = ( $billing_country !== $shipping_country );
				if ( $is_country_same && ! $is_valid ) {
					$is_valid = true;
				}
			}

			if ( 'no' != get_option( 'alg_wc_eu_vat_preserve_vat_for_base_country_shipping', 'no' ) ) {
				$store_base_country = WC()->countries->get_base_country();

				$is_country_same = ( strtoupper( $store_base_country ) === $shipping_country );

				if ( $is_country_same && ! $is_valid ) {
					$is_valid = true;
				}
			}

			// Checks if company name autofill is enabled
			if ( 'no' !== get_option( 'alg_wc_eu_vat_advance_enable_company_name_autofill', 'no' ) ) {
				$company_name = sanitize_text_field(
					alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check_company_name' )
				);

				// Check if the company names match and if it's not valid yet
				if ( $company_name === $billing_company && ! $is_valid ) {
					$is_valid = true;
				} elseif ( ! empty( $company_name ) ) {
					// If company names don't match, show an error notice
					wc_add_notice(
						str_replace(
							'%company_name%',
							esc_html( $company_name ),
							do_shortcode(
								get_option(
									'alg_wc_eu_vat_company_name_mismatch',
									__( 'VAT is valid, but registered to %company_name%.', 'eu-vat-for-woocommerce' ) // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
								)
							)
						),
						'error'
					);
				}
			}

			$is_valid = apply_filters( 'alg_wc_eu_vat_is_valid_vat_at_checkout', $is_valid );

			if ( ! $is_valid ) {
				$this->add_vat_error_notice( $vat_number );

				alg_wc_eu_vat_log(
					esc_attr( $billing_country ),
					esc_attr( $vat_number ),
					esc_attr( $billing_company ),
					'',
					__( 'Error: VAT is not valid (checkout)', 'eu-vat-for-woocommerce' )
				);
			}

		} else if (
			WC()->customer &&
			WC()->customer->get_is_vat_exempt() &&
			$vat_number !== $valid_vat_number
		) {
			$this->add_vat_error_notice( $vat_number );
		}
	}

	/**
	 * Adds VAT error notice.
	 *
	 * @version 4.5.0
	 * @since   4.5.0
	 */
	function add_vat_error_notice($vat_number) {
		wc_add_notice(
			str_replace(
				'%eu_vat_number%',
				esc_attr($vat_number),
				do_shortcode(
					get_option(
						'alg_wc_eu_vat_not_valid_message',
						__("<strong>EU VAT Number</strong> is not valid.", 'eu-vat-for-woocommerce')
					)
				)
			),
			'error'
		);
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

	/**
	 * vat_required
	 *
	 * @version 4.5.9
	 * @since   4.5.9
	 */
	function vat_required( $billing_country = '', $billing_company = '' ) {

		$setting = get_option( 'alg_wc_eu_vat_field_required', 'no' );

		switch ( $setting ) {

			case 'yes':
				return true;

			case 'yes_for_company':
				return ! empty( $billing_company );

			case 'yes_for_countries':
			case 'no_for_countries':

				$countries = get_option( 'alg_wc_eu_vat_field_required_countries', array() );

				$in_list = in_array( $billing_country, $countries, true );

				return (
					( 'yes_for_countries' === $setting && $in_list ) ||
					( 'no_for_countries' === $setting && ! $in_list )
				);

			case 'no':
			default:
				return false;
		}
	}

	/**
	 * parse_vat.
	 *
	 * @version 4.5.9
	 * @since   4.5.9
	 */
	function parse_vat( $vat_number, $billing_country ) {

		// Remove non-alphanumeric (spaces, dots and hyphens) symbols
		if ( 'yes' === get_option( 'alg_wc_eu_vat_allow_non_alphanumeric', 'no' ) ) {
			$vat_number = str_replace( [ '-', '.', ' ' ], '', $vat_number );
		}

		// Only letters and digits
		if ( ! preg_match( '/^[a-zA-Z0-9]+$/', $vat_number ) ) {
			return array(
				'country' => '',
				'number'  => $vat_number
			);
		}

		$vat_number        = strtoupper( $vat_number );
		$billing_country   = strtoupper( $billing_country );
		$extracted_country = alg_wc_eu_vat_extract_country( $vat_number );
		$country           = '';
		if ( false !== $extracted_country ) {
			$country = $extracted_country;
			$number  = substr( $vat_number, strlen( $country ) );
		} elseif ( 'yes' === get_option( 'alg_wc_eu_vat_allow_without_country_code', 'no' ) ) {
			$country = alg_wc_eu_vat_match_vat_id_country( $billing_country );
			$number  = $vat_number;
		} else {
			$number = $vat_number;
		}

		return array(
			'country' => $country,
			'number'  => $number
		);
	}

	/**
	 * vat_validation.
	 *
	 * @version 4.6.2
	 * @since   4.5.9
	 */
	function vat_validation( $data ) {

		if ( ! $this->is_validate_and_exempt() ) {
			return false;
		}

		$data = wp_unslash( $data );

		$vat_number       = sanitize_text_field( $data['vat_number'] ?? '' );
		$billing_country  = strtoupper( sanitize_text_field( $data['billing_country'] ?? '' ) );
		$shipping_country = strtoupper( sanitize_text_field( $data['shipping_country'] ?? '' ) );
		$billing_company  = strtolower( sanitize_text_field( $data['billing_company'] ?? '' ) );

		$store_base_country = strtoupper( wc_get_base_location()['country'] );

		// Check if VAT is required
		$is_required = $this->vat_required( $billing_country, $billing_company );
		if ( ! $is_required && empty( $vat_number ) ) {
			$wc_customer = WC()->customer;
			$wc_customer->set_is_vat_exempt( false );

			$result = array(
				'is_validate'   => true,
				'is_vat_exempt' => false,
			);

			alg_wc_eu_vat_session_set( 'alg_eu_vat_validation', $result );

			return $result;
		}

		//  Let customer decide to skip
		if (
			$is_required &&
			'yes' === get_option( 'alg_wc_eu_vat_field_let_customer_decide', 'no' ) &&
			wc_string_to_bool( $data['vat_customer_decide'] ?? false )
		) {
			$wc_customer = WC()->customer;
			$wc_customer->set_is_vat_exempt( false );

			$result = array(
				'is_validate'   => true,
				'is_vat_exempt' => false,
			);

			alg_wc_eu_vat_session_set( 'alg_eu_vat_validation', $result );

			return $result;
		}

		$is_validate   = false;
		$is_vat_exempt = false;
		$is_vat_valid  = false;
		$messages      = '';
		$css_class     = '';

		// Basic VAT number format check
		$vat_length = strlen( $vat_number );
		if ( ! empty( $vat_number ) && ( $vat_length < 4 || $vat_length > 20 ) ) {
			$css_class = 'alg-wc-vat-invalid-format';
			$messages  = __( 'Invalid VAT format.', 'eu-vat-for-woocommerce' );
		}

		// VAT is required
		if ( $is_required && empty( $vat_number ) ) {
			$css_class = 'alg-wc-vat-required';
			$messages  = do_shortcode( get_option(
				'alg_wc_eu_vat_progress_text_is_required',
				__( 'VAT is required.', 'eu-vat-for-woocommerce' )
			) );
		}

		// Parse VAT number
		$parse_vat_number = $this->parse_vat( $vat_number, $billing_country );
		$parse_country    = sanitize_text_field( $parse_vat_number['country'] );
		$parse_number     = sanitize_text_field( $parse_vat_number['number'] );


		// Skip VAT validation for selected countries
		$skip_countries = get_option( 'alg_wc_eu_vat_advanced_skip_countries', '' );
		if ( ! empty( $skip_countries ) ) {
			$skip_countries = array_map(
				'strtoupper',
				array_map( 'trim', explode( ',', $skip_countries ) )
			);
			if ( in_array( $parse_country, $skip_countries, true ) ) {
				$is_validate   = true;
				$is_vat_exempt = true;
			}
		}

		// VAT validate manually pre-saved number
		if ( 'yes' === get_option( 'alg_wc_eu_vat_manual_validation_enable', 'no' ) ) {
			$manual_validation_vat_numbers = get_option( 'alg_wc_eu_vat_manual_validation_vat_numbers', '' );
			if ( ! empty( $manual_validation_vat_numbers ) ) {
				$sanitized_vat_numbers = array_map(
					'strtoupper',
					array_map( 'trim', explode( ',', $manual_validation_vat_numbers ) )
				);
				$concatenated_vat_number = $parse_country . $parse_number;

				if ( in_array( $concatenated_vat_number, $sanitized_vat_numbers ) ) {
					alg_wc_eu_vat_log(
						$parse_country,
						$parse_number,
						$billing_company,
						'',
						__(
							'Success: VAT ID valid. Matched with prevalidated VAT numbers.',
							'eu-vat-for-woocommerce'
						)
					);

					alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_details', null );
					$css_class     = 'alg-wc-vat-prevalidate';
					$is_vat_exempt = true;
					$is_vat_valid  = true;
				}
			}
		}

		if ( ! $is_validate && empty( $messages) ) {

			// Check VAT validate for manually pre-saved number first
			if ( ! $is_vat_valid ) {
				// Validate VAT
				$cached_is_valid   = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_valid' );
				$cached_vat_number = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_checked' );
				$cached_country    = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check_country' );

				$cache_is_fresh = (
					true === $cached_is_valid &&
					$cached_vat_number === $vat_number &&
					$cached_country === $billing_country
				);

				if ( $cache_is_fresh ) {
					$is_vat_valid = $cached_is_valid;
				} else {
					// Vat validating
					$is_vat_valid = alg_wc_eu_vat_validate_vat(
						$parse_country,
						$parse_number,
						$billing_company
					);

					if ( $is_vat_valid ) {
						$is_vat_valid = apply_filters( 'alg_wc_eu_vat_is_valid_vat_at_checkout', $is_vat_valid );

						// Update cache
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_checked', $vat_number );
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid', $is_vat_valid );
						alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check_country', $billing_country );
					}
				}
			}

			$is_validate   = $is_vat_valid;
			$is_vat_exempt = $is_vat_valid;

			// VIES not available
			if (
				! $is_validate &&
				null !== alg_wc_eu_vat()->core->get_error_vies_unavailable()
			) {
				$is_validate   = false;
				$is_vat_exempt = true;
				$css_class     = 'alg-wc-eu-vat-validation-failed';

				$vies_error = esc_html( alg_wc_eu_vat()->core->get_error_vies_unavailable() );

				$messages = str_replace(
					'%vies_error%',
					$vies_error,
					do_shortcode(
						get_option(
							'alg_wc_eu_vat_progress_text_validation_vies_error',
							__( 'VAT accepted due to VIES error: %vies_error%. The admin will check the VAT validation again and proceed accordingly.', 'eu-vat-for-woocommerce' )
						)
					)
				);
			}

			if ( $is_validate ) {

				// Check country by IP
				if ( 'yes' === apply_filters( 'alg_wc_eu_vat_check_ip_location_country', 'no' ) ) {
					$country_by_ip = alg_wc_eu_vat_get_customers_location_by_ip();

					if ( $country_by_ip !== $parse_country ) {
						$is_validate    = false;
						$is_vat_exempt  = false;
						$css_class     .= ' alg-wc-vat-ip-not-match';
						alg_wc_eu_vat_log(
							$parse_country,
							$parse_number,
							$billing_company,
							'',
							sprintf(
								/* Translators: %s: Country code. */
								__( 'Error: Country by IP does not match (%s)', 'eu-vat-for-woocommerce' ),
								$country_by_ip
							)
						);
					}
				}

				// Check for matching billing country code
				if (
					'no' !== get_option( 'alg_wc_eu_vat_check_billing_country_code', 'no' ) &&
					alg_wc_eu_vat_match_billing_country( $parse_country ) !== $billing_country
				) {
					$is_validate    = false;
					$is_vat_exempt  = false;
					$css_class     .= ' alg-wc-eu-vat-not-valid-billing-country';
					$messages       = do_shortcode(
						get_option(
							'alg_wc_eu_vat_wrong_billing_country',
							__( 'Wrong billing country.', 'eu-vat-for-woocommerce' )
						)
					);

					// Wrong billing country
					alg_wc_eu_vat_log(
						$parse_country,
						$vat_number,
						$billing_country,
						'',
						sprintf(
							/* Translators: %s: Billing country. */
							__( 'Error: Country code does not match (%s)', 'eu-vat-for-woocommerce' ),
							$billing_country
						)
					);
				}

				// Keep VAT if shipping country is different from billing country
				if (
					'no' != get_option( 'alg_wc_eu_vat_preserv_vat_for_different_shipping', 'no' ) &&
					! empty( $shipping_country ) &&
					$shipping_country !== $billing_country
				) {
					$is_validate    = true;
					$is_vat_exempt  = false;
					$css_class     .= ' alg-wc-eu-vat-not-valid-billing-country';
					$messages       = do_shortcode(
						get_option(
							'alg_wc_eu_vat_shipping_billing_countries',
							__( 'Different shipping & billing countries.', 'eu-vat-for-woocommerce' )
						)
					);
				}

				// Keep VAT if shipping country matches store base country
				if (
					'no' != get_option( 'alg_wc_eu_vat_preserve_vat_for_base_country_shipping', 'no' ) &&
					$shipping_country !== $billing_country &&
					$store_base_country === $shipping_country
				) {
					$is_validate    = true;
					$is_vat_exempt  = false;
					$css_class     .= 'alg-wc-eu-vat-not-valid-base-country-shipping';
					$messages       = do_shortcode(
						get_option(
							'alg_wc_eu_vat_shipping_matches_base_country',
							__( 'Shipping country matches store base country.', 'eu-vat-for-woocommerce' )
						)
					);
				}

				// Keep VAT in selected countries
				$keep_vat_selected_countries = get_option( 'alg_wc_eu_vat_preserve_in_base_country', 'no' );
				if ( 'no' !== $keep_vat_selected_countries ) {

					$allowed_country_types = array( 'billing_country', 'shipping_country' );
					$country_type_option   = get_option( 'alg_wc_eu_vat_preserve_country_type', 'billing_country' );
					$country_type          = in_array( $country_type_option, $allowed_country_types, true ) ?
						$country_type_option :
						'billing_country';
					$country_to_check      = ( $country_type === 'shipping_country' ) ?
						$shipping_country :
						$billing_country;

					$preserve_message = do_shortcode(
						(
							'yes' === get_option( 'alg_wc_eu_vat_validate_enable_preserve_message', 'no' ) ?
							get_option(
								'alg_wc_eu_vat_progress_text_validation_preserv',
								__( 'VAT preserved for this billing country', 'eu-vat-for-woocommerce' )
							) :
							''
						)
					);

					// Base (i.e., store) country (yes)
					if (
						'yes' === $keep_vat_selected_countries &&
						$country_to_check === $store_base_country
					) {
						$is_validate    = true;
						$is_vat_exempt  = false;
						$css_class     .= ' alg-wc-eu-vat-not-valid-keep-base-country';
						$messages       = $preserve_message;
					}

					// Comma separated list (list)
					if ( 'list' === $keep_vat_selected_countries ) {
						$locations_raw = get_option( 'alg_wc_eu_vat_preserve_in_base_country_locations', '' );
						if ( ! empty( $locations_raw ) ) {
							$locations = array_map( 'strtoupper', wp_parse_list( $locations_raw ) );
							if ( in_array( $country_to_check, $locations, true ) ) {
								$is_validate    = true;
								$is_vat_exempt  = false;
								$css_class     .= ' alg-wc-eu-vat-not-valid-keep-selected-country';
								$messages       = $preserve_message;
							}
						}
					}
				}

				// User Roles
				if (
					$this->check_current_user_roles(
						get_option( 'alg_wc_eu_vat_exempt_for_user_roles', array() ) // Always exempt VAT for selected user roles
					)
				) {
					$is_validate    = true;
					$is_vat_exempt  = true;
					$css_class     .= ' alg-wc-vat-selected-user-roles alg-wc-vat-always-vat';
				} elseif (
					$this->check_current_user_roles(
						get_option( 'alg_wc_eu_vat_not_exempt_for_user_roles', array() ) // Always not exempt VAT for selected user roles
					)
				) {
					$is_validate    = true;
					$is_vat_exempt  = false;
					$css_class     .= ' alg-wc-vat-selected-user-roles alg-wc-vat-always-not-vat';
				}

				// Company name mismatch check
				$valid_company_name = sanitize_text_field(
					alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check_company_name' )
				);
				if ( 'no' !== apply_filters( 'alg_wc_eu_vat_check_company_name', 'no' ) ) {
					$skip_empty_response =
						'no' !== get_option( 'alg_wc_eu_vat_check_company_name_accept_empty_response', 'no' ) &&
						'---' === $valid_company_name;

					if (
						! $skip_empty_response &&
						$this->normalize_string( $billing_company ) !== $this->normalize_string( $valid_company_name )
					) {
						$css_class .= ' alg-wc-eu-vat-not-valid-company-mismatch';
						$messages   = str_replace(
							'%company_name%',
							esc_html( $valid_company_name ),
							do_shortcode( get_option(
								'alg_wc_eu_vat_company_name_mismatch',
								__( 'VAT is valid, but registered to %company_name%.', 'eu-vat-for-woocommerce' )
							) )
						);
					}
				}

				// Belgium compatibility: valid VAT but not exempt
				if (
					'yes' === get_option( 'alg_wc_eu_vat_belgium_compatibility', 'no' ) &&
					wc_string_to_bool( $data['vat_valid_but_not_exempted'] ?? false )
				) {
					$is_validate    = true;
					$is_vat_exempt  = false;
				}
			}
		}

		// Set WooCommerce tax exemption
		$wc_customer = WC()->customer;
		if ( $is_vat_exempt ) {
			$wc_customer->set_is_vat_exempt( true );
		} else {
			$wc_customer->set_is_vat_exempt( false );
		}

		// `alg_wc_eu_vat_exempt_applied` action
		do_action( 'alg_wc_eu_vat_exempt_applied', $is_vat_exempt );


		if ( $is_validate && empty( $messages ) ) {
			$css_class = 'alg-wc-eu-vat-valid';
			$messages  = do_shortcode( get_option(
				'alg_wc_eu_vat_progress_text_valid',
				__( 'VAT is valid.', 'eu-vat-for-woocommerce' )
			) );
		} else if( ! $is_validate && empty( $messages ) ){
			$css_class = 'alg-wc-eu-vat-not-valid';
			$messages  = do_shortcode( get_option(
				'alg_wc_eu_vat_progress_text_not_valid',
				__( 'VAT is not valid.', 'eu-vat-for-woocommerce' )
			) );
		}

		$result = array(
			'messages'      => $messages,
			'is_validate'   => $is_validate,
			'css_class'     => $css_class,
			'is_vat_valid'  => $is_vat_valid ?? '',
			'is_vat_exempt' => $is_vat_exempt,
			'company'       => $is_vat_valid ?
				alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_return_company_name' ) :
				'',
			'vat_details'   => $is_vat_valid ?
				alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_details' ) :
				"",
		);

		alg_wc_eu_vat_session_set( 'alg_eu_vat_validation', $result );

		return $result;
	}

	/**
	 * maybe_vat_validation.
	 *
	 * @version 4.6.2
	 * @since   4.5.9
	 */
	function maybe_vat_validation() {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( empty( WC()->customer ) ) {
			return;
		}

		// Run only for Store API (block checkout/cart)
		if ( ! WC()->is_rest_api_request() ) {
			return;
		}

		$customer = WC()->customer;

		$vat_number                 = WC()->session->get( 'alg_wc_eu_vat' );
		$vat_customer_decide        = WC()->session->get( 'alg_wc_eu_vat_customer_decide' );
		$vat_valid_but_not_exempted = WC()->session->get( 'alg_wc_eu_vat_valid_but_not_exempted' );
		$billing_country            = $customer->get_billing_country();
		$billing_company            = $customer->get_billing_company();
		$shipping_country           = $customer->get_shipping_country();

		$data = array(
			'vat_number'                 => $vat_number,
			'vat_customer_decide'        => $vat_customer_decide,
			'vat_valid_but_not_exempted' => $vat_valid_but_not_exempted,
			'billing_country'            => $billing_country,
			'shipping_country'           => $shipping_country,
			'billing_company'            => $billing_company,
		);

		alg_wc_eu_vat()->core->vat_validation( $data );
	}


	/**
	 * normalize_string.
	 *
	 * @version 4.5.9
	 * @since   4.5.9
	 */
	private function normalize_string( $value ) {
		$value = wp_specialchars_decode( $value, ENT_QUOTES );
		$value = strtolower( $value );
		$value = preg_replace( '/[^a-z0-9\-_]/', '', $value );

		return trim( $value );
	}

	/**
	 * vat_error_message.
	 *
	 * @version 4.5.9
	 * @since   4.5.9
	 */
	function vat_error_message( $vat_number, $message = '' ) {
		return str_replace(
			'%eu_vat_number%',
			esc_attr( $vat_number ),
			do_shortcode(
				get_option(
					'alg_wc_eu_vat_not_valid_message',
					__( "<strong>EU VAT Number</strong> is not valid.", 'eu-vat-for-woocommerce' )
				) . esc_html( $message )
			)
		);
	}

}

endif;

return new Alg_WC_EU_VAT_Core();
