<?php
/**
 * EU VAT for WooCommerce - Functions - General
 *
 * @version 4.7.0
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wpfactory_wc_eu_vat_is_checkout' ) ) {
	/**
	 * wpfactory_wc_eu_vat_is_checkout.
	 *
	 * @version 4.7.0
	 * @since   4.2.5
	 */
	function wpfactory_wc_eu_vat_is_checkout() {
		return apply_filters(
			'alg_wc_eu_vat_is_checkout',
			(
				function_exists( 'is_checkout' ) &&
				is_checkout()
			)
		);
	}
}

if ( ! function_exists( 'wpfactory_wc_eu_vat_is_cart' ) ) {
	/**
	 * wpfactory_wc_eu_vat_is_cart.
	 *
	 * @version 4.7.0
	 * @since   4.5.4
	 */
	function wpfactory_wc_eu_vat_is_cart() {
		return apply_filters(
			'alg_wc_eu_vat_is_cart',
			(
				function_exists( 'is_cart' ) &&
				is_cart()
			)
		);
	}
}

if ( ! function_exists( 'wpfactory_wc_eu_vat_is_block_checkout' ) ) {
	/**
	 * wpfactory_wc_eu_vat_is_block_checkout.
	 *
	 * @version 4.7.0
	 * @since   4.3.6
	 */
	function wpfactory_wc_eu_vat_is_block_checkout() {
		return (
			wpfactory_wc_eu_vat_is_checkout() &&
			has_block( 'woocommerce/checkout' )
		);
	}
}

if ( ! function_exists( 'wpfactory_wc_eu_vat_get_field_id' ) ) {
	/**
	 * wpfactory_wc_eu_vat_get_field_id.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	function wpfactory_wc_eu_vat_get_field_id( $short = false ) {
		$field_id = apply_filters( 'alg_wc_eu_vat_get_field_id', 'eu_vat_number' );
		return ( $short ? $field_id : 'billing_' . $field_id );
	}
}

if ( ! function_exists( 'wpfactory_wc_eu_vat_get_customers_location_by_ip' ) ) {
	/**
	 * wpfactory_wc_eu_vat_get_customers_location_by_ip.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	function wpfactory_wc_eu_vat_get_customers_location_by_ip( $ip_address = '' ) {
		if ( class_exists( 'WC_Geolocation' ) ) {
			// Get the country by IP
			$location = WC_Geolocation::geolocate_ip( $ip_address );
			// Base fallback
			if ( empty( $location['country'] ) ) {
				$location = wc_format_country_state_string(
					apply_filters(
						'woocommerce_customer_default_location', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
						get_option( 'woocommerce_default_country' )
					)
				);
			}
			return ( isset( $location['country'] ) ? $location['country'] : '' );
		} else {
			return '';
		}
	}
}

if ( ! function_exists( 'wpfactory_wc_eu_vat_session_start' ) ) {
	/**
	 * wpfactory_wc_eu_vat_session_start.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	function wpfactory_wc_eu_vat_session_start() {
		if (
			function_exists( 'WC' ) &&
			WC()->session &&
			! WC()->session->has_session()
		) {
			WC()->session->set_customer_session_cookie( true );
		}
	}
}

if ( ! function_exists( 'wpfactory_wc_eu_vat_session_get' ) ) {
	/**
	 * wpfactory_wc_eu_vat_session_get.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	function wpfactory_wc_eu_vat_session_get( $key, $default = null ) {
		if (
			function_exists( 'WC' ) &&
			WC()->session
		) {
			return WC()->session->get( $key, $default );
		}

		return $default;
	}
}

if ( ! function_exists( 'wpfactory_wc_eu_vat_session_set' ) ) {
	/**
	 * wpfactory_wc_eu_vat_session_set.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	function wpfactory_wc_eu_vat_session_set( $key, $value ) {
		if (
			function_exists( 'WC' ) &&
			WC()->session
		) {
			WC()->session->set( $key, $value );
		}
	}
}

if ( ! function_exists( 'wpfactory_wc_eu_vat_get_table_html' ) ) {
	/**
	 * wpfactory_wc_eu_vat_get_table_html.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	function wpfactory_wc_eu_vat_get_table_html( $data, $args = array() ) {
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

if ( ! function_exists( 'wpfactory_wc_eu_vat_get_country_name_by_code' ) ) {
	/**
	 * wpfactory_wc_eu_vat_get_country_name_by_code.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	function wpfactory_wc_eu_vat_get_country_name_by_code( $country_code ) {
		$countries = WC()->countries->get_countries();
		return ( $countries[ $country_code ] ?? $country_code );
	}
}

if ( ! function_exists( 'wpfactory_wc_eu_vat_get_order_id' ) ) {
	/**
	 * wpfactory_wc_eu_vat_get_order_id.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	function wpfactory_wc_eu_vat_get_order_id( $_order ) {
		if ( ! $_order || ! is_object( $_order ) ) {
			return 0;
		}
		return (
			wpfactory_wc_eu_vat()->core->is_wc_version_below_3_0_0 ?
			$_order->id :
			$_order->get_id()
		);
	}
}
