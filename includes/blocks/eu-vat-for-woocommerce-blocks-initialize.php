<?php
/**
 * EU VAT for WooCommerce - Blocks Initialize
 *
 * @version 4.2.4
 *
 * @author  WPFactory
 */

use Automattic\WooCommerce\StoreApi\StoreApi;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'alg_wc_eu_vat_is_checkout_block_enabled' ) ) {
	/**
	 * alg_wc_eu_vat_is_checkout_block_enabled.
	 *
	 * @version 4.2.4
	 * @since   4.2.4
	 */
	function alg_wc_eu_vat_is_checkout_block_enabled() {
		return (
			'yes' === get_option( 'alg_wc_eu_vat_enable_checkout_block_field', 'no' ) &&
			'no' === get_option( 'alg_wc_eu_vat_hide_eu_vat', 'no' ) &&
			version_compare( get_option( 'woocommerce_version', null ), '8.9.1', '>=' )
		);
	}
}

/**
 * Check if checkout block is enabled.
 *
 * @version 4.2.4
 * @since   4.2.4
 */
if ( ! alg_wc_eu_vat_is_checkout_block_enabled() ) {
	return;
}

/**
 * woocommerce_blocks_loaded.
 *
 * @version 4.2.2
 */
add_action( 'woocommerce_blocks_loaded', function () {

	require_once plugin_dir_path( __FILE__ ) . 'eu-vat-for-woocommerce-blocks-integration.php';

	add_action(
		'woocommerce_blocks_cart_block_registration',
		function ( $integration_registry ) {
			$integration_registry->register( new EuVatForWoocommerce_Blocks_Integration() );
		}
	);

	add_action(
		'woocommerce_blocks_checkout_block_registration',
		function ( $integration_registry ) {
			$integration_registry->register( new EuVatForWoocommerce_Blocks_Integration() );
		}
	);

	if ( function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => CheckoutSchema::IDENTIFIER,
				'namespace'       => 'eu-vat-for-woocommerce-block-example',
				'data_callback'   => function () {
					return array(
						'billing_eu_vat_number' => '',
					);
				},
				'schema_callback' => function () {
					return array(
						'billing_eu_vat_number' => array(
							'description' => __( 'EU VAT Number', 'eu-vat-for-woocommerce' ),
							'type'        => array( 'string', 'null' ),
							'readonly'    => true,
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
