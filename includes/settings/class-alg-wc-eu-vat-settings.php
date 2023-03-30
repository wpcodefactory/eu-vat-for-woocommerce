<?php
/**
 * EU VAT for WooCommerce - Settings
 *
 * @version 1.2.1
 * @since   1.0.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_EU_VAT_Settings' ) ) :

class Alg_WC_EU_VAT_Settings extends WC_Settings_Page {

	/**
	 * Constructor.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id    = 'alg_wc_eu_vat';
		$this->label = __( 'EU VAT', 'eu-vat-for-woocommerce' );
		parent::__construct();
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'maybe_unsanitize_option' ), PHP_INT_MAX, 3 );
	}

	/**
	 * maybe_unsanitize_option.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function maybe_unsanitize_option( $value, $option, $raw_value ) {
		return ( ! empty( $option['alg_wc_eu_vat_raw'] ) ? $raw_value : $value );
	}

	/**
	 * get_settings.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function get_settings() {
		global $current_section;
		$initialarray = array(
			array(
				'title'    => __( '', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'desc'	   => apply_filters( 'alg_wc_eu_vat_advertise' , '<div class="alg_wc_eu_vat_right_ad">
				<div class="alg_wc_eu_vat-sidebar__section">
				<div class="alg_wc_eu_vat_name_heading">
				<img class="alg_wc_eu_vat_resize" src="https://wpfactory.com/wp-content/uploads/EU-VAT-for-WooCommerce-300x300.png">
				<p class="alg_wc_eu_vat_text">Enjoying the plugin? Unleash its full potential with the premium version, it allows you to:</p>
				</div>
				<ul>
					<li>
						<strong>Show the VAT field for specific countries of your choice.</strong>
					</li>
					<li>
						<strong>Keep VAT in your store country EVEN if number is validated.</strong>
					</li>
					<li>
						<strong>Match company name along with VAT number.</strong>
					</li>
				</ul>
				<p style="text-align:center">
				<a id="alg_wc_eu_vat-premium-button" class="alg_wc_pq-button-upsell" href="https://wpfactory.com/item/eu-vat-for-woocommerce/" target="_blank">Get EU VAT for WooCommerce Pro</a>
				</p>
				<br>
			</div>
			</div>'),
				'id'       => $this->id . '_' . $current_section . '_options_ad_section',
			)
		);
		$return = array_merge( apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() ), array(
			array(
				'title'     => __( 'Reset Settings', 'eu-vat-for-woocommerce' ),
				'type'      => 'title',
				'id'        => $this->id . '_' . $current_section . '_reset_options',
			),
			array(
				'title'     => __( 'Reset section settings', 'eu-vat-for-woocommerce' ),
				'desc'      => '<strong>' . __( 'Reset', 'eu-vat-for-woocommerce' ) . '</strong>',
				'id'        => $this->id . '_' . $current_section . '_reset',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'type'      => 'sectionend',
				'id'        => $this->id . '_' . $current_section . '_reset_options',
			),
		) );
		
		$return = array_merge($initialarray, $return);
		
		return $return;
	}

	/**
	 * maybe_reset_settings.
	 *
	 * @version 1.2.1
	 * @since   1.0.0
	 */
	function maybe_reset_settings() {
		global $current_section;
		if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {
			foreach ( $this->get_settings() as $value ) {
				if ( isset( $value['id'] ) ) {
					$id = explode( '[', $value['id'] );
					delete_option( $id[0] );
				}
			}
			add_action( 'admin_notices', array( $this, 'admin_notice_settings_reset' ) );
		}
	}

	/**
	 * admin_notice_settings_reset.
	 *
	 * @version 1.2.1
	 * @since   1.2.1
	 */
	function admin_notice_settings_reset() {
		echo '<div class="notice notice-warning is-dismissible"><p><strong>' .
			__( 'Your settings have been reset.', 'eu-vat-for-woocommerce' ) . '</strong></p></div>';
	}

	/**
	 * Save settings.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function save() {
		parent::save();
		$this->maybe_reset_settings();
	}

}

endif;

return new Alg_WC_EU_VAT_Settings();
