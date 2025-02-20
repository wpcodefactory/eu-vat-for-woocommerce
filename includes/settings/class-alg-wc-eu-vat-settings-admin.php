<?php
/**
 * EU VAT for WooCommerce - Admin Section Settings
 *
 * @version 4.3.0
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
	 * @version 4.2.3
	 * @since   1.5.0
	 */
	function __construct() {
		$this->id   = 'admin';
		$this->desc = __( 'Admin', 'eu-vat-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 4.3.0
	 * @since   1.5.0
	 *
	 * @todo    (dev) `alg_wc_eu_vat_add_order_edit_metabox` default to `yes`?
	 */
	function get_settings() {

		// Admin Options
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
				'title'    => __( 'VAT validation for orders created manually from the admin side', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Validate VAT when an order is created manually from the admin side.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_validate_vat_admin_side',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Admin new order email', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Adds EU VAT number summary to the admin new order emails.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_admin_new_order_email',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_admin_options',
			),
		);

		// Additional Info
		$additional_info = array(
			array(
				'title'    => '&#8505;' . ' ' . __( 'Additional Info', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_additional_info',
				'desc'     => '<ul style="background-color:white;padding:10px 30px;color:black;list-style-type:square;margin-top:1em;">' .
					'<li>' .
						sprintf(
							/* Translators: %s: Field ID. */
							__( 'Field ID used for EU VAT: %s.', 'eu-vat-for-woocommerce' ) ,
							'<code>' . '_' . alg_wc_eu_vat_get_field_id() . '</code>'
						) .
					'</li>' .
					'<li>' .
						sprintf(
							/* Translators: %s: Tool link. */
							__( 'Tool for adding EU country standard VAT rates: %s.', 'eu-vat-for-woocommerce' ),
							'<a href="' . admin_url( 'tools.php?page=alg-wc-eu-vat-country-rates' ) . '">' .
								__( 'Tools > EU country VAT Rates', 'eu-vat-for-woocommerce' ) .
							'</a>'
						) .
					'</li>' .
					'<li>' .
						sprintf(
							/* Translators: %s: Report link. */
							__( 'EU VAT report: %s.', 'eu-vat-for-woocommerce' ),
							'<a href="' . admin_url( 'admin.php?page=wc-reports&tab=taxes&report=alg_wc_eu_vat' ) . '">' .
								__( 'WooCommerce > Reports > Taxes > EU VAT', 'eu-vat-for-woocommerce' ) .
							'</a>'
						) .
					'</li>' .
					'<li>' .
						sprintf(
							/* Translators: %s: Site link. */
							__( 'You can use shortcodes in field label, placeholder, description and all messages options, e.g.: %s.', 'eu-vat-for-woocommerce' ),
							'<a target="_blank" href="https://wpfactory.com/kb/eu-vat-for-woocommerce/shortcodes/"><code>[alg_wc_eu_vat_translate]</code></a>'
						) .
					'</li>' .
					'<li>' .
						sprintf(
							/* Translators: %s: Site link. */
							__( 'Plugin description on %s.', 'eu-vat-for-woocommerce' ),
							'<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">WPFactory</a>'
						) .
					'</li>' .
				'</ul>',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_additional_info',
			),
		);

		// Language Guide
		$language_guide = array(
			array(
				'title'    => '&#8505;' . ' ' . __( 'Language Guide', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_language_guide',
				'desc'     => (
					'<p>' .
						__( 'If you have multi-language store with WPML or Polylang, you can use shortcodes to show different languages.', 'eu-vat-for-woocommerce' ) .
						' ' .
						__( 'For example:', 'eu-vat-for-woocommerce' ) .
					'</p>' .
					'<p>' .
						sprintf(
							/* Translators: %s: Shortcode example. */
							__( '%s can be used to show English messages, similar to other languages you have.', 'eu-vat-for-woocommerce' ),
							'<code>[alg_wc_eu_vat_translate lang="en"]Your VAT number is valid.[/alg_wc_eu_vat_translate]</code>'
						) .
					'</p>' .
					'<p>' .
						sprintf(
							/* Translators: %s: Shortcode example. */
							__( '%s can be used as a fallback for non-defined languages.', 'eu-vat-for-woocommerce' ),
							'<code>[alg_wc_eu_vat_translate not_lang="en"]Your VAT number is valid.[/alg_wc_eu_vat_translate]</code>'
						) .
					'</p>'
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_language_guide',
			),
		);

		// Result
		return array_merge(
			$admin_settings,
			$additional_info,
			$language_guide
		);

	}

}

endif;

return new Alg_WC_EU_VAT_Settings_Admin();
