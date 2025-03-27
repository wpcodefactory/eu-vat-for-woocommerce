<?php
/**
 * EU VAT for WooCommerce - Compatibility Section Settings
 *
 * @version 4.3.6
 * @since   4.3.6
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Settings_Compatibility' ) ) :

class Alg_WC_EU_VAT_Settings_Compatibility extends Alg_WC_EU_VAT_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 4.3.6
	 * @since   4.3.6
	 */
	function __construct() {
		$this->id   = 'compatibility';
		$this->desc = __( 'Compatibility', 'eu-vat-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 4.3.6
	 * @since   4.3.6
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Compatibility Options', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_compatibility_options',
			),
			array(
				'title'    => __( 'PDF Invoices & Packing Slips for WooCommerce', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => sprintf(
					/* Translators: %s: Plugin link. */
					__( 'Enables compatibility with the %s plugin.', 'eu-vat-for-woocommerce' ),
					'<a target="_blank" href="https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/">' .
						__( 'PDF Invoices & Packing Slips for WooCommerce', 'eu-vat-for-woocommerce' ) .
					'</a>'
				),
				'id'       => 'alg_wc_eu_vat_compatibility_wpo_wcpdf',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Prefix', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_compatibility_wpo_wcpdf_prefix',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'VAT shifted text', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_advanced_vat_shifted_text',
				'default'  => __( 'VAT Shifted', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'title'    => __( 'YITH WooCommerce PDF Invoices & Packing Slips', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => sprintf(
					/* Translators: %s: Plugin link. */
					__( 'Enables compatibility with the %s plugin.', 'eu-vat-for-woocommerce' ),
					'<a target="_blank" href="https://yithemes.com/themes/plugins/yith-woocommerce-pdf-invoice/">' .
						__( 'YITH WooCommerce PDF Invoices & Packing Slips', 'eu-vat-for-woocommerce' ) .
					'</a>'
				),
				'id'       => 'alg_wc_eu_vat_compatibility_yith_ywpi',
				'default'  => 'yes',
				'type'     => 'checkbox',
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
	}

}

endif;

return new Alg_WC_EU_VAT_Settings_Compatibility();
