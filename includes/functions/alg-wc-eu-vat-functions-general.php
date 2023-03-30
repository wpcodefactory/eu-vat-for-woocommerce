<?php
/**
 * EU VAT for WooCommerce - Functions - General
 *
 * @version 1.7.1
 * @since   1.0.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'alg_wc_eu_vat_get_field_id' ) ) {
	/**
	 * alg_wc_eu_vat_get_field_id.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_eu_vat_get_field_id( $short = false ) {
		$field_id = 'eu_vat_number';
		return ( $short ? $field_id : 'billing_' . $field_id );
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_get_customers_location_by_ip' ) ) {
	/**
	 * alg_wc_eu_vat_get_customers_location_by_ip.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_eu_vat_get_customers_location_by_ip( $ip_address = '' ) {
		if ( class_exists( 'WC_Geolocation' ) ) {
			// Get the country by IP
			$location = WC_Geolocation::geolocate_ip( $ip_address );
			// Base fallback
			if ( empty( $location['country'] ) ) {
				$location = wc_format_country_state_string( apply_filters( 'woocommerce_customer_default_location', get_option( 'woocommerce_default_country' ) ) );
			}
			return ( isset( $location['country'] ) ? $location['country'] : '' );
		} else {
			return '';
		}
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_session_start' ) ) {
	/**
	 * alg_wc_eu_vat_session_start.
	 *
	 * @version 1.7.1
	 * @since   1.0.0
	 */
	function alg_wc_eu_vat_session_start() {
		if ( ! defined( 'ALG_WC_EU_VAT_SESSION_TYPE' ) ) {
			/*define( 'ALG_WC_EU_VAT_SESSION_TYPE', get_option( 'alg_wc_eu_vat_session_type', 'standard' ) );*/
			define( 'ALG_WC_EU_VAT_SESSION_TYPE', get_option( 'alg_wc_eu_vat_session_type', 'wc' ) );
		}
		switch ( ALG_WC_EU_VAT_SESSION_TYPE ) {
			case 'wc':
				if ( function_exists( 'WC' ) && WC()->session && ! WC()->session->has_session() ) {
					WC()->session->set_customer_session_cookie( true );
				}
				break;
			default: // 'standard'
				if ( ! session_id() ) {
					if ( ! headers_sent() ) {
						session_start([
							'read_and_close' => true,
						]);
					} else {
						$message = __( 'Can\'t create session (headers already sent).', 'eu-vat-for-woocommerce' ) . ' ' .
							__( 'Try selecting "WC session (recommended)" for "Session type" in "WooCommerce > Settings > EU VAT > Admin & Advanced > Advanced Options".', 'eu-vat-for-woocommerce' );
						alg_wc_eu_vat_maybe_log( false, false, false, false, $message );
					}
				}
				break;
		}
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_session_get' ) ) {
	/**
	 * alg_wc_eu_vat_session_get.
	 *
	 * @version 1.2.1
	 * @since   1.0.0
	 */
	function alg_wc_eu_vat_session_get( $key, $default = null ) {
		if ( ! defined( 'ALG_WC_EU_VAT_SESSION_TYPE' ) ) {
			define( 'ALG_WC_EU_VAT_SESSION_TYPE', get_option( 'alg_wc_eu_vat_session_type', 'wc' ) );
		}
		switch ( ALG_WC_EU_VAT_SESSION_TYPE ) {
			case 'wc':
				return ( function_exists( 'WC' ) && WC()->session ? WC()->session->get( $key, $default ) : $default );
			default: // 'standard'
			if ( ! session_id() ) {
				if ( ! headers_sent() ) {
					session_start();
				}
			}
				return ( isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : $default );
		}
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_session_set' ) ) {
	/**
	 * alg_wc_eu_vat_session_set.
	 *
	 * @version 1.2.1
	 * @since   1.0.0
	 */
	function alg_wc_eu_vat_session_set( $key, $value ) {
		if ( ! defined( 'ALG_WC_EU_VAT_SESSION_TYPE' ) ) {
			define( 'ALG_WC_EU_VAT_SESSION_TYPE', get_option( 'alg_wc_eu_vat_session_type', 'wc' ) );
		}
		switch ( ALG_WC_EU_VAT_SESSION_TYPE ) {
			case 'wc':
				if ( function_exists( 'WC' ) && WC()->session ) {
					WC()->session->set( $key, $value );
				}
				break;
			default: // 'standard'
				session_start();
				$_SESSION[ $key ] = $value;
				session_write_close();
				break;
		}
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_get_table_html' ) ) {
	/**
	 * alg_wc_eu_vat_get_table_html.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_eu_vat_get_table_html( $data, $args = array() ) {
		$defaults = array(
			'table_class'        => '',
			'table_style'        => '',
			'row_styles'         => '',
			'table_heading_type' => 'horizontal',
			'columns_classes'    => array(),
			'columns_styles'     => array(),
		);
		$args = array_merge( $defaults, $args );
		$table_class = ( '' == $args['table_class'] ? '' : ' class="' . $args['table_class'] . '"' );
		$table_style = ( '' == $args['table_style'] ? '' : ' style="' . $args['table_style'] . '"' );
		$row_styles  = ( '' == $args['row_styles']  ? '' : ' style="' . $args['row_styles']  . '"' );
		$html = '';
		$html .= '<table' . $table_class . $table_style . '>';
		$html .= '<tbody>';
		foreach( $data as $row_number => $row ) {
			$html .= '<tr' . $row_styles . '>';
			foreach( $row as $column_number => $value ) {
				$th_or_td = ( ( 0 === $row_number && 'horizontal' === $args['table_heading_type'] ) || ( 0 === $column_number && 'vertical' === $args['table_heading_type'] ) ?
					'th' : 'td' );
				$column_class = ( ! empty( $args['columns_classes'] ) && isset( $args['columns_classes'][ $column_number ] ) ?
					' class="' . $args['columns_classes'][ $column_number ] . '"' : '' );
				$column_style = ( ! empty( $args['columns_styles'] ) && isset( $args['columns_styles'][ $column_number ] ) ?
					' style="' . $args['columns_styles'][ $column_number ] . '"' : '' );
				$html .= '<' . $th_or_td . $column_class . $column_style . '>';
				$html .= $value;
				$html .= '</' . $th_or_td . '>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		return $html;
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_get_country_name_by_code' ) ) {
	/**
	 * alg_wc_eu_vat_get_country_name_by_code.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_eu_vat_get_country_name_by_code( $country_code ) {
		$countries = WC()->countries->get_countries();
		return ( isset( $countries[ $country_code ] ) ? $countries[ $country_code ] : $country_code );
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_get_order_id' ) ) {
	/**
	 * alg_wc_eu_vat_get_order_id.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_eu_vat_get_order_id( $_order ) {
		if ( ! $_order || ! is_object( $_order ) ) {
			return 0;
		}
		return ( alg_wc_eu_vat()->core->is_wc_version_below_3_0_0 ? $_order->id : $_order->get_id() );
	}
}
