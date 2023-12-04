<?php
/*
Plugin Name: EU/UK VAT for WooCommerce
Plugin URI: https://wpfactory.com/item/eu-vat-for-woocommerce/
Description: Manage EU VAT in WooCommerce. Beautifully.
Version: 2.9.18
Author: WPFactory
Author URI: https://wpfactory.com/
Text Domain: eu-vat-for-woocommerce
Domain Path: /langs
Copyright: © 2023 WPFactory
WC tested up to: 8.3
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_EU_VAT' ) ) :

/**
 * Main Alg_WC_EU_VAT Class
 *
 * @class   Alg_WC_EU_VAT
 * @version 2.9.17
 * @since   1.0.0
 */
final class Alg_WC_EU_VAT {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '2.9.17';
	public $core = null;
	public $settings = array();

	/**
	 * @var   Alg_WC_EU_VAT The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_EU_VAT Instance
	 *
	 * Ensures only one instance of Alg_WC_EU_VAT is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @static
	 * @return  Alg_WC_EU_VAT - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_EU_VAT Constructor.
	 *
	 * @version 1.7.1
	 * @since   1.0.0
	 * @access  public
	 */
	function __construct() {

		// Check for active plugins
		if (
			! $this->is_plugin_active( 'woocommerce/woocommerce.php' ) ||
			( 'eu-vat-for-woocommerce.php' === basename( __FILE__ ) && $this->is_plugin_active( 'eu-vat-for-woocommerce-pro/eu-vat-for-woocommerce-pro.php' ) )
		) {
			return;
		}

		// For debug
		require_once( 'includes/functions/alg-wc-eu-vat-functions-debug.php' );

		// Set up localisation
		load_plugin_textdomain( 'eu-vat-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// Pro
		if ( 'eu-vat-for-woocommerce-pro.php' === basename( __FILE__ ) ) {
			require_once( 'includes/pro/class-alg-wc-eu-vat-pro.php' );
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

	}

	/**
	 * is_plugin_active.
	 *
	 * @version 1.7.1
	 * @since   1.7.1
	 */
	function is_plugin_active( $plugin ) {
		return ( function_exists( 'is_plugin_active' ) ? is_plugin_active( $plugin ) :
			(
				in_array( $plugin, apply_filters( 'active_plugins', ( array ) get_option( 'active_plugins', array() ) ) ) ||
				( is_multisite() && array_key_exists( $plugin, ( array ) get_site_option( 'active_sitewide_plugins', array() ) ) )
			)
		);
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 */
	function includes() {
		// Functions
		require_once( 'includes/functions/alg-wc-eu-vat-functions-general.php' );
		// Core
		$this->core = require_once( 'includes/class-alg-wc-eu-vat-core.php' );
	}

	/**
	 * admin.
	 *
	 * @version 1.5.0
	 * @since   1.2.0
	 */
	function admin() {
		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		require_once( 'includes/settings/class-alg-wc-eu-vat-settings-section.php' );
		$this->settings = array();
		$this->settings['general']    = require_once( 'includes/settings/class-alg-wc-eu-vat-settings-general.php' );
		$this->settings['validation'] = require_once( 'includes/settings/class-alg-wc-eu-vat-settings-validation.php' );
		$this->settings['admin']      = require_once( 'includes/settings/class-alg-wc-eu-vat-settings-admin.php' );
		// Rates tool
		require_once( 'includes/admin/class-alg-wc-eu-vat-country-rates.php' );
		// Version update
		if ( get_option( 'alg_wc_eu_vat_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_eu_vat' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		if ( 'eu-vat-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">' .
				__( 'Unlock All', 'eu-vat-for-woocommerce' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Add EU VAT settings tab to WooCommerce settings.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once( 'includes/settings/class-alg-wc-eu-vat-settings.php' );
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function version_updated() {
		update_option( 'alg_wc_eu_vat_version', $this->version );
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

endif;

if ( ! function_exists( 'alg_wc_eu_vat' ) ) {
	/**
	 * Returns the main instance of Alg_WC_EU_VAT to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_EU_VAT
	 */
	function alg_wc_eu_vat() {
		return Alg_WC_EU_VAT::instance();
	}
}

alg_wc_eu_vat();

if ( ! function_exists( 'alg_wc_eu_vat_admin_js_field_control' ) ) {
	function alg_wc_eu_vat_admin_js_field_control() {
		?>
		<script>
		jQuery(document).ready(function() {
			
			var eu_vat_required = jQuery('#alg_wc_eu_vat_field_required');
			
			/*
			if (eu_vat_required.is(':checked')) {
				toogle_customer_decide(2);
			}else{
				toogle_customer_decide(1);
			}
			*/
			if(eu_vat_required.val() == 'yes_for_countries'){
				toogle_required_countries(2);
			}else if(eu_vat_required.val() == 'no_for_countries'){
				toogle_required_countries(2);
			}else{
				toogle_required_countries(1);
			}
			
			eu_vat_required.change(function() {
				
				/*
				if (jQuery(this).is(':checked')) {
					toogle_customer_decide(2);
				}else{
					toogle_customer_decide(1);
				}
				*/
				
				if(jQuery(this).val() == 'yes_for_countries'){
					toogle_required_countries(2);
				}else if(jQuery(this).val() == 'no_for_countries'){
					toogle_required_countries(2);
				}else{
					toogle_required_countries(1);
				}
				
			});
			
			
		});	
		
		function toogle_customer_decide(flag = 1) {
			var customer_decide = jQuery('#alg_wc_eu_vat_field_let_customer_decide');
			var customer_decide_label = jQuery('#alg_wc_eu_vat_field_let_customer_decide_label');
			
			if(flag==1){
				//customer_decide.closest('tr').hide();
				//customer_decide_label.closest('tr').hide();
				customer_decide.attr('disabled', 'disabled');
				customer_decide_label.attr('disabled', 'disabled');
			}else{
				//customer_decide.closest('tr').show();
				//customer_decide_label.closest('tr').show();
				customer_decide.removeAttr('disabled');
				customer_decide_label.removeAttr('disabled');
			}
		}
		
		function toogle_required_countries(flag = 1) {
			var field_required_countries = jQuery('#alg_wc_eu_vat_field_required_countries');
			
			if(flag==1){
				field_required_countries.attr('disabled', 'disabled');
			}else{
				field_required_countries.removeAttr('disabled');
			}
		}
		</script>
		<?php 
	}
}
add_action('admin_footer', 'alg_wc_eu_vat_admin_js_field_control');

add_action( 'wp_ajax_alg_wc_eu_vat_update_closedate', 'alg_wc_eu_vat_update_closedate' );
add_action( 'wp_ajax_nopriv_alg_wc_eu_vat_update_closedate', 'alg_wc_eu_vat_update_closedate' );

if ( ! function_exists( 'alg_wc_eu_vat_update_closedate' ) ) {
	function alg_wc_eu_vat_update_closedate(){
		$user_id = get_current_user_id();
		if($user_id > 0){
			$phpdatetime  = time();
			update_user_meta($user_id, 'alg_wc_eu_vat_closedate', $phpdatetime);
		}
		echo "ok";
		die;
	}
}
	
if ( ! function_exists( 'alg_wc_eu_vat_admin_footer_js' ) ) {
	add_action('admin_footer', 'alg_wc_eu_vat_admin_footer_js');
	function alg_wc_eu_vat_admin_footer_js($data) {
		?>
			<script>
				jQuery(document).ready(function() {
					jQuery(".alg_wc_eu_vat_close").on('click', function(){
						var closeData = {
							'action'  : 'alg_wc_eu_vat_update_closedate'
						};

						jQuery.ajax({
							type   : 'POST',
							url    : <?php echo "'" . admin_url( 'admin-ajax.php' ) . "'"; ?>,
							data   : closeData,
							async  : true,
							success: function( response ) {
								if(response=='ok'){
									jQuery(".alg_wc_eu_vat_right_ad").remove();
								}
							},
						});
					});
				})
			</script>
			<style>
			.alg_wc_eu_vat_close{
				position: absolute;
				right:-13px;
				top: -26px;
				cursor: pointer;
				color: white;
				background: #000;
				width: 25px;
				height: 25px;
				text-align: center;
				border-radius: 50%;
				font-size: 32px;
			}
			
			.alg_wc_eu_vat_name_heading{
				position: relative;
			}
			.alg_wc_eu_vat_right_ad{
				position: absolute;
				right:20px;
				padding: 16px;
				box-shadow: 0 1px 6px 0 rgb(0 0 0 / 30%);
				border: 1px solid #dcdcdc;
				background-color: #fff;
				margin: 0px 0 20px;
				width: 25em;
				z-index: 99;
				font-weight: 600;
				border-radius: 10px;
				
			}
			.alg_wc_eu_vat-button-upsell{
				display:inline-flex;
				align-items:center;
				justify-content:center;
				box-sizing:border-box;
				min-height:48px;
				padding:8px 1em;
				font-size:16px;
				line-height:1.5;
				font-family:Arial,sans-serif;
				color:#000;
				border-radius:4px;
				box-shadow:inset 0 -4px 0 rgba(0,0,0,.2);
				filter:drop-shadow(0 2px 4px rgba(0,0,0,.2));
				text-decoration:none;
				background-color:#7ce577;
				font-weight: 600;
			}
			.alg_wc_eu_vat-button-upsell:hover{
				background-color:#7ce577;
				color:#000;
				font-weight: 600;
			}
			.alg_wc_eu_vat-sidebar__section li:before{
				content:"+";
				position:absolute;
				left:0;
				font-weight:700
			}
			.alg_wc_eu_vat-sidebar__section li{
				list-style:none;
				margin-left:20px
			}
			.alg_wc_eu_vat-sidebar__section{
				position: relative;
			}
			img.alg_wc_eu_vat_resize{
				width: 60px;
				float: right;
				position: absolute;
				right: 0px;
				top: -15px;
				padding-left: 10px;
			}
			.alg_wc_eu_vat_text{
				margin-right: 18%;
			}
			</style>
		<?php
	}
}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', dirname(__FILE__), true );
	}
} );

