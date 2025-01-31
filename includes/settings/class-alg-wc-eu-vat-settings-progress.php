<?php
/**
 * EU VAT for WooCommerce - Progress Section Settings
 *
 * @version 4.2.3
 * @since   4.2.3
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Settings_Progress' ) ) :

class Alg_WC_EU_VAT_Settings_Progress extends Alg_WC_EU_VAT_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 4.2.3
	 * @since   4.2.3
	 */
	function __construct() {
		$this->id   = 'progress';
		$this->desc = __( 'Progress', 'eu-vat-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 4.2.3
	 * @since   4.2.3
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Progress Messages', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_message_options',
			),
			array(
				'title'    => __( 'Add progress messages', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enables/disables progress messages on checkout.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Add', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_add_progress_text',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Validating', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message when validation is in progress. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-validating</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_progress_text_validating',
				'default'  => __( 'Validating VAT. Please wait...', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Valid', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on valid VAT. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-valid</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_progress_text_valid',
				'default'  => __( 'VAT is valid.', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Not valid', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on invalid VAT. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-not-valid</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_progress_text_not_valid',
				'default'  => __( 'VAT is not valid.', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Is required', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on empty (required) VAT.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_progress_text_is_required',
				'default'  => __( 'VAT is required.', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Validation failed', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on VAT validation server timeout etc. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-validation-failed</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_progress_text_validation_failed',
				'default'  => __( 'Validation failed. Please try again.', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Different shipping & billing countries', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on Different shipping & billing countries. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-not-valid-billing-country</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_shipping_billing_countries',
				'default'  => __( 'Different shipping & billing countries.', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Company name mismatch', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on Company name mismatch. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-not-valid-company-mismatch</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_company_name_mismatch',
				'default'  => __( 'VAT is valid, but registered to %company_name%.', 'eu-vat-for-woocommerce' ), // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Enable country preserve message', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enables/disables country preserve validation message.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_validate_enable_preserve_message',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Country preserved', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on billing country preserved for VAT. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-not-valid-country-preserved</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_progress_text_validation_preserv',
				'default'  => __( 'VAT preserved for this billing country.', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'VIES error message', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on VIES error. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-not-valid-vies-error</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_progress_text_validation_vies_error',
				'default'  => __( 'VAT accepted due to VIES error: %vies_error%. The admin will check the VAT validation again and proceed accordingly.', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Hide messages on preserved countries list', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_hide_message_on_preserved_countries',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Remove validation color', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Remove the validation color from the VAT field (this may depend on the theme)', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_remove_validation_color',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_message_options',
			),
		);
	}

}

endif;

return new Alg_WC_EU_VAT_Settings_Progress();
