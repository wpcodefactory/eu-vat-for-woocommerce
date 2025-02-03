<?php
/*
Plugin Name: EU/UK VAT Validation Manager for WooCommerce
Plugin URI: https://wpfactory.com/item/eu-vat-for-woocommerce/
Description: Manage EU VAT in WooCommerce. Beautifully.
Version: 4.2.4
Author: WPFactory
Author URI: https://wpfactory.com/
Text Domain: eu-vat-for-woocommerce
Domain Path: /langs
WC tested up to: 9.6
Requires Plugins: woocommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

if ( 'eu-vat-for-woocommerce.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 4.2.2
	 * @since   3.2.0
	 */
	$plugin = 'eu-vat-for-woocommerce-pro/eu-vat-for-woocommerce-pro.php';
	if (
		in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
		(
			is_multisite() &&
			array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) )
		)
	) {
		defined( 'ALG_WC_EU_VAT_FILE_FREE' ) || define( 'ALG_WC_EU_VAT_FILE_FREE', __FILE__ );
		return;
	}
}

defined( 'ALG_WC_EU_VAT_VERSION' ) || define( 'ALG_WC_EU_VAT_VERSION', '4.2.4' );

defined( 'ALG_WC_EU_VAT_FILE' ) || define( 'ALG_WC_EU_VAT_FILE', __FILE__ );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-alg-wc-eu-vat.php';

if ( ! function_exists( 'alg_wc_eu_vat' ) ) {
	/**
	 * Returns the main instance of Alg_WC_EU_VAT to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @return  Alg_WC_EU_VAT
	 */
	function alg_wc_eu_vat() {
		return Alg_WC_EU_VAT::instance();
	}
}

/**
 * plugins_loaded.
 *
 * @version 3.1.1
 */
add_action( 'plugins_loaded', 'alg_wc_eu_vat' );

/**
 * Load block.
 *
 * @version 4.0.0
 * @since   3.1.5
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/blocks/eu-vat-for-woocommerce-blocks-initialize.php';
