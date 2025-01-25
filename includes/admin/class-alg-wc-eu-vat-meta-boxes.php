<?php
/**
 * EU VAT for WooCommerce - Meta Boxes
 *
 * @version 4.2.0
 * @since   4.2.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Meta_Boxes' ) ) :

class Alg_WC_EU_VAT_Meta_Boxes {

	/**
	 * Constructor.
	 *
	 * @version 4.2.0
	 * @since   4.2.0
	 */
	function __construct() {

		// EU VAT number summary on order edit page
		if ( 'yes' === get_option( 'alg_wc_eu_vat_add_order_edit_metabox', 'no' ) ) {

			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

			// "Validate VAT and remove taxes" button
			add_action( 'admin_init', array( $this, 'validate_vat_and_maybe_remove_taxes' ), PHP_INT_MAX );

			// Get VAT details
			add_action( 'admin_init', array( $this, 'get_vat_details' ), PHP_INT_MAX );
			add_action( 'admin_notices', array( $this, 'admin_notice' ), PHP_INT_MAX );

		}

		// Popup metabox
		add_action( 'add_meta_boxes', array( $this, 'add_popup_order_meta_box' ) );

	}

	/**
	 * Update the order vat details.
	 *
	 * @version 4.1.0
	 * @since   4.0.0
	 */
	function get_vat_details() {
		if ( isset( $_GET['get_vat_details'], $_GET['number'], $_GET['country'] ) ) {
			$order_id   = absint( $_GET['get_vat_details'] );
			$vat_number = sanitize_text_field( wp_unslash( $_GET['number'] ) );
			$country    = sanitize_text_field( wp_unslash( $_GET['country'] ) );
			$eu_vat_number = alg_wc_eu_vat_parse_vat( $vat_number, $country );
			$is_valid      = alg_wc_eu_vat_validate_vat( $eu_vat_number['country'], $eu_vat_number['number'] );
			if ( $is_valid ) {
				$vat_response_data = alg_wc_eu_vat_session_get( 'alg_wc_eu_vat_details' );
				$order = wc_get_order( $order_id );
				if ( $order ) {
					$order->update_meta_data( alg_wc_eu_vat_get_field_id() . '_details', $vat_response_data );
					$order->save();
					// Store success message in a transient
					set_transient( 'vat_details_success', __( 'VAT details have been updated successfully.', 'eu-vat-for-woocommerce' ) );
				}
			} else {
				set_transient( 'vat_details_error', __( 'VAT details update failed. Please update the valid VAT number.', 'eu-vat-for-woocommerce' ) );
			}
			wp_safe_redirect( remove_query_arg( array( 'get_vat_details', 'country', 'number' ) ) );
			exit;
		}
	}

	/**
	 * Admin notice.
	 *
	 * @version 4.1.0
	 * @since   4.0.0
	 */
	function admin_notice() {
		if ( $message = get_transient( 'vat_details_success' ) ) {
			echo '<div class="notice notice-success is-dismissible">';
			echo '<p>' . esc_html( $message ) . '</p>';
			echo '</div>';
			delete_transient( 'vat_details_success' );
		}
		if ( $message = get_transient( 'vat_details_error' ) ) {
			echo '<div class="notice notice-error is-dismissible">';
			echo '<p>' . esc_html( $message ) . '</p>';
			echo '</div>';
			delete_transient( 'vat_details_error' );
		}
	}

	/**
	 * add_popup_order_meta_box.
	 *
	 * @version 4.2.0
	 */
	function add_popup_order_meta_box() {
		 add_meta_box(
			'woocommerce_eu_vat_shop_order_popup',
			__( 'Check VAT Number', 'eu-vat-for-woocommerce' ),
			array( $this, 'create_popup_order_meta'),
			'shop_order',
			'side',
			'low'
		);
	}

	/**
	 * create_popup_order_meta.
	 *
	 * @version 4.2.0
	 */
	function create_popup_order_meta( $post ) {
		add_thickbox();
		?>
		<a href="https://ec.europa.eu/taxation_customs/vies?TB_iframe=true&width=772&height=485" class="thickbox button"><?php
			esc_html_e( 'Open VIES', 'eu-vat-for-woocommerce' );
		?></a>
		<?php
	}

	/**
	 * add_meta_box.
	 *
	 * @version 2.12.6
	 * @since   1.0.0
	 */
	function add_meta_box() {
		$current_screen = get_current_screen()->id;

		if (
			'shop_order' == $current_screen ||
			'woocommerce_page_wc-orders' == $current_screen
		) {
			add_meta_box(
				'alg-wc-eu-vat',
				__( 'EU VAT', 'eu-vat-for-woocommerce' ),
				array( $this, 'create_meta_box' ),
				$current_screen,
				'side',
				'low'
			);
		}
	}

	/**
	 * create_meta_box.
	 *
	 * @version 4.2.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) save actual EU VAT number used on checkout (instead of `$_order->get_meta( '_' . alg_wc_eu_vat_get_field_id() )`)
	 * @todo    (dev) add country flag?
	 */
	function create_meta_box( $object ) {

		$_order = is_a( $object, 'WP_Post' ) ? wc_get_order( $object->ID ) : $object;

		$_customer_ip_address = ( alg_wc_eu_vat()->core->is_wc_version_below_3_0_0 ? $_order->customer_ip_address : $_order->get_customer_ip_address() );

		// Country by IP
		$customer_country = alg_wc_eu_vat_get_customers_location_by_ip( $_customer_ip_address );

		// Customer EU VAT number
		if ( '' == ( $customer_eu_vat_number = $_order->get_meta( '_' . alg_wc_eu_vat_get_field_id() ) ) ) {
			$customer_eu_vat_number = '-';
		}

		// Taxes
		$taxes = '';
		$taxes_array = $_order->get_tax_totals();
		if ( empty( $taxes_array ) ) {
			$taxes = '-';
		} else {
			foreach ( $taxes_array as $tax ) {
				$taxes .= $tax->label . ': ' . $tax->formatted_amount . '<br>';
			}
		}

		// Results table
		$table_data = array(
			array(
				__( 'Customer IP', 'eu-vat-for-woocommerce' ),
				$_customer_ip_address,
			),
			array(
				__( 'Country by IP', 'eu-vat-for-woocommerce' ),
				alg_wc_eu_vat_get_country_name_by_code( $customer_country ) . ' [' . $customer_country . ']',
			),
			array(
				__( 'Customer EU VAT Number', 'eu-vat-for-woocommerce' ),
				$customer_eu_vat_number,
			),
			array(
				__( 'Taxes', 'eu-vat-for-woocommerce' ),
				$taxes,
			),
		);

		// VAT Details
		$customer_eu_vat_details = $_order->get_meta( alg_wc_eu_vat_get_field_id() . '_details' );
		if ( is_array( $customer_eu_vat_details ) ) {
			$table_data = array_merge(
				$table_data,
				array(
					array(
						__( 'Business Name', 'eu-vat-for-woocommerce' ),
						esc_html( $customer_eu_vat_details['business_name']['data'] ?? '' ),
					),
					array(
						__( 'Business Address', 'eu-vat-for-woocommerce' ),
						esc_html( $customer_eu_vat_details['business_address']['data'] ?? '' ),
					),
					array(
						__( 'Country Code', 'eu-vat-for-woocommerce' ),
						esc_html( $customer_eu_vat_details['country_code']['data'] ?? '' ),
					),
					array(
						__( 'VAT Number', 'eu-vat-for-woocommerce' ),
						esc_html( $customer_eu_vat_details['vat_number']['data'] ?? '' ),
					),
				)
			);
		}

		// Request Identifier
		$request_identifier = $_order->get_meta(
			apply_filters(
				'alg_wc_eu_vat_request_identifier_meta_key',
				alg_wc_eu_vat_get_field_id() . '_request_identifier'
			)
		);
		if ( '' !== $request_identifier ) {
			$table_data = array_merge(
				$table_data,
				array(
					array(
						__( 'Request Identifier', 'eu-vat-for-woocommerce' ),
						esc_html( $request_identifier ),
					),
				)
			);
		}

		// Output
		$order_id = $_order->get_id();
		echo alg_wc_eu_vat_get_table_html(
			$table_data,
			array(
				'table_class'        => 'widefat striped',
				'table_heading_type' => 'vertical',
			)
		);

		// Validate VAT and remove taxes
		echo '<p>' .
			'<a href="' . esc_url( add_query_arg( 'validate_vat_and_maybe_remove_taxes', absint( $order_id ) ) ) . '">' .
				esc_html__( 'Validate VAT and remove taxes', 'eu-vat-for-woocommerce' ) .
			'</a>' .
		'</p>';

		// Fetch VAT details and display the business name and address
		echo '<p>' .
			'<a href="' . esc_url( add_query_arg( array(
				'get_vat_details' => absint( $order_id ),
				'country'         => esc_html( $_order->get_billing_country() ),
				'number'          => esc_html( $customer_eu_vat_number ),
			) ) ) . '">' .
				esc_html__( 'Get VAT details', 'eu-vat-for-woocommerce' ) .
			'</a>' .
		'</p>';

	}

	/**
	 * validate_vat_and_maybe_remove_taxes.
	 *
	 * @version 4.1.0
	 * @since   1.0.0
	 */
	function validate_vat_and_maybe_remove_taxes() {
		$preserve_countries = alg_wc_eu_vat()->core->eu_vat_ajax_instance->get_preserve_countries();
		$preserve_countries_condition = false;

		if ( isset( $_GET['validate_vat_and_maybe_remove_taxes'] ) ) {
			$order_id = sanitize_text_field( wp_unslash( $_GET['validate_vat_and_maybe_remove_taxes'] ) );
			$order    = wc_get_order( $order_id );
			if ( $order ) {

				$vat_id          = $order->get_meta( '_' . alg_wc_eu_vat_get_field_id() );
				$billing_company = $order->get_meta( '_' . 'billing_company' );
				if ( '' != $vat_id ) {
					$eu_vat_number = alg_wc_eu_vat_parse_vat( $vat_id, $order->get_billing_country() );

					if ( ! empty( $preserve_countries ) ) {
						if ( in_array( $eu_vat_number['country'], $preserve_countries ) ) {
							$preserve_countries_condition = true;
						}
					}

					if (
						! $preserve_countries_condition &&
						alg_wc_eu_vat_validate_vat(
							$eu_vat_number['country'],
							$eu_vat_number['number'],
							$billing_company
						)
					) {
						foreach ( $order->get_items( array( 'line_item', 'fee' ) ) as $item_id => $item ) {
							$item->set_taxes( false );
						}
						foreach ( $order->get_shipping_methods() as $item_id => $item ) {
							$item->set_taxes( false );
						}
						$order->update_taxes();
						$order->calculate_totals( false );
					}
				}
			}
			wp_safe_redirect( remove_query_arg( 'validate_vat_and_maybe_remove_taxes' ) );
			exit;
		}
	}

}

endif;

return new Alg_WC_EU_VAT_Meta_Boxes();
