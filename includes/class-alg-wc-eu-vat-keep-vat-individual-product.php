<?php
/**
 * EU VAT for WooCommerce - Keep VAT for Individual Product
 *
 * @version 4.0.0
 * @since   4.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Keep_VAT_Individual_Product' ) ) :

class Alg_WC_EU_VAT_Keep_VAT_Individual_Product {

	/**
	 * Constructor.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function __construct() {
		// Hook into 'init' to ensure proper loading order
		add_action( 'init', array( $this, 'init_hooks' ) );
	}

	/**
	 * Init hooks.
	 *
	 * @version 3.0.1
	 * @since   3.0.1
	 */
	function init_hooks() {
		add_action( 'woocommerce_product_options_tax', array( $this, 'add_keep_vat_individual_product' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_keep_vat_individual_product' ) );
		add_action( 'alg_wc_eu_vat_exempt_applied', array( $this, 'handle_keep_vat_individual_product' ) );
	}

	/**
	 * Adds a checkbox to keep VAT for an individual product.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function add_keep_vat_individual_product() {
		// Add a checkbox below the tax options
		woocommerce_wp_checkbox(
			array(
				'id'              => '_alg_wc_eu_vat_keep_vat',
				'label'           => esc_html__( 'Keep VAT for this product', 'eu-vat-for-woocommerce' ),
				'description'     => esc_html__( 'Enable this to ensure VAT is charged on this product, even if the customer provides a valid VAT number.', 'eu-vat-for-woocommerce' ),
				'desc_tip'        => true,
				'unchecked_value' => 'no',
			)
		);
	}

	/**
	 * Saves a checkbox to keep VAT for an individual product.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function save_keep_vat_individual_product( $product ) {
		if ( isset( $_POST['_alg_wc_eu_vat_keep_vat'] ) ) {
			$product->update_meta_data(
				'_alg_wc_eu_vat_keep_vat',
				wc_clean( wp_unslash( $_POST['_alg_wc_eu_vat_keep_vat'] ) )
			);
		}
	}

	/**
	 * Handles the VAT exemption for individual products based on the "Keep VAT" checkbox.
	 *
	 * @version 4.0.0
	 * @since   3.0.0
	 */
	function handle_keep_vat_individual_product( $is_exempt ) {

		if ( ! did_action( 'wp_loaded' ) ) {
			return;
		}

		if ( ! $is_exempt ) {
			return;
		}

		$vat_required_products = array();

		// Check if any products have the "Keep VAT" option enabled
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product_id = $cart_item['product_id'];

			$do_keep_vat = get_post_meta( $product_id, '_alg_wc_eu_vat_keep_vat', true );

			if ( 'yes' === $do_keep_vat ) {
				$vat_required_products[] = $product_id;
			}
		}

		// If there are any VAT-required products, keep VAT for the customer
		if ( ! empty( $vat_required_products ) ) {
			WC()->customer->set_is_vat_exempt( false ); // Disable VAT exemption for the customer

			foreach ( WC()->cart->get_cart() as $cart_item ) {
				$product_id = $cart_item['product_id'];

				// If the product doesn't require VAT, set its tax status to "None"
				if ( ! in_array( $product_id, $vat_required_products ) ) {
					$product             = $cart_item['data'];
					$price_excluding_tax = wc_get_price_excluding_tax( $product );
					$product->set_price( $price_excluding_tax );
					$product->set_tax_status( 'none' );
				}
			}
		}

	}

}

endif;

return new Alg_WC_EU_VAT_Keep_VAT_Individual_Product();
