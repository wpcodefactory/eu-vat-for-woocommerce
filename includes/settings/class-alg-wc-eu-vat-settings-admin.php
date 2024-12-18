<?php
/**
 * EU VAT for WooCommerce - Admin Section Settings
 *
 * @version 3.0.0
 * @since   1.5.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Settings_Admin' ) ) :

class Alg_WC_EU_VAT_Settings_Admin extends Alg_WC_EU_VAT_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function __construct() {
		$this->id   = 'admin';
		$this->desc = __( 'Admin & Advanced', 'eu-vat-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.0.0
	 * @since   1.5.0
	 */
	function get_settings() {

		$admin_settings = array(
			array(
				'title'    => __( 'Admin Options', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_admin_options',
			),
			array(
				'title'    => __( 'Meta box', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Adds EU VAT number summary meta box to admin order edit page.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Add', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_add_order_edit_metabox',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Column', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Adds EU VAT number column to admin orders list.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Add', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_add_order_list_column',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'VAT Validation for Orders Created Manually from the Admin Side', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Validate VAT when an order is created manually from the admin side.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_validate_vat_admin_side',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_admin_options',
			),

			array(
				'title'    => __( 'Language Guide', 'eu-vat-for-woocommerce' ),
				'desc'     =>
					'<table class="form-table" style="width:67%;">' .
						'<tbody>' .
							'<tr valign="top" class="">' .
								'<th scope="row" class="titledesc">' .
									__( 'WPML or Polylang', 'eu-vat-for-woocommerce' ) .
								'</th>' .
								'<td class="forminp forminp-checkbox">' .
									__( 'If you are using multi-language store with WPML or Polylang, you can use shortcodes to show different languages.', 'eu-vat-for-woocommerce' ) .
									'<br>' .
									sprintf(
										__( 'Example: %s can be used to show English messages, similar to other languages you have.', 'eu-vat-for-woocommerce' ),
										'<code>[alg_wc_eu_vat_translate lang="en"]Your VAT number is valid.[/alg_wc_eu_vat_translate]</code>'
									) .
									'<br>' .
									sprintf(
										__( 'Use %s as fallback for non-defined languages.', 'eu-vat-for-woocommerce' ),
										'<code>[alg_wc_eu_vat_translate not_lang=" "]</code>'
									) .
								'</td>' .
							'</tr>' .
						'</tbody>' .
					'</table>',
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_language_guide',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_language_guide',
			),
		);

		$advanced_settings = array(
			array(
				'title'    => __( 'Advanced Options', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_advanced_options',
			),
			array(
				'title'    => __( 'Debug', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Log will be added to %s.', 'eu-vat-for-woocommerce' ),
					'<a href="' . admin_url( 'admin.php?page=wc-status&tab=logs' ) . '">' . __( 'WooCommerce > Status > Logs', 'eu-vat-for-woocommerce' ) . '</a>' ),
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
				'title'    => __( 'Enable if sitepress optimizer dynamic caching plugin not works', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_sitepress_optimizer_dynamic_caching',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'VAT shifted text', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Please use text for 3rd party PDF invoice plugin.', 'eu-vat-for-woocommerce' ),
				'desc'     => '',
				'id'       => 'alg_wc_eu_vat_advanced_vat_shifted_text',
				'default'  => __( 'VAT Shifted', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Enable manual validation of VAT numbers', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_manual_validation_enable',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'VAT numbers to pass validation', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enter multiple VAT numbers that have been manually validated, separated by commas.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enter multiple VAT numbers that have been manually validated, separated by commas.', 'eu-vat-for-woocommerce' ),
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
				'title'    => __( 'Autofill company name from VAT ID', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Please use validation method SOAP for this option to work.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_advance_enable_company_name_autofill',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_advanced_options',
			),
		);

		$additional_info = array(
			array(
				'title'    => '&#8505;' . ' ' . __( 'Additional Info', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_additional_info',
				'desc'     => '<ul style="background-color:white;padding:10px 30px;color:black;list-style-type:square;margin-top:50px;">' .
					'<li>' . sprintf( __( 'Field ID used for EU VAT: %s.', 'eu-vat-for-woocommerce' ) , '<code>' . '_' . alg_wc_eu_vat_get_field_id() . '</code>' ) . '</li>' .
					'<li>' . sprintf( __( 'Tool for adding EU country standard VAT rates: %s.', 'eu-vat-for-woocommerce' ),
						'<a href="' . admin_url( 'tools.php?page=alg-wc-eu-vat-country-rates' ) . '">' . __( 'Tools > EU country VAT Rates', 'eu-vat-for-woocommerce' ) . '</a>' ) . '</li>' .
					'<li>' . sprintf( __( 'EU VAT report: %s.', 'eu-vat-for-woocommerce' ),
						'<a href="' . admin_url( 'admin.php?page=wc-reports&tab=taxes&report=alg_wc_eu_vat' ) . '">' . __( 'WooCommerce > Reports > Taxes > EU VAT', 'eu-vat-for-woocommerce' ) . '</a>' ) . '</li>' .
					'<li>' . sprintf( __( 'You can use shortcodes in field label, placeholder, description and all messages options, e.g.: %s.', 'eu-vat-for-woocommerce' ),
						'<a target="_blank" href="https://wpfactory.com/kb/eu-vat-for-woocommerce/shortcodes/"><code>[alg_wc_eu_vat_translate]</code></a>' ) . '</li>' .
					'<li>' . sprintf( __( 'Plugin description on %s.', 'eu-vat-for-woocommerce' ),
						'<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">WPFactory</a>' ) . '</li>' .
				'</ul>',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_additional_info',
			),
		);

		return array_merge( $admin_settings, $advanced_settings, $additional_info );
	}

}

endif;

return new Alg_WC_EU_VAT_Settings_Admin();
