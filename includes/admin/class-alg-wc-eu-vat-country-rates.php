<?php
/**
 * EU VAT for WooCommerce - Tool - EU country VAT Rates
 *
 * @version 1.7.0
 * @since   1.0.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Countries_VAT_Rates_Tool' ) ) :

class Alg_WC_EU_VAT_Countries_VAT_Rates_Tool {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @todo    [dev] (maybe) add option to disable the tool
	 */
	function __construct() {
		add_action( 'admin_menu', array( $this, 'add_eu_countries_vat_rates_tool' ) );
		add_action( 'admin_init', array( $this, 'add_eu_countries_vat_rates' ) );
	}

	/**
	 * get_rates_for_tax_class.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_rates_for_tax_class( $tax_class ) {
		global $wpdb;

		// Get all the rates and locations. Snagging all at once should significantly cut down on the number of queries.
		$rates     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rates` WHERE `tax_rate_class` = %s ORDER BY `tax_rate_order`;",
			sanitize_title( $tax_class ) ) );
		$locations = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rate_locations`" );

		// Set the rates keys equal to their ids.
		$rates = array_combine( wp_list_pluck( $rates, 'tax_rate_id' ), $rates );

		// Drop the locations into the rates array.
		foreach ( $locations as $location ) {
			// Don't set them for unexistent rates.
			if ( ! isset( $rates[ $location->tax_rate_id ] ) ) {
				continue;
			}
			// If the rate exists, initialize the array before appending to it.
			if ( ! isset( $rates[ $location->tax_rate_id ]->{$location->location_type} ) ) {
				$rates[ $location->tax_rate_id ]->{$location->location_type} = array();
			}
			$rates[ $location->tax_rate_id ]->{$location->location_type}[] = $location->location_code;
		}

		return $rates;
	}

	/**
	 * get_european_union_countries_with_vat.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @todo    [dev] (maybe) check `MC`, `IM`
	 */
	function get_european_union_countries_with_vat() {
		return array(
			'AT' => 20,
			'BE' => 21,
			'BG' => 20,
			'CY' => 19,
			'CZ' => 21,
			'DE' => 19,
			'DK' => 25,
			'EE' => 20,
			'ES' => 21,
			'FI' => 24,
			'FR' => 20,
			'GB' => 20,
			'GR' => 24,
			'HU' => 27,
			'HR' => 25,
			'IE' => 23,
			'IT' => 22,
			'LT' => 21,
			'LU' => 17,
			'LV' => 21,
			'MT' => 18,
			'NL' => 21,
			'PL' => 23,
			'PT' => 23,
			'RO' => 19,
			'SE' => 25,
			'SI' => 22,
			'SK' => 20,
		);
	}

	/**
	 * add_eu_countries_vat_rates.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 */
	function add_eu_countries_vat_rates() {
		if ( ! isset( $_POST['add_eu_countries_vat_rates'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$loop = 0;
		foreach ( $this->get_european_union_countries_with_vat() as $country => $rate ) {
			if ( WC_Tax::find_rates( array( 'country' => $country ) ) ) {
				continue;
			}
			$tax_rate = array(
				'tax_rate_country'  => $country,
				'tax_rate'          => $rate,

				'tax_rate_name'     => isset( $_POST['alg_wc_eu_vat_tax_name'] ) ? $_POST['alg_wc_eu_vat_tax_name'] : __( 'VAT', 'woocommerce' ),
				'tax_rate_priority' => 1,
				'tax_rate_compound' => 0,
				'tax_rate_shipping' => 1,

				'tax_rate_order'    => $loop++,
				'tax_rate_class'    => '',
			);
			$tax_rate_id = WC_Tax::_insert_tax_rate( $tax_rate );
			WC_Tax::_update_tax_rate_postcodes( $tax_rate_id, '' );
			WC_Tax::_update_tax_rate_cities( $tax_rate_id, '' );
		}
		add_action( 'admin_notices', array( $this, 'success_notice' ) );
	}

	/**
	 * success_notice.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function success_notice() {
		$class = 'notice notice-info';
		$message = __( 'EU country VAT rates were successfully added.', 'eu-vat-for-woocommerce' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	/**
	 * add_eu_countries_vat_rates_tool.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_eu_countries_vat_rates_tool() {
		add_submenu_page(
			'tools.php',
			__( 'EU country VAT Rates', 'eu-vat-for-woocommerce' ),
			__( 'EU country VAT Rates', 'eu-vat-for-woocommerce' ),
			'manage_woocommerce',
			'alg-wc-eu-vat-country-rates',
			array( $this, 'create_eu_countries_vat_rates_tool' )
		);
	}

	/**
	 * create_eu_countries_vat_rates_tool.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 */
	function create_eu_countries_vat_rates_tool() {
		$header_html  = '';
		$header_html .= '<h2>' . __( 'EU country VAT Rates Tool', 'eu-vat-for-woocommerce' ) . '</h2>';
		$header_html .= '<h3>' . __( 'Add all EU country VAT standard rates to WooCommerce.', 'eu-vat-for-woocommerce' ) . '</h3>';
		$header_html .= '<p><em>' . '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_eu_vat' ) . '">' .
			__( 'Plugin settings', 'eu-vat-for-woocommerce' ) . '</a>' . '</em></p>';

		$the_tool_html  = '';
		$the_tool_html .= $header_html;

		$data = array();
		$the_name = ( isset( $_POST['alg_wc_eu_vat_tax_name'] ) ? $_POST['alg_wc_eu_vat_tax_name'] : __( 'VAT', 'woocommerce' ) );
		$data[] = array(
			__( 'Name', 'eu-vat-for-woocommerce' ) . '<br>' .
				'<input class="input-text" type="text" name="alg_wc_eu_vat_tax_name" value="' . $the_name . '">',
			'<em>' . __( 'Note: will not add duplicates for country.', 'eu-vat-for-woocommerce' ) . '</em>' . '<br>' .
				'<input class="button-primary" type="submit" name="add_eu_countries_vat_rates" value="' . __( 'Add EU country VAT Rates', 'eu-vat-for-woocommerce' ) . '">',
		);
		$the_tool_html .= '<p>';
		$the_tool_html .= '<form method="post" action="">';
		$the_tool_html .=  alg_wc_eu_vat_get_table_html( $data, array( 'table_heading_type' => 'none', 'table_class' => 'widefat', 'table_style' => 'width:50%;min-width:300px;' ) );
		$the_tool_html .= '</form>';
		$the_tool_html .= '</p>';

		$the_tool_html .= '<h4>' . __( 'List of EU VAT rates to be added', 'eu-vat-for-woocommerce' ) . '</h4>';
		$eu_vat_rates = $this->get_european_union_countries_with_vat();
		$data = array();
		$data[] = array(
			'#',
			__( 'Country', 'eu-vat-for-woocommerce' ),
			__( 'Rate', 'eu-vat-for-woocommerce' ),
		);
		$i = 1;
		foreach ( $eu_vat_rates as $country => $rate ) {
			$data[] = array( $i++, $country . ' - ' . alg_wc_eu_vat_get_country_name_by_code( $country ), $rate . '%' );
		}
		$the_tool_html .= alg_wc_eu_vat_get_table_html( $data, array( 'table_class' => 'widefat', 'table_style' => 'width:50%;min-width:300px;' ) );

		$the_tool_html .= '<h4>' . __( 'Current standard tax rates', 'eu-vat-for-woocommerce' ) . '</h4>';
		$standard_tax_rates = $this->get_rates_for_tax_class( '' );
		$data = array();
		$data[] = array(
			'',
			__( 'Country', 'eu-vat-for-woocommerce' ),
			__( 'Rate', 'eu-vat-for-woocommerce' ),
			__( 'Name', 'eu-vat-for-woocommerce' ),
		);
		$i = 1;
		foreach ( $standard_tax_rates as $tax_rate_object ) {
			$data[] = array( $i++, $tax_rate_object->tax_rate_country . ' - ' . alg_wc_eu_vat_get_country_name_by_code( $tax_rate_object->tax_rate_country ),
				$tax_rate_object->tax_rate . '%', $tax_rate_object->tax_rate_name );
		}
		$the_tool_html .= alg_wc_eu_vat_get_table_html( $data, array( 'table_class' => 'widefat', 'table_style' => 'width:75%;min-width:300px;' ) );

		echo '<div class="wrap">' . $the_tool_html . '</div>';
	}
}

endif;

return new Alg_WC_EU_VAT_Countries_VAT_Rates_Tool();
