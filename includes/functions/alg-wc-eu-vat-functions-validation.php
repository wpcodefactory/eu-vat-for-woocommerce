<?php
/**
 * EU VAT for WooCommerce - Functions - Validation
 *
 * @version 1.7.1
 * @since   1.0.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'alg_wc_eu_vat_parse_vat' ) ) {
	/**
	 * alg_wc_eu_vat_parse_vat.
	 *
	 * @version 1.7.1
	 * @since   1.1.0
	 * @todo    [dev] (maybe) `alg_wc_eu_vat_maybe_log`: extract ID from `$full_vat_number`
	 */
	function alg_wc_eu_vat_parse_vat( $full_vat_number, $billing_country ) {
		$full_vat_number = strtoupper( $full_vat_number );
		$billing_country = strtoupper( $billing_country );
		if ( strlen( $full_vat_number ) > 2 && ( $country = substr( $full_vat_number, 0, 2 ) ) && ctype_alpha( $country ) ) {
			if ( 'no' === get_option( 'alg_wc_eu_vat_check_billing_country_code', 'no' ) || ( 'EL' === $country ? 'GR' : $country ) == $billing_country ) {
				$number = substr( $full_vat_number, 2 );
			} else {
				alg_wc_eu_vat_maybe_log( $country, $full_vat_number, '', '',
					sprintf( __( 'Error: Country code does not match (%s)', 'eu-vat-for-woocommerce' ), $billing_country ) );
				$country = '';
				$number  = '';
			}
		} elseif ( 'yes' === get_option( 'alg_wc_eu_vat_allow_without_country_code', 'no' ) ) {
			$country = $billing_country;
			$number  = $full_vat_number;
		} else {
			$country = '';
			$number  = $full_vat_number;
		}
		$eu_vat_number = array( 'country' => $country, 'number' => $number );
		return $eu_vat_number;
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_validate_vat_no_soap' ) ) {
	/**
	 * alg_wc_eu_vat_validate_vat_no_soap.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 * @return  mixed: bool on successful checking, null otherwise
	 */
	function alg_wc_eu_vat_validate_vat_no_soap( $country_code, $vat_number, $billing_company, $method ) {
		$country_code = strtoupper( $country_code );
		$api_url = "http://ec.europa.eu/taxation_customs/vies/viesquer.do?ms=" . $country_code . "&vat=" . $vat_number;
		switch ( $method ) {
			case 'file_get_contents':
				if ( ini_get( 'allow_url_fopen' ) ) {
					$response = file_get_contents( $api_url );
				} else {
					alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, $method,
						sprintf( __( 'Error: %s is disabled', 'eu-vat-for-woocommerce' ), 'allow_url_fopen' ) );
					return null;
				}
				break;
			default: // 'curl'
				if ( function_exists( 'curl_version' ) ) {
					$curl = curl_init( $api_url );
					curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
					$response = curl_exec( $curl );
					curl_close( $curl );
				} else {
					alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, $method,
						sprintf( __( 'Error: %s is disabled', 'eu-vat-for-woocommerce' ), 'cURL' ) );
					return null;
				}
				break;
		}
		if ( false === $response ) {
			alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, $method,
				__( 'Error: No response', 'eu-vat-for-woocommerce' ) );
			return null;
		}
		// Company name
		if ( 'yes' === apply_filters( 'alg_wc_eu_vat_check_company_name', 'no' ) && false !== ( $pos = strpos( $response, '<td class="labelStyle">Name</td>' ) ) ) {
			$company_name = substr( $response, $pos );
			$company_name = explode( '<td', $company_name );
			if ( isset( $company_name[2] ) ) {
				$company_name = $company_name[2];
				if ( strlen( $company_name ) > 0 ) {
					$company_name = substr( $company_name, 1 );
					$company_name = explode( '</td>', $company_name );
					$company_name = trim( $company_name[0] );
					$company_name = strtoupper( $company_name );
				} else {
					$company_name = '';
				}
			} else {
				$company_name = '';
			}
		} else {
			$company_name = '';
		}
		// Final result
		$return = ( false !== strpos( $response, '="validStyle"' ) &&
			( 'no' === apply_filters( 'alg_wc_eu_vat_check_company_name', 'no' ) || $company_name === $billing_company ) );
		if ( ! $return ) {
			if ( false !== strpos( $response, '="validStyle"' ) ) {
				alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, $method,
					sprintf( __( 'Error: Company name does not match (%s)', 'eu-vat-for-woocommerce' ), $company_name ) );
			} else {
				alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, $method,
					__( 'Error: VAT ID not valid', 'eu-vat-for-woocommerce' ) );
			}
		} else {
			alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, $method,
				__( 'Success: VAT ID is valid', 'eu-vat-for-woocommerce' ) );
		}
		return $return;
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_validate_vat_soap' ) ) {
	/**
	 * alg_wc_eu_vat_validate_vat_soap.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 * @return  mixed: bool on successful checking, null otherwise
	 */
	function alg_wc_eu_vat_validate_vat_soap( $country_code, $vat_number, $billing_company ) {
		try {
			if ( class_exists( 'SoapClient' ) ) {
				$client = new SoapClient(
					'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl',
					array( 'exceptions' => true )
				);
				$result = $client->checkVat( array(
					'countryCode' => $country_code,
					'vatNumber'   => $vat_number,
				) );
				/**
				 * $result = stdClass Object( countryCode, vatNumber, requestDate, valid, name, address )
				 */
				$return = ( isset( $result->valid ) ?
					( $result->valid && ( 'no' === apply_filters( 'alg_wc_eu_vat_check_company_name', 'no' ) || strtoupper( $result->name ) === $billing_company ) ) : null );
				if ( ! $return ) {
					if ( isset( $result->valid ) ) {
						if ( $result->valid ) {
							alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, 'soap',
								sprintf( __( 'Error: Company name does not match (%s)', 'eu-vat-for-woocommerce' ), strtoupper( $result->name ) ) );
						} else {
							alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, 'soap',
								__( 'Error: VAT ID not valid', 'eu-vat-for-woocommerce' ) );
						}
					} else {
						alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, 'soap',
							__( 'Error: Result is not set', 'eu-vat-for-woocommerce' ) );
					}
				} else {
					alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, 'soap',
						__( 'Success: VAT ID is valid', 'eu-vat-for-woocommerce' ) );
				}
				return $return;
			} else {
				alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, 'soap',
					__( 'Error: SoapClient class does not exist', 'eu-vat-for-woocommerce' ) );
				return null;
			}
		} catch( Exception $exception ) {
			alg_wc_eu_vat_maybe_log( $country_code, $vat_number, $billing_company, 'soap',
				sprintf( __( 'Error: Exception: %s', 'eu-vat-for-woocommerce' ), $exception->getMessage() ) );
			return null;
		}
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_validate_vat_with_method' ) ) {
	/**
	 * alg_wc_eu_vat_validate_vat_with_method.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 * @return  mixed: bool on successful checking, null otherwise
	 */
	function alg_wc_eu_vat_validate_vat_with_method( $country_code, $vat_number, $billing_company, $method ) {
		switch ( $method ) {
			case 'soap':
				return alg_wc_eu_vat_validate_vat_soap( $country_code, $vat_number, $billing_company );
			default: // 'curl', 'file_get_contents'
				return alg_wc_eu_vat_validate_vat_no_soap( $country_code, $vat_number, $billing_company, $method );
		}
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_validate_vat' ) ) {
	/**
	 * alg_wc_eu_vat_validate_vat.
	 *
	 * @version 1.6.1
	 * @since   1.0.0
	 * @return  mixed: bool on successful checking, null otherwise
	 * @todo    [dev] (maybe) check for minimal length
	 */
	function alg_wc_eu_vat_validate_vat( $country_code, $vat_number, $billing_company = '' ) {
		if ( '' != ( $skip_countries = get_option( 'alg_wc_eu_vat_advanced_skip_countries', array() ) ) ) {
			$skip_countries = array_map( 'strtoupper', array_map( 'trim', explode( ',', $skip_countries ) ) );
			if ( in_array( strtoupper( $country_code ), $skip_countries ) ) {
				return true;
			}
		}
		$methods = array();
		switch ( get_option( 'alg_wc_eu_vat_first_method', 'soap' ) ) {
			case 'curl':
				$methods = array( 'curl', 'file_get_contents', 'soap' );
				break;
			case 'file_get_contents':
				$methods = array( 'file_get_contents', 'curl', 'soap' );
				break;
			default: // 'soap'
				$methods = array( 'soap', 'curl', 'file_get_contents' );
				break;
		}
		$billing_company = strtoupper( $billing_company );
		foreach ( $methods as $method ) {
			if ( null !== ( $result = alg_wc_eu_vat_validate_vat_with_method( $country_code, $vat_number, $billing_company, $method ) ) ) {
				return $result;
			}
		}
		return null;
	}
}
