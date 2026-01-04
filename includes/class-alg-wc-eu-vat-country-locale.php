<?php
/**
 * EU VAT for WooCommerce - Country Locale
 *
 * @version 4.5.5
 * @since   4.1.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Country_Locale' ) ) :

class Alg_WC_EU_VAT_Country_Locale {

	/**
	 * required_in_countries.
	 *
	 * @version 4.1.0
	 */
	public $required_in_countries;

	/**
	 * show_in_countries.
	 *
	 * @version 4.1.0
	 */
	public $show_in_countries;

	/**
	 * Constructor.
	 *
	 * @version 4.1.0
	 * @since   4.1.0
	 */
	function __construct() {

		// Show in countries, Required in countries
		$this->required_in_countries = $this->get_required_in_countries();
		$this->show_in_countries     = $this->get_show_in_countries();

		if (
			'' != $this->show_in_countries ||
			'' != $this->required_in_countries ||
			'yes_for_company' === get_option( 'alg_wc_eu_vat_field_required', 'no' )
		) {
			add_filter(
				'woocommerce_get_country_locale',
				array( $this, 'set_eu_vat_country_locale' ),
				PHP_INT_MAX
			);
			add_filter(
				'woocommerce_get_country_locale_default',
				array( $this, 'set_eu_vat_country_locale_default' ),
				PHP_INT_MAX
			);
			add_filter(
				'woocommerce_country_locale_field_selectors',
				array( $this, 'set_eu_vat_country_locale_field_selectors' ),
				PHP_INT_MAX
			);
		}

	}

	/**
	 * set_eu_vat_country_locale.
	 *
	 * @version 4.5.5
	 * @since   1.7.0
	 */
	function set_eu_vat_country_locale( $country_locales ) {

		if ( has_block( 'woocommerce/checkout' ) ) {
			return $country_locales;
		}

		$show_eu_vat_field_countries = array_map(
			'strtoupper',
			array_map( 'trim', explode( ',', $this->show_in_countries ) )
		);
		$required_eu_vat_field_countries = array_map(
			'strtoupper',
			array_map( 'trim', explode( ',', $this->required_in_countries ) )
		);

		$eu_vat_required = get_option( 'alg_wc_eu_vat_field_required', 'no' );

		// Enable field in selected locales
		$original_required = ( 'yes' === $eu_vat_required );

		if ( ! empty( $show_eu_vat_field_countries ) ) {
			$country_locales_keys = array_keys( $country_locales );
			$ky2                  = $country_locales_keys;
			$wc_countries         = new WC_Countries();
			$w_countries          = $wc_countries->get_countries();
			$ky1                  = array_keys( $w_countries );
			$arr_dif              = array_diff( $ky1, $ky2 );
		}

		if (
			'yes_for_company'   === $eu_vat_required ||
			'yes_for_countries' === $eu_vat_required ||
			'no_for_countries'  === $eu_vat_required ||
			! empty( $show_eu_vat_field_countries )
		) {

			$is_required = $original_required;

			foreach ( $country_locales as $country_code => &$country_locale ) {

				if ( 'yes_for_countries' ===  $eu_vat_required ) {
					if ( in_array( $country_code, $required_eu_vat_field_countries ) ) {
						$is_required = true;
					}
				} elseif ( 'no_for_countries' === $eu_vat_required ) {
					if ( in_array( $country_code, $required_eu_vat_field_countries ) ) {
						$is_required = false;
					} else {
						$is_required = true;
					}
				}

				if ( ! empty( $show_eu_vat_field_countries[0] ) ) {
					if ( in_array( $country_code, $show_eu_vat_field_countries ) ) {
						$hidden = false;
					} else {
						$hidden = true;
					}
				} else {
					$hidden = false;
				}

				if ( 'yes_for_company' === $eu_vat_required ) {
					$is_required = false;
				}

				$country_locale[ alg_wc_eu_vat_get_field_id( true ) ] = array(
					'required' => $is_required,
					'hidden'   => $hidden,
				);
			}

			if ( ! empty( $show_eu_vat_field_countries[0] ) ) {
				foreach ( $show_eu_vat_field_countries as $count_code ) {
					$country_locales[ $count_code ][ alg_wc_eu_vat_get_field_id( true ) ] = array(
						'hidden' => false,
					);
				}
			}

			if ( ! empty( $arr_dif ) ) {
				foreach ( $arr_dif as $con ) {
					if ( ! empty( $show_eu_vat_field_countries[0] ) ) {
						if ( in_array( $con, $show_eu_vat_field_countries ) ) {
							$hidden = false;
						} else {
							$hidden = true;
						}
					} else {
						$hidden = false;
					}
					$country_locales[ $con ][ alg_wc_eu_vat_get_field_id( true ) ] = array(
						'hidden'   => $hidden,
						'required' => $is_required,
					);
				}
			}

			if ( ! empty( $required_eu_vat_field_countries ) ) {
				foreach ( $required_eu_vat_field_countries as $country_code_re ) {

					$is_required = $original_required;

					if ( 'yes_for_countries' === $eu_vat_required ) {
						$is_required = true;
					} elseif ( 'no_for_countries' === $eu_vat_required ) {
						$is_required = false;
					}

					if ( ! empty( $show_eu_vat_field_countries[0] ) ) {
						if ( in_array( $country_code_re, $show_eu_vat_field_countries ) ) {
							$hidden = false;
						} else {
							$hidden = true;
						}
					} else {
						$hidden = false;
					}

					if ( 'yes_for_company' === $eu_vat_required ) {
						$is_required = false;
					}

					$country_locales[ $country_code_re ][ alg_wc_eu_vat_get_field_id( true ) ] = array(
						'required' => $is_required,
						'hidden'   => $hidden,
					);
				}
			}
		}

		return $country_locales;
	}

	/**
	 * set_eu_vat_country_locale_field_selectors.
	 *
	 * @version 1.4.1
	 * @since   1.4.0
	 */
	function set_eu_vat_country_locale_field_selectors( $locale_fields ) {
		$locale_fields[ alg_wc_eu_vat_get_field_id( true ) ] = '#' . alg_wc_eu_vat_get_field_id() . '_field';
		return $locale_fields;
	}

	/**
	 * set_eu_vat_country_locale_default.
	 *
	 * @version 4.1.0
	 * @since   1.4.0
	 */
	function set_eu_vat_country_locale_default( $default_locale ) {

		if ( has_block( 'woocommerce/checkout' ) ) {
			return $default_locale;
		}

		$required = in_array(
			get_option( 'alg_wc_eu_vat_field_required', 'no' ),
			array(
				'yes',
				'no_for_countries',
				'yes_for_company',
			)
		);

		$default_locale[ alg_wc_eu_vat_get_field_id( true ) ] = array(
			'required' => $required,
			'hidden'   => false,
		);

		return $default_locale;
	}

	/**
	 * get_show_in_countries.
	 *
	 * @version 4.1.0
	 * @since   1.7.0
	 */
	function get_show_in_countries() {
		return apply_filters( 'alg_wc_eu_vat_show_in_countries', '' );
	}

	/**
	 * get_required_in_countries.
	 *
	 * @version 4.1.0
	 * @since   1.7.0
	 */
	function get_required_in_countries() {
		if (
			in_array(
				get_option( 'alg_wc_eu_vat_field_required', 'no' ),
				array( 'yes_for_countries', 'no_for_countries' )
			)
		) {
			$countries = get_option( 'alg_wc_eu_vat_field_required_countries', array() );
			return (
				! empty( $countries ) ?
				implode( ',', $countries ) :
				''
			);
		}
		return '';
	}

}

endif;

return new Alg_WC_EU_VAT_Country_Locale();
