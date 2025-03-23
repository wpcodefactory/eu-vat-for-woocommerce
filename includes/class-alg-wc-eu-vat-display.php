<?php
/**
 * EU VAT for WooCommerce - Display
 *
 * @version 4.3.4
 * @since   4.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Display' ) ) :

class Alg_WC_EU_VAT_Display {

	/**
	 * Constructor.
	 *
	 * @version 4.2.9
	 * @since   4.0.0
	 */
	function __construct() {

		// Get positions
		$positions = $this->get_positions();

		// After order table
		if ( in_array( 'after_order_table', $positions ) ) {
			add_action( 'woocommerce_order_details_after_order_table', array( $this, 'add_eu_vat_number_to_order_display' ), PHP_INT_MAX );
			add_action( 'woocommerce_email_after_order_table', array( $this, 'add_eu_vat_number_to_order_display' ), PHP_INT_MAX );
		}

		// In billing address
		if ( in_array( 'in_billing_address', $positions ) ) {
			add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'add_eu_vat_number_to_order_billing_address' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_my_account_my_address_formatted_address', array( $this, 'add_eu_vat_number_to_my_account_billing_address' ), PHP_INT_MAX, 3 );
			add_filter( 'woocommerce_localisation_address_formats', array( $this, 'add_eu_vat_number_to_address_formats' ) );
			add_filter( 'woocommerce_formatted_address_replacements', array( $this, 'replace_eu_vat_number_in_address_formats' ), PHP_INT_MAX, 2 );
			// Make it editable ("My Account > Addresses")
			add_filter( 'woocommerce_address_to_edit', array( $this, 'add_eu_vat_number_to_editable_fields' ), PHP_INT_MAX, 2 );
			add_action( 'woocommerce_customer_save_address', array( $this, 'save_eu_vat_number_from_editable_fields' ), PHP_INT_MAX, 2 );
		}

	}

	/**
	 * get_positions.
	 *
	 * @version 4.2.9
	 * @since   4.2.9
	 */
	function get_positions() {
		$positions = get_option( 'alg_wc_eu_vat_display_position', array( 'after_order_table' ) );
		if ( empty( $positions ) ) {
			$positions = array( 'after_order_table' );
		}
		return (
			is_array( $positions ) ?
			$positions :
			array( $positions )
		);
	}

	/**
	 * add_eu_vat_number_to_order_display.
	 *
	 * @version 2.9.13
	 * @since   1.0.0
	 */
	function add_eu_vat_number_to_order_display( $order ) {

		$order_id                    = alg_wc_eu_vat_get_order_id( $order );
		$html                        = '';
		$option_name                 = '_' . alg_wc_eu_vat_get_field_id();
		$option_name_customer_decide = '_' . alg_wc_eu_vat_get_field_id() . '_customer_decide';
		$the_eu_vat_number           = $order->get_meta( $option_name );
		$customer_decide             = $order->get_meta( $option_name_customer_decide );

		if ( '' != $customer_decide ) {
			$the_label_cd = do_shortcode( __( 'Customer Decide', 'eu-vat-for-woocommerce' ) );
			$html .= '<p>' . '<strong>' . $the_label_cd . '</strong>: ' . ( 1 == $customer_decide ? 'yes' : 'no' ) . '</p>';
		}

		if ( '' != $the_eu_vat_number ) {
			$the_label = do_shortcode( get_option( 'alg_wc_eu_vat_field_label', __( 'EU VAT Number', 'eu-vat-for-woocommerce' ) ) );
			$html .= '<p>' . '<strong>' . $the_label . '</strong>: ' . $the_eu_vat_number . '</p>';
		}

		echo $html;

	}

	/**
	 * add_eu_vat_number_to_order_billing_address.
	 *
	 * @version 2.9.13
	 * @since   1.0.0
	 */
	function add_eu_vat_number_to_order_billing_address( $fields, $_order ) {
		$field_name  = alg_wc_eu_vat_get_field_id();
		$field_value = $_order->get_meta( '_' . $field_name );
		$fields[ $field_name ] = $field_value;
		return $fields;
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
	 * add_eu_vat_number_to_address_formats.
	 *
	 * @version 4.3.4
	 * @since   1.0.0
	 */
	function add_eu_vat_number_to_address_formats( $address_formats ) {
		$field_id = alg_wc_eu_vat_get_field_id();
		foreach ( $address_formats as &$address_format ) {
			$address_format .= "\n{{$field_id}}";
		}
		return $address_formats;
	}

	/**
	 * replace_eu_vat_number_in_address_formats.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function replace_eu_vat_number_in_address_formats( $replacements, $args ) {
		$field_name    = alg_wc_eu_vat_get_field_id();
		$the_label     = do_shortcode( get_option( 'alg_wc_eu_vat_field_label', __( 'EU VAT Number', 'eu-vat-for-woocommerce' ) ) );
		$field_name_cd = alg_wc_eu_vat_get_field_id() . '_customer_decide';

		if ( isset( $args[ $field_name ] ) && ! empty( $args[ $field_name ] ) ) {
			$replacements[ '{' . $field_name . '}' ] = ( isset( $args[ $field_name ] ) ) ? $the_label . ': ' . $args[ $field_name ] : '';
		}else{
			$replacements[ '{' . $field_name . '}' ] = ( isset( $args[ $field_name ] ) ) ? $args[ $field_name ] : '';
		}

		return $replacements;

	}

	/**
	 * add_eu_vat_number_to_editable_fields.
	 *
	 * @version 4.0.0
	 * @since   1.3.0
	 *
	 * @todo    (dev) `check_current_user_roles()`?
	 * @todo    (feature) also add an option to display/edit in "My Account > Account details"?
	 */
	function add_eu_vat_number_to_editable_fields( $address, $load_address ) {
		if ( 'billing' === $load_address ) {
			$field_id = alg_wc_eu_vat_get_field_id();

			$address[ $field_id ] = alg_wc_eu_vat()->core->get_field_data();
			$address[ $field_id ]['value'] = get_user_meta( get_current_user_id(), $field_id, true );
		}
		return $address;
	}

	/**
	 * save_eu_vat_number_from_editable_fields.
	 *
	 * @version 4.1.0
	 * @since   1.3.0
	 */
	function save_eu_vat_number_from_editable_fields( $user_id, $load_address ) {
		if ( 'billing' === $load_address ) {
			$field_id    = alg_wc_eu_vat_get_field_id();
			$field_id_cd = alg_wc_eu_vat_get_field_id() . '_customer_decide';
			if ( isset( $_POST[ $field_id ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $field_id ] ) );
				update_user_meta( $user_id, $field_id, $value );
			}
			if ( isset( $_POST[ $field_id_cd ] ) && 1 == $_POST[ $field_id_cd ] ) {
				$value_cd = sanitize_text_field( wp_unslash( $_POST[ $field_id_cd ] ) );
				update_user_meta( $user_id, $field_id_cd, $value_cd );
			}
		}
	}

}

endif;

return new Alg_WC_EU_VAT_Display();
