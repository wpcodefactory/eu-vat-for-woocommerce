<?php
/**
 * EU VAT for WooCommerce - Section Settings
 *
 * @version 4.2.3
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Settings_Section' ) ) :

class Alg_WC_EU_VAT_Settings_Section {

	/**
	 * id.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	public $id;

	/**
	 * desc.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	public $desc;

	/**
	 * Constructor.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function __construct() {
		add_filter( 'woocommerce_get_sections_alg_wc_eu_vat',              array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_alg_wc_eu_vat_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
	}

	/**
	 * settings_section.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

	/**
	 * get_all_user_roles.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function get_all_user_roles() {
		global $wp_roles;
		$guest_role = array(
			'guest' => array(
				'name'         => __( 'Guest', 'eu-vat-for-woocommerce' ),
				'capabilities' => array(),
			),
		);
		$all_roles = array_merge(
			$guest_role,
			apply_filters(
				'editable_roles',
				(
					isset( $wp_roles ) && is_object( $wp_roles ) ?
					$wp_roles->roles :
					array()
				)
			)
		);
		return wp_list_pluck( $all_roles, 'name' );
	}

}

endif;
