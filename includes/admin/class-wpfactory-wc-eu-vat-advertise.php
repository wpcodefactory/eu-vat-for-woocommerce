<?php
/**
 * EU VAT for WooCommerce - Advertise
 *
 * @version 4.7.0
 * @since   4.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_EU_VAT_Advertise' ) ) :

class WPFactory_WC_EU_VAT_Advertise {

	/**
	 * Constructor.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function __construct() {
		add_filter( 'alg_wc_eu_vat_get_settings', array( $this, 'add_advertisement' ), 10, 3 );
		add_action( 'admin_footer', array( $this, 'add_css' ) );
	}

	/**
	 * add_advertisement.
	 *
	 * @version 4.7.0
	 * @since   4.0.0
	 */
	function add_advertisement( $settings, $current_section, $settings_page_id ) {

		ob_start();
		?>
		<div class="wpfactory_wc_eu_vat_right_ad">
			<div class="wpfactory_wc_eu_vat-sidebar__section">
				<div class="wpfactory_wc_eu_vat_name_heading">
					<img class="wpfactory_wc_eu_vat_resize" src="<?php echo esc_url( plugins_url( 'assets/images/EU-VAT-for-WooCommerce-300x300.png', WPFACTORY_WC_EU_VAT_FILE ) ); ?>">
					<p class="wpfactory_wc_eu_vat_text">Enjoying the plugin? Unleash its full potential with the premium version, it allows you to:</p>
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
					<a id="wpfactory_wc_eu_vat-premium-button" class="wpfactory_wc_pq-button-upsell" href="https://wpfactory.com/item/eu-vat-for-woocommerce/" target="_blank">Get EU/UK VAT Validation Manager for WooCommerce Pro</a>
				</p>
				<br>
			</div>
		</div>
		<?php
		$advertisement = ob_get_clean();

		return array_merge(
			array(
				array(
					'title' => '',
					'type'  => 'title',
					'desc'  => apply_filters( 'alg_wc_eu_vat_advertise' , $advertisement ),
					'id'    => $settings_page_id . '_' . $current_section . '_options_ad_section',
				)
			),
			$settings
		);

	}

	/**
	 * add_css.
	 *
	 * @version 4.7.0
	 */
	function add_css() {
		?>
		<style>
			.wpfactory_wc_eu_vat_name_heading {
				position: relative;
			}
			.wpfactory_wc_eu_vat_right_ad {
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
			.wpfactory_wc_eu_vat-button-upsell {
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
			.wpfactory_wc_eu_vat-button-upsell:hover {
				background-color:#7ce577;
				color:#000;
				font-weight: 600;
			}
			.wpfactory_wc_eu_vat-sidebar__section li:before {
				content:"+";
				position:absolute;
				left:0;
				font-weight:700
			}
			.wpfactory_wc_eu_vat-sidebar__section li {
				list-style:none;
				margin-left:20px
			}
			.wpfactory_wc_eu_vat-sidebar__section {
				position: relative;
			}
			img.wpfactory_wc_eu_vat_resize {
				width: 60px;
				float: right;
				position: absolute;
				right: 0px;
				top: -15px;
				padding-left: 10px;
			}
			.wpfactory_wc_eu_vat_text {
				margin-right: 18%;
			}
		</style>
		<?php
	}

}

endif;

return new WPFactory_WC_EU_VAT_Advertise();
