<?php
/**
 * EU VAT for WooCommerce - Core Class
 *
 * @version 1.7.2
 * @since   1.0.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_EU_VAT_Core' ) ) :

class Alg_WC_EU_VAT_Core {

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 * @todo    [dev] (maybe) "eu vat number" to "eu vat"
	 * @todo    [feature] `add_eu_vat_verify_button` (`woocommerce_form_field_text`) (`return ( alg_wc_eu_vat_get_field_id() === $key ) ? $field . '<span style="font-size:smaller !important;">' . '[<a name="billing_eu_vat_number_verify" href="">' . __( 'Verify', 'eu-vat-for-woocommerce' ) . '</a>]' . '</span>' : $field;`)
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_eu_vat_plugin_enabled', 'yes' ) ) {
			// Properties
			$this->is_wc_version_below_3_0_0 = version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' );
			// Functions
			require_once( 'functions/alg-wc-eu-vat-functions-validation.php' );
			// Classes
			require_once( 'class-alg-wc-eu-vat-ajax.php' );
			require_once( 'admin/class-alg-wc-eu-vat-admin.php' );
			// Hooks: Session, exclusion, validation
			add_action( 'init',                                                      array( $this, 'start_session' ) );
			add_filter( 'init',                                                      array( $this, 'maybe_exclude_vat' ), PHP_INT_MAX );
			add_action( 'woocommerce_after_checkout_validation',                     array( $this, 'checkout_validate_vat' ), PHP_INT_MAX );
			// Hooks: Customer meta, default value
			add_filter( 'woocommerce_customer_meta_fields',                          array( $this, 'add_eu_vat_number_customer_meta_field' ) );
			add_filter( 'default_checkout_' . alg_wc_eu_vat_get_field_id(),          array( $this, 'add_default_checkout_billing_eu_vat_number' ), PHP_INT_MAX, 2 );
			// Hooks: Frontend
			add_filter( 'woocommerce_checkout_fields',                               array( $this, 'add_eu_vat_checkout_field_to_frontend' ), PHP_INT_MAX );
			// Hooks: Display
			$positions = get_option( 'alg_wc_eu_vat_display_position', array( 'after_order_table' ) );
			if ( empty( $positions ) ) {
				$positions = array( 'after_order_table' );
			}
			if ( ! is_array( $positions ) ) {
				$positions = array( $positions );
			}
			if ( in_array( 'after_order_table', $positions ) ) {
				add_action( 'woocommerce_order_details_after_order_table',           array( $this, 'add_eu_vat_number_to_order_display' ), PHP_INT_MAX );
				add_action( 'woocommerce_email_after_order_table',                   array( $this, 'add_eu_vat_number_to_order_display' ), PHP_INT_MAX );
			}
			if ( in_array( 'in_billing_address', $positions ) ) {
				add_filter( 'woocommerce_order_formatted_billing_address',           array( $this, 'add_eu_vat_number_to_order_billing_address' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_my_account_my_address_formatted_address',   array( $this, 'add_eu_vat_number_to_my_account_billing_address' ), PHP_INT_MAX, 3 );
				add_filter( 'woocommerce_localisation_address_formats',              array( $this, 'add_eu_vat_number_to_address_formats' ) );
				add_filter( 'woocommerce_formatted_address_replacements',            array( $this, 'replace_eu_vat_number_in_address_formats' ), PHP_INT_MAX, 2 );
				// Make it editable ("My Account > Addresses")
				add_filter( 'woocommerce_address_to_edit',                           array( $this, 'add_eu_vat_number_to_editable_fields' ), PHP_INT_MAX, 2 );
				add_action( 'woocommerce_customer_save_address',                     array( $this, 'save_eu_vat_number_from_editable_fields' ), PHP_INT_MAX, 2 );
			}
			// Show zero VAT
			if ( 'yes' === get_option( 'alg_wc_eu_vat_always_show_zero_vat', 'no' ) ) {
				add_filter( 'woocommerce_cart_tax_totals',                           array( $this, 'always_show_zero_vat' ), PHP_INT_MAX, 2 );
			}
			// Shortcodes
			add_shortcode( 'alg_wc_eu_vat_translate',                                array( $this, 'language_shortcode' ) );
			// Show field for selected countries only
			if ( '' != ( $this->show_in_countries = apply_filters( 'alg_wc_eu_vat_show_in_countries', '' ) ) ) {
				add_filter( 'woocommerce_get_country_locale',                        array( $this, 'set_eu_vat_country_locale' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_get_country_locale_default',                array( $this, 'set_eu_vat_country_locale_default' ), PHP_INT_MAX );
				add_filter( 'woocommerce_country_locale_field_selectors',            array( $this, 'set_eu_vat_country_locale_field_selectors' ), PHP_INT_MAX );
			}
			// "Place order" button confirmation
			if ( 'yes' === get_option( 'alg_wc_eu_vat_field_confirmation', 'no' ) ) {
				add_filter( 'wp_enqueue_scripts',                                    array( $this, 'add_place_order_button_confirmation_script' ) );
			}
		}
	}

	/**
	 * add_place_order_button_confirmation_script.
	 *
	 * @version 1.4.1
	 * @since   1.4.1
	 */
	function add_place_order_button_confirmation_script() {
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			wp_enqueue_script( 'alg-wc-eu-vat-place-order',
				trailingslashit( alg_wc_eu_vat()->plugin_url() ) . 'includes/js/alg-wc-eu-vat-place-order.js', array( 'jquery' ), alg_wc_eu_vat()->version, true );
			wp_localize_script( 'alg-wc-eu-vat-place-order',
				'place_order_data', array( 'confirmation_text' => get_option( 'alg_wc_eu_vat_field_confirmation_text',
					__( 'You didn\'t set your VAT ID. Are you sure you want to continue?', 'eu-vat-for-woocommerce' ) ) ) );
		}
	}

	/**
	 * language_shortcode.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function language_shortcode( $atts, $content = '' ) {
		// E.g.: `[alg_wc_eu_vat_translate lang="DE,NL" lang_text="EU-Steuernummer" not_lang_text="EU VAT Number"]`
		if ( isset( $atts['lang_text'] ) && isset( $atts['not_lang_text'] ) && ! empty( $atts['lang'] ) ) {
			return ( ! defined( 'ICL_LANGUAGE_CODE' ) || ! in_array( strtolower( ICL_LANGUAGE_CODE ), array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) ) ) ) ?
				$atts['not_lang_text'] : $atts['lang_text'];
		}
		// E.g.: `[alg_wc_eu_vat_translate lang="DE"]EU-Steuernummer[/alg_wc_eu_vat_translate][alg_wc_eu_vat_translate lang="NL"]BTW nummer van de EU[/alg_wc_eu_vat_translate][alg_wc_eu_vat_translate not_lang="DE,NL"]EU VAT Number[/alg_wc_eu_vat_translate]`
		return (
			( ! empty( $atts['lang'] )     && ( ! defined( 'ICL_LANGUAGE_CODE' ) || ! in_array( strtolower( ICL_LANGUAGE_CODE ), array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) ) ) ) ) ||
			( ! empty( $atts['not_lang'] ) &&     defined( 'ICL_LANGUAGE_CODE' ) &&   in_array( strtolower( ICL_LANGUAGE_CODE ), array_map( 'trim', explode( ',', strtolower( $atts['not_lang'] ) ) ) ) )
		) ? '' : $content;
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
	 * @version 1.4.1
	 * @since   1.4.0
	 */
	function set_eu_vat_country_locale_default( $default_locale ) {
		// Disable field in default locale
		$default_locale[ alg_wc_eu_vat_get_field_id( true ) ] = array(
			'required' => false,
			'hidden'   => true,
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
		return apply_filters( 'alg_wc_eu_vat_set_eu_vat_country_locale', $country_locales, $this->show_in_countries );
	}

	/**
	 * always_show_zero_vat.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 * @todo    [dev] (maybe) remove `$zero_tax->amount`, `$zero_tax->tax_rate_id`, `$zero_tax->is_compound` (as they are not really used in `review-order` template)
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
	 * get_field_data.
	 *
	 * @version 1.7.2
	 * @since   1.3.0
	 * @todo    [dev] rethink `$is_required` (check filters: `woocommerce_default_address_fields`, `woocommerce_billing_fields`)
	 * @todo    [dev] (maybe) `default`
	 * @todo    [dev] (maybe) `autocomplete`
	 * @todo    [dev] (maybe) `value`
	 */
	function get_field_data() {
		$is_required = ( 'yes' === get_option( 'alg_wc_eu_vat_field_required', 'no' ) );
		if ( '' != ( $this->show_in_countries = apply_filters( 'alg_wc_eu_vat_show_in_countries', '' ) ) ) {
			$show_eu_vat_field_countries = array_map( 'strtoupper', array_map( 'trim', explode( ',', $this->show_in_countries ) ) );
			if ( ! in_array( WC()->checkout->get_value( 'billing_country' ), $show_eu_vat_field_countries ) ) {
				$is_required = false;
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
	 * add_eu_vat_number_to_editable_fields.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 * @todo    [dev] (maybe) `check_current_user_roles()`
	 * @todo    [feature] (maybe) also add an option to display/edit in "My Account > Account details"
	 */
	function add_eu_vat_number_to_editable_fields( $address, $load_address ) {
		if ( 'billing' === $load_address ) {
			$field_id = alg_wc_eu_vat_get_field_id();
			$address[ $field_id ] = $this->get_field_data();
			$address[ $field_id ]['value'] = get_user_meta( get_current_user_id(), $field_id, true );
		}
		return $address;
	}

	/**
	 * save_eu_vat_number_from_editable_fields.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function save_eu_vat_number_from_editable_fields( $user_id, $load_address ) {
		if ( 'billing' === $load_address ) {
			$field_id = alg_wc_eu_vat_get_field_id();
			if ( isset( $_POST[ $field_id ] ) ) {
				update_user_meta( $user_id, $field_id, $_POST[ $field_id ] );
			}
		}
	}

	/**
	 * add_eu_vat_checkout_field_to_frontend.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 */
	function add_eu_vat_checkout_field_to_frontend( $fields ) {
		$user_roles = apply_filters( 'alg_wc_eu_vat_show_for_user_roles', array() );
		if ( ! empty( $user_roles ) && ! $this->check_current_user_roles( $user_roles ) ) {
			return $fields;
		}
		$fields['billing'][ alg_wc_eu_vat_get_field_id() ] = $this->get_field_data();
		return $fields;
	}

	/**
	 * replace_eu_vat_number_in_address_formats.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function replace_eu_vat_number_in_address_formats( $replacements, $args ) {
		$field_name = alg_wc_eu_vat_get_field_id();
		$replacements['{' . $field_name . '}'] = ( isset( $args[ $field_name ] ) ) ? $args[ $field_name ] : '';
		return $replacements;
	}

	/**
	 * add_eu_vat_number_to_address_formats.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_eu_vat_number_to_address_formats( $address_formats ) {
		$field_name = alg_wc_eu_vat_get_field_id();
		$modified_address_formats = array();
		foreach ( $address_formats as $country => $address_format ) {
			$modified_address_formats[ $country ] = $address_format . "\n{" . $field_name . '}';
		}
		return $modified_address_formats;
	}

	/**
	 * add_eu_vat_number_to_my_account_billing_address.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_eu_vat_number_to_my_account_billing_address( $fields, $customer_id, $name ) {
		if ( 'billing' === $name ) {
			$field_name = alg_wc_eu_vat_get_field_id();
			$fields[ $field_name ] = get_user_meta( $customer_id, $field_name, true );
		}
		return $fields;
	}

	/**
	 * add_eu_vat_number_to_order_billing_address.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_eu_vat_number_to_order_billing_address( $fields, $_order ) {
		$field_name = alg_wc_eu_vat_get_field_id();
		$fields[ $field_name ] = get_post_meta( alg_wc_eu_vat_get_order_id( $_order ), '_' . $field_name, true );
		return $fields;
	}

	/**
	 * add_eu_vat_number_to_order_display.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	function add_eu_vat_number_to_order_display( $order ) {
		$order_id          = alg_wc_eu_vat_get_order_id( $order );
		$html              = '';
		$option_name       = '_' . alg_wc_eu_vat_get_field_id();
		$the_eu_vat_number = get_post_meta( $order_id, $option_name, true );
		if ( '' != $the_eu_vat_number ) {
			$the_label = do_shortcode( get_option( 'alg_wc_eu_vat_field_label', __( 'EU VAT Number', 'eu-vat-for-woocommerce' ) ) );
			$html .= '<p>' . '<strong>' . $the_label . '</strong>: ' . $the_eu_vat_number . '</p>';
		}
		echo $html;
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
		alg_wc_eu_vat_session_start();
		$args = array();
		if ( isset( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $args );
			if ( isset( $args[ alg_wc_eu_vat_get_field_id() ] ) && alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' ) != $args[ alg_wc_eu_vat_get_field_id() ] ) {
				alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_valid', null );
				alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check', null );
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
	 * @todo    [dev] (maybe) assign `array( 'guest' )` if `wp_get_current_user()` does not exist
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
		return ( is_checkout() || is_cart() || defined( 'WOOCOMMERCE_CHECKOUT' ) || defined( 'WOOCOMMERCE_CART' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) );
	}

	/**
	 * is_validate_and_exempt.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function is_validate_and_exempt() {
		return ( 'yes' === get_option( 'alg_wc_eu_vat_validate', 'yes' ) && 'yes' === get_option( 'alg_wc_eu_vat_disable_for_valid', 'yes' ) );
	}

	/**
	 * check_and_save_eu_vat.
	 *
	 * @version 1.7.1
	 * @since   1.7.1
	 * @todo    [dev] (important) use in `Alg_WC_EU_VAT_AJAX::alg_wc_eu_vat_validate_action()`
	 */
	function check_and_save_eu_vat( $eu_vat_to_check, $billing_country, $billing_company ) {
		$eu_vat_number = alg_wc_eu_vat_parse_vat( $eu_vat_to_check, $billing_country );
		if ( 'yes' === apply_filters( 'alg_wc_eu_vat_check_ip_location_country', 'no' ) ) {
			$country_by_ip   = alg_wc_eu_vat_get_customers_location_by_ip();
			$is_county_valid = ( $country_by_ip === $eu_vat_number['country'] );
			$is_valid        = $is_county_valid ? alg_wc_eu_vat_validate_vat( $eu_vat_number['country'], $eu_vat_number['number'], $billing_company ) : false;
			if ( ! $is_county_valid ) {
				alg_wc_eu_vat_maybe_log( $eu_vat_number['country'], $eu_vat_number['number'], $billing_company, '',
					sprintf( __( 'Error: Country by IP does not match (%s)', 'eu-vat-for-woocommerce' ), $country_by_ip ) );
			}
		} else {
			$is_valid = alg_wc_eu_vat_validate_vat( $eu_vat_number['country'], $eu_vat_number['number'], $billing_company );
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
	 * @version 1.7.0
	 * @since   1.0.0
	 * @todo    [fix] (important) mini cart
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
		WC()->customer->set_is_vat_exempt( $is_exempt );
	}

	/**
	 * checkout_validate_vat.
	 *
	 * @version 1.7.1
	 * @since   1.0.0
	 * @todo    [dev] (important) simplify the code
	 */
	function checkout_validate_vat( $_posted ) {
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
						( isset( $_posted['billing_country'] ) ? $_posted['billing_country'] : '' ),
						( isset( $_posted['billing_company'] ) ? $_posted['billing_company'] : '' )
					);
				}
				if ( ! $is_valid ) {
					wc_add_notice(
						str_replace( '%eu_vat_number%', $_posted[ alg_wc_eu_vat_get_field_id() ],
							do_shortcode( get_option( 'alg_wc_eu_vat_not_valid_message', __( '<strong>EU VAT Number</strong> is not valid.', 'eu-vat-for-woocommerce' ) ) ) ),
						'error'
					);
					alg_wc_eu_vat_maybe_log(
						( isset( $_posted['billing_country'] ) ? $_posted['billing_country'] : '' ),
						$_posted[ alg_wc_eu_vat_get_field_id() ],
						( isset( $_posted['billing_company'] ) ? $_posted['billing_company'] : '' ),
						'',
						__( 'Error: VAT is not valid (checkout)', 'eu-vat-for-woocommerce' )
					);
				}
			}
		}
	}

}

endif;

return new Alg_WC_EU_VAT_Core();
