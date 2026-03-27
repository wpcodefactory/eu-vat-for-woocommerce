<?php
/**
 * EU VAT for WooCommerce - Checkout Block Class
 *
 * @version 4.5.9
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
	 * @version 4.3.1
	 * @since   4.0.0
	 */
	function __construct() {

		// Is enabled?
		if ( ! alg_wc_eu_vat_is_checkout_block_enabled() ) {
			return;
		}

		// Register checkout field
		add_action(
			'woocommerce_init',
			array( $this, 'register_additional_checkout_block_field' ),
			PHP_INT_MAX
		);

		// Register update callback
		add_action(
			'woocommerce_init',
			array( $this, 'store_api_register_update_callback' ),
			10
		);

		// Update order meta
		add_action(
			'woocommerce_store_api_checkout_update_order_from_request',
			array( $this, 'update_block_order_meta_eu_vat' ),
			10,
			2
		);

		// Validate
		add_action(
			'woocommerce_blocks_validate_location_contact_fields',
			array( $this, 'validate_eu_vat_field_checkout_block' ),
			10,
			3
		);

		// Default value
		add_filter(
			'woocommerce_get_default_value_for_alg_eu_vat/billing_eu_vat_number',
			array( $this, 'update_default_value_for_eu_vat_field' ),
			99,
			3
		);

		// Deregister
		add_action(
			'wp',
			array( $this, 'deregister_field_if_not_checkout' )
		);

		// User meta
		add_action(
			'woocommerce_created_customer',
			array( $this, 'save_user_meta' ),
			PHP_INT_MAX
		);
		add_action(
			'woocommerce_customer_save_address',
			array( $this, 'save_user_meta_customer_save_address' ),
			PHP_INT_MAX,
			2
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
		if ( isset( $_POST[ $field_id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$value = sanitize_text_field( wp_unslash( $_POST[ $field_id ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
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
	 * @version 4.2.5
	 * @since   4.0.0
	 */
	function deregister_field_if_not_checkout() {
		if (
			function_exists( '__internal_woocommerce_blocks_deregister_checkout_field' ) &&
			! alg_wc_eu_vat_is_checkout()
		) {
			__internal_woocommerce_blocks_deregister_checkout_field(
				'alg_eu_vat' . '/' . alg_wc_eu_vat_get_field_id()
			);
		}
	}

	/**
	 * register_additional_checkout_block_field.
	 *
	 * @version 4.5.9
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
				'optionalLabel' => $field_attr['label'],
				'location'      => 'contact',
				'required'      => 'yes' === get_option( 'alg_wc_eu_vat_field_required', 'no' ),
				'attributes'    => array(
					'autocomplete' => 'on',
					'title'        => $field_attr['description'],
				),
			),
		);

	}

	/**
	 * store_api_register_update_callback.
	 *
	 * @version 4.5.9
	 * @since   2.10.4
	 */
	 function store_api_register_update_callback() {

		woocommerce_store_api_register_update_callback(
			array(
				'namespace' => 'alg-wc-eu-vat-extension-namespace',
				'callback'  => function ( $data ) {
					$country               = $data['billing_country'];
					$same_billing_shipping = $data['same_billing_shipping'];
					if ( ! empty( $country ) ) {
						WC()->customer->set_billing_country( wc_clean( $country ) );
						if (
							isset( $same_billing_shipping ) &&
							'yes' == $same_billing_shipping
						) {
							WC()->customer->set_shipping_country( wc_clean( $country ) );
						}
					}

					// Update billing company
					if ( isset( $data['billing_company'] ) ) {
						WC()->customer->set_billing_company( wc_clean( $data['billing_company'] ) );
						if (
							isset( $same_billing_shipping ) &&
							'yes' === $same_billing_shipping
						) {
							WC()->customer->set_shipping_company( wc_clean( $data['billing_company'] ) );
						}
					}

					$result = alg_wc_eu_vat()->core->vat_validation( $data );
					alg_wc_eu_vat_session_set( 'alg_eu_vat_validation', $result );

					return true;
				}
			)
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
	 * update_block_order_meta_eu_vat.
	 *
	 * @version 4.5.9
	 * @since   2.10.4
	 *
	 * @todo    (dev) `eu-vat-for-woocommerce-block-example`: rename
	 */
	function update_block_order_meta_eu_vat( $order, $request ) {

		$field_id         = alg_wc_eu_vat_get_field_id();
		$posted_eu_vat_id = $order->get_meta( $this->get_block_field_id() );

		$data = array(
			'vat_number'       => $posted_eu_vat_id,
			'billing_country'  => $order->get_billing_country(),
			'shipping_country' => $order->get_shipping_country(),
			'billing_company'  => $order->get_billing_company(),
		);

		// Let customer decide to skip
		$customer_decide_key = $field_id . '_customer_decide';
		if ( $order->get_meta( '_' . $customer_decide_key ) ) {
			$data[ $customer_decide_key ] = $order->get_meta( '_' . $customer_decide_key );
		}

		// Belgium compatibility: valid VAT but not exempt
		$belgium_key = $field_id . '_valid_vat_but_not_exempted';
		if ( $order->get_meta( '_' . $belgium_key ) ) {
			$data[ $belgium_key ] = $order->get_meta( '_' . $belgium_key );
		}

		$result = alg_wc_eu_vat()->core->vat_validation( $data );

		if ( ! $result['is_validate'] ) {
			// Block checkout shows one error at a time
			throw new \Exception( alg_wc_eu_vat()->core->vat_error_message( $posted_eu_vat_id ) );
		}

		if ( $result['is_validate'] && $result['is_vat_exempt'] ) {
			$vat_required_products = array();
			// Check if any products have the "Keep VAT" option enabled
			foreach ( $order->get_items() as $item ) {
				$product_id  = $item->get_variation_id() ?: $item->get_product_id();
				$do_keep_vat = get_post_meta( $product_id, '_alg_wc_eu_vat_keep_vat', true );

				if ( 'yes' === $do_keep_vat ) {
					$vat_required_products[] = $item->get_product_id();
				}
			}

			// If there are any VAT-required products, keep VAT for the customer
			if ( ! empty( $vat_required_products ) ) {
				foreach ( $order->get_items() as $item_id => $item ) {
					$product_id = $item->get_product_id();

					if ( ! in_array( $product_id, $vat_required_products, true ) ) {
						$item->set_tax_class( 0 );
						$item->set_taxes( [] );
						$item->save();
					}
				}
				$order->calculate_totals();
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

		if ( ( $user_id = $order->get_user_id() ) ) {
			update_user_meta(
				$user_id,
				alg_wc_eu_vat_get_field_id(),
				( $posted_eu_vat_id ?? '' )
			);
		}
	}

	/**
	 * validate_eu_vat_field_checkout_block.
	 *
	 * @version 4.5.9
	 * @since   2.11.6
	 *
	 * @todo    (dev) `%eu_vat_number%`?
	 */
	function validate_eu_vat_field_checkout_block( \WP_Error $errors, $fields, $group ) {

		$field_id             = alg_wc_eu_vat_get_field_id();
		$field_with_namespace = 'alg_eu_vat' . '/' . $field_id;

		if (
			'yes' === get_option( 'alg_wc_eu_vat_field_required', 'no' ) &&
			empty( $fields[ $field_with_namespace ] )
		) {
			$error_message = alg_wc_eu_vat()->core->vat_error_message( $fields[ $field_with_namespace ] );
			$errors->add( 'eu_vat_required', $error_message );
		}

	}

	/**
	 * update_default_value_for_eu_vat_field.
	 *
	 * @version 4.4.7
	 * @since   2.11.6
	 */
	function update_default_value_for_eu_vat_field( $value, $group, $wc_object ) {
		return (
			null !== ( $session_value = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_to_check' ) ) ?
			$session_value :
			(
				is_a( $wc_object, 'WC_Customer' ) ?
				$wc_object->get_meta( 'billing_eu_vat_number' ) :
				$value
			)
		);
	}

}

endif;

return new Alg_WC_EU_VAT_Checkout_Block();
