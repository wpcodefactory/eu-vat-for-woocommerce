<?php
/**
 * EU VAT for WooCommerce - Functions - Validation
 *
 * @version 4.3.2
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'alg_wc_eu_vat_parse_vat' ) ) {
	/**
	 * alg_wc_eu_vat_parse_vat.
	 *
	 * @version 4.2.9
	 * @since   1.1.0
	 *
	 * @todo    (v4.2.9) `VAT_NOT_VALID`: better `country` and `number`?
	 * @todo    (dev) `alg_wc_eu_vat_log`: extract ID from `$full_vat_number`?
	 */
	function alg_wc_eu_vat_parse_vat( $full_vat_number, $billing_country ) {

		if ( ! preg_match( '/^[a-zA-Z0-9]+$/', $full_vat_number ) ) {
			return array(
				'country'   => '',
				'number'    => '',
				'error_msg' => 'VAT_NOT_VALID',
			);
		}

		$error_msg = '';

		$full_vat_number = strtoupper( $full_vat_number );
		$billing_country = strtoupper( $billing_country );
		if (
			strlen( $full_vat_number ) > 2 &&
			( $country = substr( $full_vat_number, 0, 2 ) ) &&
			ctype_alpha( $country )
		) {
			if (
				'no' === get_option( 'alg_wc_eu_vat_check_billing_country_code', 'no' ) ||
				( 'EL' === $country ? 'GR' : $country ) == $billing_country
			) {
				$number = substr( $full_vat_number, 2 );
			} else {
				alg_wc_eu_vat_log(
					$country,
					$full_vat_number,
					'',
					'',
					sprintf(
						/* Translators: %s: Billing country. */
						__( 'Error: Country code does not match (%s)', 'eu-vat-for-woocommerce' ),
						$billing_country
					)
				);
				$country   = '';
				$number    = '';
				$error_msg = 'WRONG_BILLING_COUNTRY';
			}
		} elseif ( 'yes' === get_option( 'alg_wc_eu_vat_allow_without_country_code', 'no' ) ) {
			$country = $billing_country;
			if ( 'GR' === $billing_country ) {
				$country = 'EL';
			}
			$number = $full_vat_number;
		} else {
			$country = '';
			$number  = $full_vat_number;
		}

		$eu_vat_number = array(
			'country'   => $country,
			'number'    => $number,
			'error_msg' => $error_msg,
		);

		return $eu_vat_number;
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_validate_vat_no_soap' ) ) {
	/**
	 * alg_wc_eu_vat_validate_vat_no_soap.
	 *
	 * @version 4.3.1
	 * @since   1.0.0
	 *
	 * @return  mixed: bool on successful checking, null otherwise
	 */
	function alg_wc_eu_vat_validate_vat_no_soap( $country_code, $vat_number, $billing_company, $method ) {

		$country_code = strtoupper( $country_code );

		$api_url = apply_filters(
			'alg_wc_eu_vat_validation_no_soap_api_url',
			"https://ec.europa.eu/taxation_customs/vies/rest-api/ms/{$country_code}/vat/{$vat_number}",
			$country_code,
			$vat_number,
			$method
		);

		// Request identifier
		if (
			'yes' === get_option( 'alg_wc_eu_vat_request_identifier', 'no' ) &&
			'' !== ( $requester_country_code = get_option( 'alg_wc_eu_vat_requester_country_code', '' ) ) &&
			'' !== ( $requester_vat_number   = get_option( 'alg_wc_eu_vat_requester_vat_number', '' ) )
		) {
			$api_url = add_query_arg(
				array(
					'requesterMemberStateCode' => $requester_country_code,
					'requesterNumber'          => $requester_vat_number,
				),
				$api_url
			);
		}

		switch ( $method ) {
			case 'file_get_contents':
				if ( ini_get( 'allow_url_fopen' ) ) {
					$response = file_get_contents( $api_url );
				} else {
					alg_wc_eu_vat_log(
						$country_code,
						$vat_number,
						$billing_company,
						$method,
						sprintf(
							/* Translators: %s: allow_url_fopen. */
							__( 'Error: %s is disabled', 'eu-vat-for-woocommerce' ),
							'allow_url_fopen'
						)
					);
					return null;
				}
				break;
			default: // 'curl'
				if ( function_exists( 'curl_version' ) ) {
					// phpcs:disable WordPress.WP.AlternativeFunctions.curl_curl_init, WordPress.WP.AlternativeFunctions.curl_curl_setopt, WordPress.WP.AlternativeFunctions.curl_curl_exec, WordPress.WP.AlternativeFunctions.curl_curl_close
					$curl = curl_init( $api_url );
					curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
					curl_setopt( $curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
					if ( apply_filters( 'alg_wc_eu_vat_validation_curl_disable_ssl', false ) ) {
						curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
						curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
					}
					$response = curl_exec( $curl );
					curl_close( $curl );
					// phpcs:enable WordPress.WP.AlternativeFunctions.curl_curl_init, WordPress.WP.AlternativeFunctions.curl_curl_setopt, WordPress.WP.AlternativeFunctions.curl_curl_exec, WordPress.WP.AlternativeFunctions.curl_curl_close
				} else {
					alg_wc_eu_vat_log(
						$country_code,
						$vat_number,
						$billing_company,
						$method,
						sprintf(
							/* Translators: %s: cURL. */
							__( 'Error: %s is disabled', 'eu-vat-for-woocommerce' ),
							'cURL'
						)
					);
					return null;
				}
				break;
		}

		// Filter response
		$response = apply_filters(
			'alg_wc_eu_vat_validation_response',
			$response,
			$method,
			$country_code,
			$vat_number
		);

		// Save response to the variable
		alg_wc_eu_vat()->core->eu_vat_response_data = (
			false !== $response ?
			json_decode( $response, false ) :
			false
		);

		// Save response to the session
		alg_wc_eu_vat_session_set(
			'alg_wc_eu_vat_response_data',
			alg_wc_eu_vat()->core->eu_vat_response_data
		);

		// No response
		if ( false === $response ) {
			alg_wc_eu_vat_log(
				$country_code,
				$vat_number,
				$billing_company,
				$method,
				__( 'Error: No response', 'eu-vat-for-woocommerce' )
			);

			return null;
		}

		// Decode response
		$decoded_result = json_decode( $response, true );

		// Handle decoded response
		if (
			isset( $decoded_result['isValid'] ) &&
			$decoded_result['isValid']
		) {

			// Log
			alg_wc_eu_vat_log(
				$country_code,
				$vat_number,
				$billing_company,
				$method,
				__( 'Success: VAT ID is valid', 'eu-vat-for-woocommerce' )
			);

			// Store result to session
			alg_wc_eu_vat_store_validation_session(
				$country_code,
				$vat_number,
				true,
				$billing_company,
				$decoded_result
			);

			// Valid
			return true;

		} else {

			// VIES error
			if (
				! empty( $decoded_result['userError'] ) &&
				in_array(
					$decoded_result['userError'],
					array(
						'MS_UNAVAILABLE',
						'GLOBAL_MAX_CONCURRENT_REQ',
						'MS_MAX_CONCURRENT_REQ',
					)
				)
			) {
				if ( 'yes' === get_option( 'alg_wc_eu_vat_validate_vies_not_available', 'no' ) ) {
					// Accept
					alg_wc_eu_vat_session_set(
						'alg_wc_eu_vat_vies_error_message',
						$decoded_result['userError']
					);
					return false;
				}
				// Validation failed
				return null;
			}

			// Log
			alg_wc_eu_vat_log(
				$country_code,
				$vat_number,
				$billing_company,
				$method,
				__( 'Error: VAT ID not valid', 'eu-vat-for-woocommerce' )
			);

			// Not valid
			return false;

		}

	}
}

if ( ! function_exists( 'alg_wc_eu_vat_validate_vat_soap' ) ) {
	/**
	 * alg_wc_eu_vat_validate_vat_soap.
	 *
	 * @version 4.3.1
	 * @since   1.0.0
	 *
	 * @return  mixed: bool on successful checking, null otherwise
	 */
	function alg_wc_eu_vat_validate_vat_soap( $country_code, $vat_number, $billing_company ) {
		try {
			if ( class_exists( 'SoapClient' ) ) {

				$context_options = array(
					'ssl' => array(
						'verify_peer'       => false,
						'verify_peer_name'  => false,
						'allow_self_signed' => true,
					)
				);
				$ssl_context = stream_context_create( $context_options );

				$client = new SoapClient(
					'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl',
					array(
						'exceptions'     => true,
						'stream_context' => $ssl_context,
					)
				);

				if (
					'no' === get_option( 'alg_wc_eu_vat_request_identifier', 'no' ) ||
					'' === ( $requester_country_code = get_option( 'alg_wc_eu_vat_requester_country_code', '' ) ) ||
					'' === ( $requester_vat_number   = get_option( 'alg_wc_eu_vat_requester_vat_number', '' ) )
				) {
					$result = $client->checkVat( array(
						'countryCode'            => $country_code,
						'vatNumber'              => $vat_number,
					) );
				} else {
					$result = $client->checkVatApprox( array(
						'countryCode'            => $country_code,
						'vatNumber'              => $vat_number,
						'requesterCountryCode'   => $requester_country_code,
						'requesterVatNumber'     => $requester_vat_number,
					) );
					// Copy VAT details
					if (
						isset( $result->traderName ) &&
						! isset( $result->name )
					) {
						$result->name = $result->traderName;
					}
					if (
						isset( $result->traderAddress ) &&
						! isset( $result->address )
					) {
						$result->address = $result->traderAddress;
					}
				}

				// Filter response
				$result = apply_filters(
					'alg_wc_eu_vat_validation_response',
					$result,
					'soap',
					$country_code,
					$vat_number
				);

				// Save response to the variable
				alg_wc_eu_vat()->core->eu_vat_response_data = $result;

				// Save response to the session
				alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_response_data', $result );

				/**
				 * $result = stdClass Object( countryCode, vatNumber, requestDate, valid, name, address )
				 */
				$return = (
					isset( $result->valid ) ?
					(
						$result->valid &&
						(
							'no' === apply_filters( 'alg_wc_eu_vat_check_company_name', 'no' ) ||
							strtolower( $result->name ) === $billing_company
						)
					) :
					null
				);

				if ( ! $return ) {
					if ( isset( $result->valid ) ) {
						if ( $result->valid ) {
							alg_wc_eu_vat_log(
								$country_code,
								$vat_number,
								$billing_company,
								'soap',
								sprintf(
									/* Translators: %s: Company name. */
									__( 'Error: Company name does not match (%s)', 'eu-vat-for-woocommerce' ),
									strtolower( $result->name )
								)
							);

							alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check_company_name', strtolower( $result->name ) );
							alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check_company', true );

						} else {
							alg_wc_eu_vat_log(
								$country_code,
								$vat_number,
								$billing_company,
								'soap',
								__( 'Error: VAT ID not valid', 'eu-vat-for-woocommerce' )
							);
						}
					} else {
						alg_wc_eu_vat_log(
							$country_code,
							$vat_number,
							$billing_company,
							'soap',
							__( 'Error: Result is not set', 'eu-vat-for-woocommerce' )
						);
					}
				} else {
					alg_wc_eu_vat_log(
						$country_code,
						$vat_number,
						$billing_company,
						'soap',
						__( 'Success: VAT ID is valid', 'eu-vat-for-woocommerce' )
					);
				}

				if ( isset( $result->name ) ) {
					alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_return_company_name', strtolower( $result->name ) );
				}

				// Store result to session
				alg_wc_eu_vat_store_validation_session(
					$country_code,
					$vat_number,
					$return,
					$billing_company,
					$result
				);

				return $return;
			} else {
				alg_wc_eu_vat_log(
					$country_code,
					$vat_number,
					$billing_company,
					'soap',
					__( 'Error: SoapClient class does not exist', 'eu-vat-for-woocommerce' )
				);

				return null;
			}
		} catch ( Exception $exception ) {
			alg_wc_eu_vat_log(
				$country_code,
				$vat_number,
				$billing_company,
				'soap',
				sprintf(
					/* Translators: %s: Error message. */
					__( 'Error: Exception: %s', 'eu-vat-for-woocommerce' ),
					$exception->getMessage()
				)
			);

			if ( 'yes' === get_option( 'alg_wc_eu_vat_validate_vies_not_available', 'no' ) ) {

				$accepted_exception = array( 'MS_UNAVAILABLE', 'GLOBAL_MAX_CONCURRENT_REQ', 'MS_MAX_CONCURRENT_REQ' );
				if ( in_array( $exception->getMessage(), $accepted_exception ) ) {
					alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_vies_error_message', $exception->getMessage() );

					return false;
				}
			}

			return null;

		} catch ( SoapFault $fault ) {

			alg_wc_eu_vat_log(
				$country_code,
				$vat_number,
				$billing_company,
				'soap',
				sprintf(
					/* Translators: %s: Error message. */
					__( 'Error: SoapFault: %s', 'eu-vat-for-woocommerce' ),
					$fault->getMessage()
				)
			);

			if ( $fault->faultcode >= 500 && $fault->faultcode < 600 ) {
				// Custom handling for 5xx errors
				if ( 'yes' === get_option( 'alg_wc_eu_vat_validate_vies_not_available', 'no' ) ) {
					// Accept the VAT number if VIES is not available
					alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_vies_error_message', $fault->getMessage() );
					return false;
				}
			}

			return null;
		}
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_validate_vat_with_method' ) ) {
	/**
	 * alg_wc_eu_vat_validate_vat_with_method.
	 *
	 * @version 4.3.2
	 * @since   1.0.0
	 *
	 * @return  mixed: bool on successful checking, null otherwise
	 *
	 * @todo    (v4.3.2) `alg_wc_eu_vat_validate_vat_uk_vatsense()`: move it outside of the `alg_wc_eu_vat_validate_vat_with_method()`
	 */
	function alg_wc_eu_vat_validate_vat_with_method( $country_code, $vat_number, $billing_company, $method ) {

		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check_company_name', null );
		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check_company', null );
		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_return_company_name', null );
		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_details', null );
		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_response_data', null );

		alg_wc_eu_vat()->core->vat_details_data = null;
		alg_wc_eu_vat()->core->eu_vat_response_data = null;

		if ( $country_code == 'GB' ) {
			return alg_wc_eu_vat_validate_vat_uk_vatsense( $country_code, $vat_number, $billing_company );
		}

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
	 * @version 4.2.5
	 * @since   1.0.0
	 *
	 * @return  mixed: bool on successful checking, null otherwise
	 *
	 * @todo    (dev) check for minimal length?
	 */
	function alg_wc_eu_vat_validate_vat( $country_code, $vat_number, $billing_company = '' ) {
		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_vies_error_message', null );

		if ( '' != ( $skip_countries = get_option( 'alg_wc_eu_vat_advanced_skip_countries', array() ) ) ) {
			if ( ! empty( $skip_countries ) ) {
				$skip_countries = array_map( 'strtoupper', array_map( 'trim', explode( ',', $skip_countries ) ) );
				if ( in_array( strtoupper( $country_code ), $skip_countries ) ) {
					return true;
				}
			}
		}

		$vat_number = preg_replace( '/\s+/', '', $vat_number );

		// VAT validate manually pre-saved number
		if ( 'yes' === get_option( 'alg_wc_eu_vat_manual_validation_enable', 'no' ) ) {
			if ( '' != ( $manual_validation_vat_numbers = get_option( 'alg_wc_eu_vat_manual_validation_vat_numbers', '' ) ) ) {
				$prevalidated_VAT_numbers = explode( ',', $manual_validation_vat_numbers );
				$sanitized_vat_numbers    = array_map( 'trim', $prevalidated_VAT_numbers );

				$conjuncted_vat_number = $country_code . '' . $vat_number;
				if ( isset( $sanitized_vat_numbers[0] ) ) {
					if ( in_array( $conjuncted_vat_number, $sanitized_vat_numbers ) ) {
						alg_wc_eu_vat_log(
							$country_code,
							$vat_number,
							$billing_company,
							'',
							__( 'Success: VAT ID valid. Matched with prevalidated VAT numbers.', 'eu-vat-for-woocommerce' )
						);
						return true;
					}
				}
			}
		}

		// First validate from session value
		$validate_status = alg_wc_eu_vat_validate_from_session( $country_code, $vat_number, $billing_company );

		if ( $validate_status ) {
			alg_wc_eu_vat_log(
				$country_code,
				$vat_number,
				$billing_company,
				'ValidateFromStoredSession',
				__( 'Success: VAT ID is valid', 'eu-vat-for-woocommerce' )
			);
			return true;
		}
		// VAT validate manually pre-saved number end

		// Get validation methods
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
		$methods = apply_filters( 'alg_wc_eu_vat_validation_methods', $methods );

		// Billing company
		$billing_company = strtolower( $billing_company );

		// Validate
		foreach ( $methods as $method ) {
			$result = alg_wc_eu_vat_validate_vat_with_method(
				$country_code,
				$vat_number,
				$billing_company,
				$method
			);
			if ( null !== $result ) {
				return apply_filters(
					'alg_wc_eu_vat_check_alternative',
					$result,
					$country_code,
					$vat_number,
					$billing_company
				);
			}
		}

		// Validation failed
		return apply_filters(
			'alg_wc_eu_vat_check_alternative',
			null,
			$country_code,
			$vat_number,
			$billing_company
		);

	}
}

if ( ! function_exists( 'alg_wc_eu_vat_validate_vat_uk_vatsense' ) ) {
	/**
	 * alg_wc_eu_vat_validate_vat_uk_vatsense.
	 *
	 * E.g.: GB333289454 (BBC Studios)
	 *
	 * @version 4.3.2
	 * @since   4.3.2
	 *
	 * @return  mixed: bool on successful checking, null otherwise
	 *
	 * @see     https://vatsense.com/documentation#validate_number
	 *
	 * @todo    (v4.3.2) `alg_wc_eu_vat_store_validation_session()`: `$api_response`!
	 * @todo    (v4.3.2) test: `alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check_company_name', $company_name )`
	 * @todo    (v4.3.2) better log messages
	 */
	function alg_wc_eu_vat_validate_vat_uk_vatsense( $country_code, $vat_number, $billing_company = '' ) {

		$key = apply_filters(
			'alg_wc_eu_vat_vatsense_key',
			get_option( 'alg_wc_eu_vat_vatsense_key', '' )
		);
		if ( ! $key ) {
			alg_wc_eu_vat_debug_log(
				__( 'Error: "VAT Sense" key not set', 'eu-vat-for-woocommerce' ),
				array(
					'Country' => $country_code,
					'VAT ID'  => $vat_number,
				)
			);
			return null;
		}

		$country_code = strtoupper( $country_code );

		$res = wp_remote_get(
			"https://api.vatsense.com/1.0/validate?vat_number={$country_code}{$vat_number}",
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( "user:{$key}" ),
				),
			)
		);

		if ( ! empty( $res['response']['code'] ) ) {
			switch ( $res['response']['code'] ) {

				case 200:
					if ( ! empty( $res['body'] ) ) {
						$res_body = json_decode( $res['body'], true );
						if ( isset( $res_body['data']['valid'] ) ) {

							if ( empty( $res_body['data']['valid'] ) ) {

								alg_wc_eu_vat_debug_log(
									__( 'Error: VAT ID not valid', 'eu-vat-for-woocommerce' ),
									array(
										'Country' => $country_code,
										'VAT ID'  => $vat_number,
									)
								);
								return false;

							} else {

								$is_valid = true;

								// Check company name
								$company_name = ( $res_body['data']['company']['company_name'] ?? false );
								if (
									'yes' === apply_filters( 'alg_wc_eu_vat_check_company_name', 'no' ) &&
									strtolower( $company_name ) !== strtolower( $billing_company )
								) {
									$is_valid = false;
									alg_wc_eu_vat_debug_log(
										sprintf(
											/* Translators: %s: Company name. */
											__( 'Error: Company name does not match (%s)', 'eu-vat-for-woocommerce' ),
											$company_name
										),
										array(
											'Country' => $country_code,
											'VAT ID'  => $vat_number,
										)
									);

									alg_wc_eu_vat_session_set(
										'alg_wc_eu_vat_to_check_company_name',
										$company_name
									);
									alg_wc_eu_vat_session_set(
										'alg_wc_eu_vat_to_check_company',
										true
									);

								}

								// Store result to session
								alg_wc_eu_vat_store_validation_session(
									$country_code,
									$vat_number,
									$is_valid,
									$billing_company,
									array()
								);

								if ( $is_valid ) {
									alg_wc_eu_vat_debug_log(
										__( 'Success: VAT ID valid', 'eu-vat-for-woocommerce' ),
										array(
											'Country' => $country_code,
											'VAT ID'  => $vat_number,
										)
									);
								}

								return $is_valid;

							}

						}
					}
					break;

				case 400:
					alg_wc_eu_vat_debug_log(
						sprintf(
							/* Translators: %s: Error message or code. */
							__( 'Error: VAT ID not valid (%s)', 'eu-vat-for-woocommerce' ),
							(
								! empty( $res['response']['message'] ) ?
								$res['response']['message'] :
								'#' . $res['response']['code']
							)
						),
						array(
							'Country' => $country_code,
							'VAT ID'  => $vat_number,
						)
					);
					return false;

				/**
				 * All responses.
				 *
				 * @see https://vatsense.com/documentation#responses
				 *
				 * 200 - OK - Everything worked as expected.
				 * 400 - Bad Request - The request was unacceptable, often due to missing a required parameter.
				 * 401 - Unauthorized - No valid API key provided.
				 * 402 - Request Failed - The parameters were valid but the request failed.
				 * 404 - Not Found - The requested resource doesn't exist.
				 * 409 - Conflict - The request conflicts with another request (perhaps due to using the same idempotent key).
				 * 412 - VIES Service Unavailable - The EU VIES service is temporarily unavailable. Please try again later.
				 * 429 - Too Many Requests - Too many requests hit the API too quickly. We recommend an exponential backoff of your requests.
				 * 500, 502, 503, 504 - Server Errors - Something went wrong on our end. (These are rare.)
				 */

			}
		}

		alg_wc_eu_vat_debug_log(
			__( 'Error: Something went wrong', 'eu-vat-for-woocommerce' ),
			array(
				'Country' => $country_code,
				'VAT ID'  => $vat_number,
			)
		);
		return null;

	}
}

if ( ! function_exists( 'alg_wc_eu_vat_validate_vat_uk' ) ) {
	/**
	 * alg_wc_eu_vat_validate_vat_uk.
	 *
	 * @version 4.3.1
	 * @since   1.0.0
	 *
	 * @return  mixed: bool on successful checking, null otherwise
	 *
	 * @todo    (v4.3.2) fix (HMRC v2)!
	 * @todo    (dev) check for minimal length?
	 */
	function alg_wc_eu_vat_validate_vat_uk( $country_code, $vat_number, $billing_company = '', $method = '' ) {
		$country_code = strtoupper( $country_code );
		$api_url      = "https://api.service.hmrc.gov.uk/organisations/vat/check-vat-number/lookup/" . $vat_number;
		switch ( $method ) {
			case 'file_get_contents':
				if ( ini_get( 'allow_url_fopen' ) ) {
					$response = file_get_contents( $api_url );
				} else {
					alg_wc_eu_vat_log(
						$country_code,
						$vat_number,
						$billing_company,
						$method,
						sprintf(
							/* Translators: %s: allow_url_fopen. */
							__( 'Error: %s is disabled', 'eu-vat-for-woocommerce' ),
							'allow_url_fopen'
						)
					);
					return null;
				}
				break;
			default: // 'curl'
				if ( function_exists( 'curl_version' ) ) {
					// phpcs:disable WordPress.WP.AlternativeFunctions.curl_curl_init, WordPress.WP.AlternativeFunctions.curl_curl_setopt, WordPress.WP.AlternativeFunctions.curl_curl_exec, WordPress.WP.AlternativeFunctions.curl_curl_close
					$curl = curl_init( $api_url );
					curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
					if ( apply_filters( 'alg_wc_eu_vat_validation_curl_disable_ssl', false ) ) {
						curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
						curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
					}
					$response = curl_exec( $curl );
					curl_close( $curl );
					// phpcs:enable WordPress.WP.AlternativeFunctions.curl_curl_init, WordPress.WP.AlternativeFunctions.curl_curl_setopt, WordPress.WP.AlternativeFunctions.curl_curl_exec, WordPress.WP.AlternativeFunctions.curl_curl_close
				} else {
					alg_wc_eu_vat_log(
						$country_code,
						$vat_number,
						$billing_company,
						$method,
						sprintf(
							/* Translators: %s: cURL. */
							__( 'Error: %s is disabled', 'eu-vat-for-woocommerce' ),
							'cURL'
						)
					);
					return null;
				}
				break;
		}

		if ( false === $response ) {
			alg_wc_eu_vat_log(
				$country_code,
				$vat_number,
				$billing_company,
				$method,
				__( 'Error: No response', 'eu-vat-for-woocommerce' )
			);

			return null;
		}

		$responsedecode = json_decode( $response, true );
		if ( isset( $responsedecode['target'] ) ) {
			$responsetarget = $responsedecode['target'];
		} else {
			$responsetarget = '';
		}

		// API error
		if ( isset( $responsedecode['code'] ) ) {
			alg_wc_eu_vat_log(
				$country_code,
				$vat_number,
				$billing_company,
				$method,
				$responsedecode['message']
			);

			switch ( $responsedecode['code'] ) {
				case "INVALID_REQUEST":
				case "NOT_FOUND":
					return false;
				default:
					return null;
			}
		}

		// Company name
		if (
			'yes' === apply_filters( 'alg_wc_eu_vat_check_company_name', 'no' ) &&
			isset( $responsedecode['target'] )
		) {
			if ( isset( $responsetarget['name'] ) ) {
				$company_name = strtolower( $responsetarget['name'] );
			} else {
				$company_name = '';
			}
		} else {
			$company_name = '';
		}

		// Final result
		$return = (
			isset( $responsedecode['target'] ) &&
			(
				'no' === apply_filters( 'alg_wc_eu_vat_check_company_name', 'no' ) ||
				$company_name === $billing_company
			)
		);
		if ( ! $return ) {
			if ( $responsedecode['target'] ) {
				alg_wc_eu_vat_log(
					$country_code,
					$vat_number,
					$billing_company,
					$method,
					sprintf(
						/* Translators: %s: Company name. */
						__( 'Error: Company name does not match (%s)', 'eu-vat-for-woocommerce' ),
						$company_name
					)
				);

				alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check_company_name', $company_name );
				alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_to_check_company', true );

			} else {
				alg_wc_eu_vat_log(
					$country_code,
					$vat_number,
					$billing_company,
					$method,
					__( 'Error: VAT ID not valid', 'eu-vat-for-woocommerce' )
				);
			}
		} else {
			alg_wc_eu_vat_log(
				$country_code,
				$vat_number,
				$billing_company,
				$method,
				__( 'Success: VAT ID is valid', 'eu-vat-for-woocommerce' )
			);
		}

		// Store result to session
		alg_wc_eu_vat_store_validation_session(
			$country_code,
			$vat_number,
			$return,
			$billing_company,
			$responsedecode
		);

		return $return;
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_store_validation_session' ) ) {
	/**
	 * alg_wc_eu_vat_store_validation_session.
	 *
	 * @version 4.0.0
	 * @since   2.9.22
	 *
	 * @return  mixed: array of stored value
	 *
	 * @todo    (dev) store VAT number validation result to reduce request number to VIES
	 */
	function alg_wc_eu_vat_store_validation_session( $country_code, $vat_number, $status, $billing_company = '', $api_response = array() ) {
		$store_array_each = array(
			'country_code'    => $country_code,
			'vat_number'      => $vat_number,
			'billing_company' => $billing_company,
			'status'          => $status,
		);

		alg_wc_eu_vat_response( (array) $api_response, $country_code );

		if ( 'yes' !== get_option( 'alg_wc_eu_vat_reduce_concurrent_request_enable', 'no' ) ) {
			return $store_array_each;
		}

		$validation_saved = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_validation_storage', array() );

		if ( empty( $validation_saved ) ) {
			$validation_saved = array();
		}

		if ( is_array( $validation_saved ) && isset( $validation_saved[ $vat_number ] ) ) {
			return $store_array_each;
		}

		$validation_saved[ $vat_number ] = $store_array_each;

		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_validation_storage', $validation_saved );
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_validate_from_session' ) ) {
	/**
	 * alg_wc_eu_vat_validate_from_session.
	 *
	 * @version 2.10.1
	 * @since   2.9.22
	 *
	 * @return  mixed: bool on successful checking, null otherwise
	 *
	 * @todo    (dev) validate vat number from session to reduce request number to VIES
	 */
	function alg_wc_eu_vat_validate_from_session( $country_code, $vat_number, $billing_company = '' ) {
		if ( 'yes' !== get_option( 'alg_wc_eu_vat_reduce_concurrent_request_enable', 'no' ) ) {
			return false;
		}

		$validation_results = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_validation_storage', array() );
		if (
			! empty( $vat_number ) &&
			! empty( $validation_results ) &&
			is_array( $validation_results ) &&
			isset( $validation_results[ $vat_number ] )
		) {
			if ( '1' == $validation_results[ $vat_number ]['status'] ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_response' ) ) {
	/**
	 * Processes VAT response data and updates VAT details.
	 *
	 * @version 4.2.5
	 * @since   4.0.0
	 *
	 * @param array $vat_response_data The VAT response data received.
	 * @param string $country_code The country code associated with the VAT response.
	 *
	 * @return void
	 */
	function alg_wc_eu_vat_response( $vat_response_data, $country_code ) {
		$details = array(
			'business_name'    => array(
				'label' => __( 'Business Name', 'eu-vat-for-woocommerce' ),
			),
			'business_address' => array(
				'label' => __( 'Business Address', 'eu-vat-for-woocommerce' ),
			),
			'country_code'     => array(
				'label' => __( 'Country Code', 'eu-vat-for-woocommerce' ),
			),
			'vat_number'       => array(
				'label' => __( 'VAT Number', 'eu-vat-for-woocommerce' ),
			)
		);

		if ( ! empty( $vat_response_data['target'] ) ) {
			$data    = $vat_response_data['target'];
			$details = alg_wc_eu_vat_get_details( $data, $details, $country_code );
		} elseif (
			( ! empty( $vat_response_data['isValid'] ) && true === $vat_response_data['isValid'] ) ||
			( ! empty( $vat_response_data['valid'] )   && true === $vat_response_data['valid'] )
		) {
			$details = alg_wc_eu_vat_get_details( $vat_response_data, $details, $country_code );
		} else {
			alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_details', null );
			alg_wc_eu_vat()->core->vat_details_data = null;
			return;
		}

		alg_wc_eu_vat_session_set( 'alg_wc_eu_vat_details', $details );
		alg_wc_eu_vat()->core->vat_details_data = $details;
	}
}

if ( ! function_exists( 'alg_wc_eu_vat_get_details' ) ) {
	/**
	 * Retrieves and sets VAT details from the VAT response data.
	 *
	 * @version 4.2.2
	 * @since   4.0.0
	 *
	 * @param       $vat_response_data
	 * @param array $details
	 * @param       $country_code
	 *
	 * @return array
	 */
	function alg_wc_eu_vat_get_details( $vat_response_data, $details, $country_code ) {
		if (
			! empty( $vat_response_data['address'] ) &&
			is_array( $vat_response_data['address'] )
		) {
			$address_lines = [];

			if ( ! empty( $vat_response_data['address']['line1'] ) ) {
				$address_lines[] = $vat_response_data['address']['line1'];
			}
			if ( ! empty( $vat_response_data['address']['line2'] ) ) {
				$address_lines[] = $vat_response_data['address']['line2'];
			}
			if ( ! empty( $vat_response_data['address']['line3'] ) ) {
				$address_lines[] = $vat_response_data['address']['line3'];
			}
			if ( ! empty( $vat_response_data['address']['postcode'] ) ) {
				$address_lines[] = $vat_response_data['address']['postcode'];
			}

			// If any address lines are available, join them into a single address string
			if ( ! empty( $address_lines ) ) {
				$vat_response_data['address'] = implode( ', ', $address_lines );
			} else {
				$vat_response_data['address'] = '';
			}
		}

		$details['vat_number']['data']       = ! empty( $vat_response_data['vatNumber'] ) ? $vat_response_data['vatNumber'] : '';
		$details['business_name']['data']    = ! empty( $vat_response_data['name'] )      ? $vat_response_data['name']      : '';
		$details['business_address']['data'] = ! empty( $vat_response_data['address'] )   ? $vat_response_data['address']   : '';
		$details['country_code']['data']     = $country_code;

		return $details;
	}
}
