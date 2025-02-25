<?php
/**
 * EU VAT for WooCommerce - Settings
 *
 * @version 4.3.1
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Settings' ) ) :

class Alg_WC_EU_VAT_Settings extends WC_Settings_Page {

	/**
	 * Constructor.
	 *
	 * @version 4.2.3
	 * @since   1.0.0
	 */
	function __construct() {

		$this->id    = 'alg_wc_eu_vat';
		$this->label = __( 'EU VAT', 'eu-vat-for-woocommerce' );
		parent::__construct();

		// Sections
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-settings-section.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-settings-general.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-settings-validation.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-settings-progress.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-settings-admin.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-settings-advanced.php';

		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'maybe_unsanitize_option' ), PHP_INT_MAX, 3 );

		add_action( 'admin_footer', array( $this, 'add_js_admin_field_control' ) );

	}

	/**
	 * add_js_admin_field_control.
	 *
	 * @version 4.0.0
	 *
	 * @todo    (dev) add `toogle_customer_decide()`?
	 */
	function add_js_admin_field_control() {
		?>
		<script>
			jQuery( document ).ready( function () {
				function toogle_required_countries_field() {
					switch ( jQuery( '#alg_wc_eu_vat_field_required' ).val() ) {
						case 'yes_for_countries':
						case 'no_for_countries':
							jQuery( '#alg_wc_eu_vat_field_required_countries' ).removeAttr( 'disabled' );
							break;
						default:
							jQuery( '#alg_wc_eu_vat_field_required_countries' ).attr( 'disabled', 'disabled' );
					}
				}
				toogle_required_countries_field();
				jQuery( '#alg_wc_eu_vat_field_required' ).change( toogle_required_countries_field );
			} );
		</script>
		<?php
	}

	/**
	 * maybe_unsanitize_option.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function maybe_unsanitize_option( $value, $option, $raw_value ) {
		return (
			! empty( $option['alg_wc_eu_vat_raw'] ) ?
			$raw_value :
			$value
		);
	}

	/**
	 * get_settings.
	 *
	 * @version 4.3.1
	 * @since   1.0.0
	 */
	function get_settings() {
		global $current_section;
		$settings = array_merge(
			apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() ),
			array(
				array(
					'title'     => __( 'Reset Settings', 'eu-vat-for-woocommerce' ),
					'type'      => 'title',
					'id'        => $this->id . '_' . $current_section . '_reset_options',
				),
				array(
					'title'     => __( 'Reset section settings', 'eu-vat-for-woocommerce' ),
					'desc'      => '<strong>' . __( 'Reset', 'eu-vat-for-woocommerce' ) . '</strong>',
					'desc_tip'  => __( 'Check the box and save changes to reset.', 'eu-vat-for-woocommerce' ),
					'id'        => $this->id . '_' . $current_section . '_reset',
					'default'   => 'no',
					'type'      => 'checkbox',
				),
				array(
					'type'      => 'sectionend',
					'id'        => $this->id . '_' . $current_section . '_reset_options',
				),
			)
		);
		return apply_filters( 'alg_wc_eu_vat_get_settings', $settings, $current_section, $this->id, $this );
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
	 * @version 4.0.0
	 * @since   1.2.1
	 */
	function admin_notice_settings_reset() {
		echo '<div class="notice notice-warning is-dismissible"><p><strong>' .
			esc_html__( 'Your settings have been reset.', 'eu-vat-for-woocommerce' ) .
		'</strong></p></div>';
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
