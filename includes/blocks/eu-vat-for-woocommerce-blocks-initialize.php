<?php
/**
 * EU VAT for WooCommerce - Blocks Initialize
 *
 * @version 4.0.0
 *
 * @author  WPFactory
 */

use Automattic\WooCommerce\StoreApi\StoreApi;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;

defined( 'ABSPATH' ) || exit;

/**
 * woocommerce_blocks_loaded.
 *
 * @version 4.0.0
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
				'data_callback'   => 'eu_vat_for_woocommerce_data_callback',
				'schema_callback' => 'eu_vat_for_woocommerce_schema_callback',
				'schema_type'     => ARRAY_A,
			)
		);
	}
} );

/**
 * Callback function to register endpoint data for blocks.
 *
 * @version 3.2.1
 *
 * @return  array
 */
if ( ! function_exists( 'eu_vat_for_woocommerce_data_callback' ) ) {
	function eu_vat_for_woocommerce_data_callback() {
		return array(
			'billing_eu_vat_number' => '',
		);
	}
}

/**
 * Callback function to register schema for data.
 *
 * @version 3.2.1
 *
 * @return  array
 */
if ( ! function_exists( 'eu_vat_for_woocommerce_schema_callback' ) ) {
	function eu_vat_for_woocommerce_schema_callback() {
		return array(
			'billing_eu_vat_number' => array(
				'description' => __( 'EU VAT Number', 'eu-vat-for-woocommerce' ),
				'type'        => array( 'string', 'null' ),
				'readonly'    => true,
			),
		);
	}
}

/**
 * Registers the slug as a block category with WordPress.
 *
 * @version 3.2.1
 */
if ( ! function_exists( 'register_EuVatForWoocommerce_block_category' ) ) {
	function register_EuVatForWoocommerce_block_category( $categories ) {
		return array_merge(
			$categories,
			[
				[
					'slug'  => 'eu-vat-for-woocommerce',
					'title' => __( 'EU VAT Number Blocks', 'eu-vat-for-woocommerce' ),
				],
			]
		);
	}
}
add_action( 'block_categories_all', 'register_EuVatForWoocommerce_block_category', 10, 2 );
