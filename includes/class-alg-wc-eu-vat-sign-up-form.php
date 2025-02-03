<?php
/**
 * EU VAT for WooCommerce - Sign-up Form
 *
 * @version 4.2.4
 * @since   4.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Sign_Up_Form' ) ) :

class Alg_WC_EU_VAT_Sign_Up_Form {

	/**
	 * Constructor.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function __construct() {

		if ( 'no' === get_option( 'alg_wc_eu_vat_field_signup_form', 'no' ) ) {
			return;
		}

		add_action( 'woocommerce_register_form', array( $this, 'add_field_to_woocommerce_register_form' ), 15 );
		add_action( 'woocommerce_register_post', array( $this, 'validate_field_in_woocommerce_register_form' ), 15, 3 );
		add_action( 'woocommerce_created_customer', array( $this, 'save_field_on_woocommerce_created_customer' ), 15 );

	}

	/**
	 * add_field_to_woocommerce_register_form.
	 *
	 * @version 4.0.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) new field to signup form
	 */
	function add_field_to_woocommerce_register_form() {
		woocommerce_form_field(
			alg_wc_eu_vat_get_field_id(),
			alg_wc_eu_vat()->core->get_field_data()
		);
	}

	/**
	 * validate_field_in_woocommerce_register_form.
	 *
	 * @version 4.2.4
	 * @since   1.0.0
	 *
	 * @todo    (dev) `alg_wc_eu_vat_field_required`?
	 */
	function validate_field_in_woocommerce_register_form( $username, $email, $errors ) {

		$field_id = alg_wc_eu_vat_get_field_id();

		if ( ! isset( $_POST[ $field_id ] ) ) {
			return;
		}

		$eu_vat_to_check = sanitize_text_field( wp_unslash( $_POST[ $field_id ] ) );

		$form_company_name = (
			isset( $_POST['billing_company'] ) ?
			sanitize_text_field( wp_unslash( $_POST['billing_company'] ) ) :
			''
		);

		$form_country = (
			isset( $_POST['billing_country'] ) ?
			sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) :
			''
		);

		if (
			'yes' === get_option( 'alg_wc_eu_vat_field_required', 'yes' ) &&
			'yes' === get_option( 'alg_wc_eu_vat_validate_sign_up_page', 'yes' )
		) {

			$is_valid = alg_wc_eu_vat()->core->check_and_save_eu_vat(
				$eu_vat_to_check,
				$form_country,
				$form_company_name
			);

			if ( ! $is_valid ) {
				$request_uri = (
					isset( $_SERVER['REQUEST_URI'] ) ?
					sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) :
					''
				);
				if ( false === strpos( $request_uri, 'wp-json' ) ) {
					$text_not_valid = get_option(
						'alg_wc_eu_vat_progress_text_not_valid',
						__( 'VAT is not valid.', 'eu-vat-for-woocommerce' )
					);
					$errors->add( $field_id . '_error', $text_not_valid );
				}
			}

		}

	}

	/**
	 * save_field_on_woocommerce_created_customer.
	 *
	 * @version 4.0.0
	 * @since   1.0.0
	 */
	function save_field_on_woocommerce_created_customer( $customer_id ) {
		$field_id = alg_wc_eu_vat_get_field_id();
		if ( isset( $_POST[ $field_id ] ) ) {
			$value = sanitize_text_field( wp_unslash( $_POST[ $field_id ] ) );
			update_user_meta( $customer_id, $field_id, $value );
		}
	}

}

endif;

return new Alg_WC_EU_VAT_Sign_Up_Form();
