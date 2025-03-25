<?php
/**
 * EU VAT for WooCommerce - Advanced Section Settings
 *
 * @version 4.3.5
 * @since   4.2.3
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Settings_Advanced' ) ) :

class Alg_WC_EU_VAT_Settings_Advanced extends Alg_WC_EU_VAT_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 4.2.3
	 * @since   4.2.3
	 */
	function __construct() {
		$this->id   = 'advanced';
		$this->desc = __( 'Advanced', 'eu-vat-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 4.3.5
	 * @since   4.2.3
	 *
	 * @todo    (dev) separate into "Advanced" and "Compatibility"?
	 * @todo    (dev) `alg_wc_eu_vat_enable_checkout_block_field` default to `yes`?
	 * @todo    (dev) "Sitepress" - should be "SiteGround"?
	 */
	function get_settings() {

		// Advanced Options
		$advanced_settings = array(
			array(
				'title'    => __( 'Advanced Options', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_advanced_options',
			),
			array(
				'title'    => __( 'Debug', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => sprintf(
					/* Translators: %s: Logs link. */
					__( 'Log will be added to %s.', 'eu-vat-for-woocommerce' ),
					'<a href="' . admin_url( 'admin.php?page=wc-status&tab=logs' ) . '">' .
						__( 'WooCommerce > Status > Logs', 'eu-vat-for-woocommerce' ) .
					'</a>'
				),
				'id'       => 'alg_wc_eu_vat_debug',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Session type', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_session_type',
				'default'  => 'wc',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'standard' => __( 'Standard PHP session', 'eu-vat-for-woocommerce' ),
					'wc'       => __( 'WC session (recommended)', 'eu-vat-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Force VAT recheck on checkout', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_force_checkout_recheck',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Sitepress optimizer dynamic caching plugin', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enable if Sitepress optimizer dynamic caching plugin does not work.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_sitepress_optimizer_dynamic_caching',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'VAT shifted text', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Please use text for 3rd party PDF invoice plugin.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_advanced_vat_shifted_text',
				'default'  => __( 'VAT Shifted', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Manual validation of VAT numbers', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_manual_validation_enable',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'VAT numbers to pass validation', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enter multiple VAT numbers that have been manually validated, separated by commas.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_manual_validation_vat_numbers',
				'default'  => '',
				'type'     => 'textarea',
			),
			array(
				'title'    => __( 'Remove country from VAT number in REST API', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_remove_country_rest_api_enable',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Reduce concurrent request to VIES', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enable if validation fails multiple times and you encounter the "MS_MAX_CONCURRENT_REQ" error after debugging.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_reduce_concurrent_request_enable',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Checkout block field', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_enable_checkout_block_field',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Add script dependency', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enable this if there is an "Uncaught ReferenceError: alg_wc_eu_vat_ajax_object is not defined" error on the checkout page.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_checkout_block_field_dependencies',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Autofill company name from VAT ID', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Please use the SOAP validation method for this option to work.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_advance_enable_company_name_autofill',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Force price display including tax', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Display prices with taxes on single product pages, etc., even if the customer is VAT-exempt.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_force_price_display_incl_tax',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_advanced_options',
			),
			array(
				'title'    => __( 'Request Identifier', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Gets and stores the "request identifier" code.', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_request_identifier_options',
			),
			array(
				'title'    => __( 'Request identifier', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_request_identifier',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Requester country code', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_requester_country_code',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Requester VAT number', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Without the country code.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_requester_vat_number',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_request_identifier_options',
			),
		);

		// Compatibility Options
		$compatibility_settings = array(
			array(
				'title'    => __( 'Compatibility Options', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_compatibility_options',
			),
			array(
				'title'    => __( 'Fluid Checkout for WooCommerce', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => sprintf(
					/* Translators: %s: Plugin link. */
					__( 'Enables compatibility with the %s plugin.', 'eu-vat-for-woocommerce' ),
					'<a target="_blank" href="https://wordpress.org/plugins/fluid-checkout/">' .
						__( 'Fluid Checkout for WooCommerce', 'eu-vat-for-woocommerce' ) .
					'</a>'
				),
				'id'       => 'alg_wc_eu_vat_compatibility_fluid_checkout',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_compatibility_options',
			),
		);

		// Result
		return array_merge(
			$advanced_settings,
			$compatibility_settings
		);

	}

}

endif;

return new Alg_WC_EU_VAT_Settings_Advanced();
