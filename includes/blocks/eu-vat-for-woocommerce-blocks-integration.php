<?php
/**
 * EU VAT for WooCommerce - Class for Integrating with WooCommerce Blocks
 *
 * @version 4.5.6
 * @since   2.11.0
 *
 * @author  WPFactory
 */

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

defined( 'ABSPATH' ) || exit;

class EuVatForWoocommerce_Blocks_Integration implements IntegrationInterface {

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'eu-vat-for-woocommerce';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 *
	 * @version 4.5.6
	 */
	public function initialize() {
		$this->register_euvat_block_frontend_scripts();
		$this->register_euvat_block_editor_scripts();
		$this->register_euvat_block_editor_styles();

		add_action(
			'wp_enqueue_scripts',
			array( $this, 'eu_vat_country_enqueue_script' )
		);
	}

	/**
	 * Enqueue country array.
	 */
	public function eu_vat_country_enqueue_script() {

		$wc_countries      = new WC_Countries();
		$countries         = $wc_countries->get_countries();
		$flipped_countries = array_flip( $countries );

		wp_localize_script(
			'eu-vat-for-woocommerce-checkout-eu-vat-field-block-frontend',
			'alg_wc_eu_frontend_countries_object',
			$flipped_countries
		);
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @version 4.5.6
	 */
	public function get_script_handles() {
		return array( 'eu-vat-for-woocommerce-checkout-eu-vat-field-block-frontend' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @version 4.5.6
	 */
	public function get_editor_script_handles() {
		return array( 'eu-vat-for-woocommerce-checkout-eu-vat-field-block-editor' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @version 4.5.6
	 */
	public function get_script_data() {
		return array(
			'eu-vat-for-woocommerce-active'   => true,
			'alg_wc_eu_vat_field_id'          => '#contact-alg_eu_vat-billing_eu_vat_number',
			'alg_wc_eu_vat_field_position_id' => apply_filters(
				'alg_wc_eu_vat_field_position_block_checkout',
				''
			),
			'optInDefaultText'                => __( 'I want to receive updates about products and promotions.', 'eu-vat-for-woocommerce' ),
		);
	}

	/**
	 * register_euvat_block_editor_styles.
	 *
	 * @version 4.5.6
	 * @since   4.0.0
	 */
	public function register_euvat_block_editor_styles() {
		$style_path = '/build/checkout-eu-vat-field-block/style-index.css';
		$style_url  = plugins_url( $style_path, __FILE__ );
		wp_enqueue_style(
			'eu-vat-for-woocommerce-checkout-eu-vat-field-block',
			$style_url,
			[],
			ALG_WC_EU_VAT_VERSION
		);
	}

	/**
	 * register_euvat_block_editor_scripts.
	 *
	 * @version 4.5.6
	 * @since   4.0.0
	 */
	public function register_euvat_block_editor_scripts() {

		$script_path       = '/build/checkout-eu-vat-field-block/index.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = (
			dirname( __FILE__ ) .
			'/build/checkout-eu-vat-field-block/eu-vat-for-woocommerce-checkout-eu-vat-field-block.asset.php'
		);
		$script_asset      = (
			file_exists( $script_asset_path ) ?
			require $script_asset_path :
			array(
				'dependencies' => array(),
				'version'      => ALG_WC_EU_VAT_VERSION,
			)
		);

		wp_register_script(
			'eu-vat-for-woocommerce-checkout-eu-vat-field-block-editor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'eu-vat-for-woocommerce-eu-vat-field-block-editor',
			'eu-vat-for-woocommerce',
			dirname( __FILE__ ) . '/languages'
		);

	}

	/**
	 * register_euvat_block_frontend_scripts.
	 *
	 * @version 4.5.6
	 * @since   4.3.5
	 */
	public function register_euvat_block_frontend_scripts() {

		$script_path       = '/build/checkout-eu-vat-field-block/frontend.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = (
			dirname( __FILE__ ) .
			'/build/checkout-eu-vat-field-block/frontend.asset.php'
		);
		$script_asset      = (
		file_exists( $script_asset_path ) ?
			require $script_asset_path :
			array(
				'dependencies' => array(),
				'version'      => ALG_WC_EU_VAT_VERSION,
			)
		);

		if ( 'yes' === get_option( 'alg_wc_eu_vat_checkout_block_field_dependencies', 'no' ) ) {
			$script_asset['dependencies'][] = 'alg-wc-eu-vat';
		}

		wp_register_script(
			'eu-vat-for-woocommerce-checkout-eu-vat-field-block-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'eu-vat-for-woocommerce-checkout-eu-vat-field-block-frontend',
			'eu-vat-for-woocommerce',
			dirname( __FILE__ ) . '/languages'
		);
	}

}
