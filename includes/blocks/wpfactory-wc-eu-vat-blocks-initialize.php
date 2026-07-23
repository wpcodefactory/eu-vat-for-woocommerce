<?php
/**
 * EU VAT for WooCommerce - Blocks Initialize
 *
 * @version 4.7.5
 *
 * @author  WPFactory
 */

use \Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wpfactory_wc_eu_vat_is_checkout_block_enabled' ) ) {
	/**
	 * wpfactory_wc_eu_vat_is_checkout_block_enabled.
	 *
	 * @version 4.7.5
	 * @since   4.2.4
	 */
	function wpfactory_wc_eu_vat_is_checkout_block_enabled() {
		return (
			'yes' === get_option( 'alg_wc_eu_vat_enable_checkout_block_field', 'yes' ) &&
			'no' === get_option( 'alg_wc_eu_vat_hide_eu_vat', 'no' ) &&
			version_compare( get_option( 'woocommerce_version', null ), '8.9.1', '>=' )
		);
	}
}

/**
 * Check if checkout block is enabled.
 *
 * @version 4.7.0
 * @since   4.2.4
 */
if ( ! wpfactory_wc_eu_vat_is_checkout_block_enabled() ) {
	return;
}

/**
 * woocommerce_blocks_loaded.
 *
 * @version 4.7.0
 */
add_action( 'woocommerce_blocks_loaded', function () {

	require_once plugin_dir_path( __FILE__ ) . 'wpfactory-wc-eu-vat-blocks-integration.php';

	add_action(
		'woocommerce_blocks_cart_block_registration',
		function ( $integration_registry ) {
			$integration_registry->register( new WPFactory_WC_EU_VAT_Blocks_Integration() );
		}
	);

	add_action(
		'woocommerce_blocks_checkout_block_registration',
		function ( $integration_registry ) {
			$integration_registry->register( new WPFactory_WC_EU_VAT_Blocks_Integration() );
		}
	);

	if ( function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => CartSchema::IDENTIFIER,
				'namespace'       => 'eu-vat-for-woocommerce-block-example',
				'data_callback'   => function () {
					return array(
						'billing_eu_vat_number' => '',
						'wpfactory_eu_vat_validation' => wpfactory_wc_eu_vat_session_get( 'wpfactory_eu_vat_validation' ),
					);
				},
				'schema_callback' => function () {
					return array(
						'billing_eu_vat_number' => array(
							'description' => __( 'EU VAT Number', 'eu-vat-for-woocommerce' ),
							'type'        => array( 'string', 'null' ),
							'readonly'    => true,
						),
						'wpfactory_eu_vat_validation' => array(
							'type'       => 'object',
							'properties' => array(),
						),
					);
				},
				'schema_type'     => ARRAY_A,
			)
		);
	}
} );

/**
 * Registers the slug as a block category with WordPress.
 *
 * @version 4.2.2
 */
add_action( 'block_categories_all', function ( $categories ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'eu-vat-for-woocommerce',
				'title' => __( 'EU VAT Number Blocks', 'eu-vat-for-woocommerce' ),
			),
		)
	);
}, 10, 2 );
