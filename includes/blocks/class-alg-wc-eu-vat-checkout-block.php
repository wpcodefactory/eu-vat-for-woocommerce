<?php
/**
 * EU VAT for WooCommerce - Checkout Block Class
 *
 * @version 4.2.0
 * @since   4.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Checkout_Block' ) ) :

class Alg_WC_EU_VAT_Checkout_Block {

	/**
	 * Constructor.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function __construct() {

		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'woocommerce_init', array( $this, 'register_additional_checkout_block_field' ), PHP_INT_MAX );
		add_action( 'woocommerce_init', array( $this, 'alg_wc_eu_woocommerce_store_api_register_update_callback' ), 10 );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'alg_eu_vat_update_block_order_meta_eu_vat' ), 10, 2 );
		add_action( 'woocommerce_blocks_validate_location_contact_fields', array( $this, 'alg_wc_eu_woocommerce_validate_eu_vat_field_checkout_block' ), 10, 3 );

		// Default value
		add_filter( 'woocommerce_get_default_value_for_alg_eu_vat/billing_eu_vat_number', array( $this, 'alg_eu_vat_update_default_value_for_eu_vat_field' ), 99, 3 );

		// Deregister
		add_action( 'wp', array( $this, 'deregister_field_if_not_checkout' ) );

		// User meta
		add_action( 'woocommerce_created_customer', array( $this, 'save_user_meta' ), PHP_INT_MAX );
		add_action( 'woocommerce_customer_save_address', array( $this, 'save_user_meta_customer_save_address' ), PHP_INT_MAX, 2 );

	}

	/**
	 * is_enabled.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function is_enabled() {
		return (
			'yes' === get_option( 'alg_wc_eu_vat_enable_checkout_block_field', 'no' ) &&
			version_compare( get_option( 'woocommerce_version', null ), '8.9.1', '>=' )
		);
	}

	/**
	 * get_block_field_id.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function get_block_field_id() {
		return '_wc_other/alg_eu_vat/' . alg_wc_eu_vat_get_field_id();
	}

	/**
	 * save_user_meta.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function save_user_meta( $user_id ) {
		$field_id = alg_wc_eu_vat_get_field_id();
		if ( isset( $_POST[ $field_id ] ) ) {
			$value = sanitize_text_field( wp_unslash( $_POST[ $field_id ] ) );
			update_user_meta( $user_id, $this->get_block_field_id(), $value );
		}
	}

	/**
	 * save_user_meta_customer_save_address.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function save_user_meta_customer_save_address( $user_id, $load_address ) {
		if ( 'billing' === $load_address ) {
			$this->save_user_meta( $user_id );
		}
	}

	/**
	 * deregister_field_if_not_checkout.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function deregister_field_if_not_checkout() {
		if (
			function_exists( '__internal_woocommerce_blocks_deregister_checkout_field' ) &&
			! is_checkout()
		) {
			__internal_woocommerce_blocks_deregister_checkout_field( 'alg_eu_vat' . '/' . alg_wc_eu_vat_get_field_id() );
		}
	}

	/**
	 * register_additional_checkout_block_field.
	 *
	 * @version 4.0.0
	 * @since   2.11.6
	 */
	function register_additional_checkout_block_field() {

		if ( is_admin() ) {
			return;
		}

		$field_attr = alg_wc_eu_vat()->core->get_field_data();
		$field_id   = alg_wc_eu_vat_get_field_id();

		woocommerce_register_additional_checkout_field(
			array(
				'id'            => 'alg_eu_vat' . '/' . $field_id,
				'label'         => $field_attr['label'],
				'location'      => 'contact',
				'required'      => $field_attr['required'],
				'attributes'    => array(
					'autocomplete' => 'on',
					'title'        => $field_attr['description'],
				),
			),
		);

	}

	/**
	 * alg_wc_eu_woocommerce_store_api_register_update_callback.
	 *
	 * @version 4.0.0
	 * @since   2.10.4
	 */
	 function alg_wc_eu_woocommerce_store_api_register_update_callback() {

		woocommerce_store_api_register_update_callback(
			[
				'namespace' => 'alg-wc-eu-vat-extension-namespace',
				'callback'  => function ( $data ) {
					$country               = $data['eu_country'];
					$same_billing_shipping = $data['same_billing_shipping'];
					if ( ! empty( $country ) ) {
						WC()->customer->set_billing_country( wc_clean( $country ) );
						if ( isset( $same_billing_shipping ) && 'yes' == $same_billing_shipping ) {
							WC()->customer->set_shipping_country( wc_clean( $country ) );
						}
					}
					return;
				}
			]
		);

		woocommerce_store_api_register_update_callback(
			[
				'namespace' => 'alg-wc-eu-vat-extension-namespace-reload-first',
				'callback'  => function ( $data ) {
					return;
				}
			]
		);

	}

	/**
	 * alg_eu_vat_update_block_order_meta_eu_vat.
	 *
	 * @version 4.2.0
	 * @since   2.10.4
	 *
	 * @todo    (dev) `eu-vat-for-woocommerce-block-example`: rename
	 */
	function alg_eu_vat_update_block_order_meta_eu_vat( $order, $request ) {

		$field_id = alg_wc_eu_vat_get_field_id();

		$data = ( $request['extensions']['eu-vat-for-woocommerce-block-example'] ?? array() );

		$billing_address  = $order->get_address( 'billing' );
		$shipping_address = $order->get_address( 'shipping' );

		$posted_billing_country  = $billing_address['country'];
		$posted_shipping_country = $shipping_address['country'];

		$posted_billing_company = $billing_address['company'];

		$posted_eu_vat_id = $order->get_meta( $this->get_block_field_id() );

		$is_valid = false;

		if ( 'yes' === get_option( 'alg_wc_eu_vat_validate', 'yes' ) ) {
			if (
				( '' != $posted_eu_vat_id  ) &&
				(
					null === alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_valid' ) ||
					false == alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_valid' ) ||
					null === alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' ) ||
					$posted_eu_vat_id  != alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' )
				)
			) {

				$is_valid = false;
				if (
					'yes' === get_option( 'alg_wc_eu_vat_force_checkout_recheck', 'no' ) &&
					$posted_eu_vat_id  != alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' )
				) {
					$is_valid = alg_wc_eu_vat()->core->check_and_save_eu_vat(
						$posted_eu_vat_id ,
						( $posted_billing_country ?? '' ),
						( $posted_billing_company ?? '' )
					);
				} else {

					$vat_number      = $posted_eu_vat_id;
					$billing_country = ( $posted_billing_country ?? '' );
					$billing_company = ( $posted_billing_company ?? '' );
					$vat_number      = preg_replace( '/\s+/', '', $vat_number );
					$eu_vat_number   = alg_wc_eu_vat_parse_vat( $vat_number, $billing_country );

					// VAT validate manually pre-saved number
					if ( 'yes' === get_option( 'alg_wc_eu_vat_manual_validation_enable', 'no' ) ) {
						if ( '' != ( $manual_validation_vat_numbers = get_option( 'alg_wc_eu_vat_manual_validation_vat_numbers', '' ) ) ) {
							$prevalidated_VAT_numbers = array();
							$prevalidated_VAT_numbers = explode( ',', $manual_validation_vat_numbers );
							$sanitized_vat_numbers    = array_map( 'trim', $prevalidated_VAT_numbers );
							$conjuncted_vat_number    = $billing_country . $eu_vat_number['number'];
							if ( isset( $sanitized_vat_numbers[0] ) ) {
								if ( in_array( $conjuncted_vat_number, $sanitized_vat_numbers ) ) {
									alg_wc_eu_vat_maybe_log(
										$eu_vat_number['country'],
										$eu_vat_number['number'],
										$billing_company,
										'',
										__( 'Success (checkout): VAT ID valid. Matched with pre-validated VAT numbers.', 'eu-vat-for-woocommerce' )
									);
									$is_valid = true;
								}
							}
						}
					}
				}

				if ( 'no' != ( $preserve_option_value = get_option( 'alg_wc_eu_vat_preserv_vat_for_different_shipping', 'no' ) ) ) {
					$billing_country  = $posted_billing_country;
					$shipping_country = $posted_shipping_country;
					$is_country_same  = ( strtoupper( $billing_country ) !== strtoupper( $shipping_country ) );
					if ( ! $is_country_same && ! $is_valid ) {
						$is_valid = true;
					}
				}

				$is_valid = apply_filters( 'alg_wc_eu_vat_is_valid_vat_at_checkout', $is_valid );
				if ( ! $is_valid ) {

					alg_wc_eu_vat_maybe_log(
						( $posted_billing_country ?? '' ),
						$posted_eu_vat_id,
						( $posted_billing_company ?? '' ),
						'',
						__( 'Error: VAT is not valid (checkout)', 'eu-vat-for-woocommerce' )
					);

					throw new Exception(
						esc_html(
							wp_strip_all_tags(
								str_replace(
									'%eu_vat_number%',
									$posted_eu_vat_id,
									do_shortcode(
										get_option(
											'alg_wc_eu_vat_not_valid_message',
											__( '<strong>EU VAT Number</strong> is not valid.', 'eu-vat-for-woocommerce' )
										)
									)
								)
							)
						)
					);

				}

			}
		}

		if ( ! empty( $posted_eu_vat_id ) ) {

			$order->update_meta_data( '_billing_eu_vat_number', $posted_eu_vat_id );
			$order->delete_meta_data( $this->get_block_field_id() );

			$vat_details_response_data = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_details' );
			$order->update_meta_data( alg_wc_eu_vat_get_field_id() . '_details', $vat_details_response_data );

			$vat_response_data = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_response_data' );
			if ( isset( $vat_response_data->requestIdentifier ) ) {
				$order->update_meta_data(
					apply_filters(
						'alg_wc_eu_vat_request_identifier_meta_key',
						alg_wc_eu_vat_get_field_id() . '_request_identifier'
					),
					$vat_response_data->requestIdentifier
				);
			}

		}

		if ( $is_valid ) {
			$order->update_meta_data( 'is_vat_exempt', 'yes' );
		}

		if ( ( $user_id = $order->get_user_id() ) ) {
			update_user_meta(
				$user_id,
				alg_wc_eu_vat_get_field_id(),
				( $posted_eu_vat_id ?? '' )
			);
		}

	}

	/**
	 * alg_wc_eu_woocommerce_validate_eu_vat_field_checkout_block.
	 *
	 * @version 4.0.0
	 * @since   2.11.6
	 *
	 * @todo    (dev) `%eu_vat_number%`?
	 */
	function alg_wc_eu_woocommerce_validate_eu_vat_field_checkout_block( \WP_Error $errors, $fields, $group ) {

		$field_id   = alg_wc_eu_vat_get_field_id();
		$field_attr = alg_wc_eu_vat()->core->get_field_data();

		$field_with_namespace = 'alg_eu_vat' . '/' . $field_id;

		if (
			isset( $field_attr['required'] ) &&
			$field_attr['required'] &&
			empty( $fields[ $field_with_namespace ] )
		) {
			$error_message = str_replace(
				'%eu_vat_number%',
				$fields[ $field_with_namespace ],
				do_shortcode(
					get_option(
						'alg_wc_eu_vat_not_valid_message',
						__( '<strong>EU VAT Number</strong> is required.', 'eu-vat-for-woocommerce' )
					)
				)
			);
			$errors->add( 'eu_vat_required', $error_message );
		}

	}

	/**
	 * alg_eu_vat_update_default_value_for_eu_vat_field.
	 *
	 * @version 3.2.4
	 * @since   2.11.6
	 */
	function alg_eu_vat_update_default_value_for_eu_vat_field( $value, $group, $wc_object ) {
		return (
			is_a( $wc_object, 'WC_Customer' ) ?
			$wc_object->get_meta( 'billing_eu_vat_number' ) :
			$value
		);
	}

}

endif;

return new Alg_WC_EU_VAT_Checkout_Block();
