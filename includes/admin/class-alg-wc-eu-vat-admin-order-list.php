<?php
/**
 * EU VAT for WooCommerce - Admin Order List
 *
 * @version 4.2.2
 * @since   4.2.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Admin_Order_List' ) ) :

class Alg_WC_EU_VAT_Admin_Order_List {

	/**
	 * Constructor.
	 *
	 * @version 4.2.0
	 * @since   4.2.0
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_eu_vat_add_order_list_column', 'no' ) ) {

			// Column
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_columns' ), PHP_INT_MAX );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_order_columns' ), PHP_INT_MAX );

			// HPOS column
			add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'add_order_columns' ), PHP_INT_MAX );
			add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'render_wc_order_columns' ), PHP_INT_MAX, 2 );

			// Filter
			add_action( 'restrict_manage_posts', array( $this, 'display_admin_shop_order_by_meta_filter' ), PHP_INT_MAX );

			// HPOS filter
			add_action( 'woocommerce_order_list_table_restrict_manage_orders', array( $this, 'display_admin_shop_order_by_meta_filter_hpos' ), PHP_INT_MAX, 2 );

			add_filter( 'request', array( $this, 'process_admin_shop_order_marketing_by_meta' ), 99 );
			add_filter( 'woocommerce_shop_order_search_fields',  array( $this, 'shop_order_meta_search_fields') );

			// Filter query
			add_filter( 'pre_get_posts', array( $this, 'euvat_filter_orders' ), 100 );

			// HPOS filter query
			add_filter( 'woocommerce_order_list_table_prepare_items_query_args', array( $this, 'euvat_filter_orders_hpos' ), 100 );

		}
	}

	/**
	 * add_order_columns.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function add_order_columns( $columns ) {
		$columns['alg_wc_eu_vat'] = __( 'EU VAT', 'eu-vat-for-woocommerce' );
		return $columns;
	}

	/**
	 * render_order_columns.
	 *
	 * @version 4.1.0
	 * @since   1.5.0
	 */
	function render_order_columns( $column ) {
		if ( 'alg_wc_eu_vat' === $column ) {
			echo esc_html( get_post_meta( get_the_ID(), '_'. alg_wc_eu_vat_get_field_id(), true ) );
			if ( 'yes' === get_post_meta( get_the_ID(), 'is_vat_exempt', true ) ) {
				echo ' &#10004;';
			}
		}
	}

	/**
	 * render_wc_order_columns.
	 *
	 * @version 4.1.0
	 * @since   2.11.3
	 */
	function render_wc_order_columns( $column, $order ) {
		if ( 'alg_wc_eu_vat' === $column ) {
			$key           = '_'. alg_wc_eu_vat_get_field_id();
			$vat_value     = $order->get_meta( $key );
			$is_vat_exempt = $order->get_meta( 'is_vat_exempt' );
			if ( ! empty( $vat_value ) ) {
				echo esc_html( $vat_value );
			}
			if ( 'yes' === $is_vat_exempt ) {
				echo ' &#10004;';
			}
		}
	}

	/**
	 * display_admin_shop_order_by_meta_filter_hpos.
	 *
	 * @version 4.1.0
	 * @since   2.11.7
	 */
	function display_admin_shop_order_by_meta_filter_hpos( $post_type, $which ) {

		if( 'shop_order' !== $post_type ) {
			return;
		}

		$domain    = 'eu-vat-for-woocommerce';
		$filter_id = 'filter_shop_order_by_meta';
		$current   = (
			isset( $_GET[ $filter_id ] ) ?
			sanitize_text_field( wp_unslash( $_GET[ $filter_id ] ) ) :
			''
		);

		echo '<select name="' . esc_attr( $filter_id ) . '">' .
			'<option value="">' .
				esc_html__( 'Select Filter EU VAT...', 'eu-vat-for-woocommerce' ) .
			'</option>';

		$options = $this->get_filter_shop_order_meta( $domain );

		foreach ( $options as $key => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $key ),
				$key === $current ? '" selected="selected"' : '',
				esc_html( $label )
			);
		}
		echo '</select>';

	}

	/**
	 * display_admin_shop_order_by_meta_filter.
	 *
	 * @version 4.1.0
	 * @since   1.5.0
	 */
	function display_admin_shop_order_by_meta_filter() {
		global $pagenow, $typenow;

		if( 'shop_order' === $typenow && 'edit.php' === $pagenow ) {
			$domain    = 'eu-vat-for-woocommerce';
			$filter_id = 'filter_shop_order_by_meta';
			$current   = (
				isset( $_GET[ $filter_id ] ) ?
				sanitize_text_field( wp_unslash( $_GET[ $filter_id ] ) ) :
				''
			);

			echo '<select name="' . esc_attr( $filter_id ) . '">' .
				'<option value="">' .
					esc_html__( 'Select Filter EU VAT...', 'eu-vat-for-woocommerce' ) .
				'</option>';

			$options = $this->get_filter_shop_order_meta( $domain );

			foreach ( $options as $key => $label ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $key ),
					$key === $current ? '" selected="selected"' : '',
					esc_html( $label )
				);
			}
			echo '</select>';
		}
	}

	/**
	 * process_admin_shop_order_marketing_by_meta.
	 *
	 * @version 4.1.0
	 * @since   1.5.0
	 */
	function process_admin_shop_order_marketing_by_meta( $vars ) {
		global $pagenow, $typenow;
		$filter_id = 'filter_shop_order_by_meta';
		if (
			'edit.php' === $pagenow &&
			'shop_order' === $typenow &&
			! empty( $_GET[ $filter_id ] )
		) {
			$vars['meta_key'] = sanitize_text_field( wp_unslash( $_GET[ $filter_id ] ) );
			$vars['orderby']  = 'meta_value';
		}
		return $vars;
	}

	/**
	 * shop_order_meta_search_fields.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function shop_order_meta_search_fields( $meta_keys ) {
		foreach ( $this->get_filter_shop_order_meta() as $meta_key => $label ) {
			$meta_keys[] = $meta_key;
		}
		return $meta_keys;
	}

	/**
	 * euvat_filter_orders_hpos.
	 *
	 * @version 4.2.2
	 * @since   2.11.7
	 */
	function euvat_filter_orders_hpos( $query_args ) {

		$filter_id = 'filter_shop_order_by_meta';

		if ( ! empty( $_GET[ $filter_id ] ) ) {
			$query_args['meta_query'] = array(
				array(
					'key'     => '_billing_eu_vat_number',
					'value'   => array( '' ),
					'compare' => 'NOT IN',
				)
			);
		}

		return $query_args;
	}

	/**
	 * euvat_filter_orders.
	 *
	 * @version 3.2.0
	 * @since   2.11.7
	 */
	function euvat_filter_orders($query) {
		global $pagenow, $typenow;

		$filter_id = 'filter_shop_order_by_meta';

		$qv = &$query->query_vars;

		if (
			'edit.php' === $pagenow &&
			'shop_order' === $typenow &&
			! empty( $_GET[ $filter_id ] )
		) {
			if ( 'shop_order' === $qv['post_type'] ) {
				$query->set( 'meta_key', '_billing_eu_vat_number' );
				$query->set( 'meta_value', array( '' ) );
				$query->set( 'meta_compare', 'NOT IN' );
			}
		}

		return $query;
	}

	/**
	 * get_filter_shop_order_meta.
	 *
	 * @version 4.0.0
	 * @since   1.5.0
	 */
	function get_filter_shop_order_meta( $domain = 'woocommerce' ) {
		// Add below the metakey / label pairs to filter orders
		return [
			'_billing_eu_vat_number' => __( 'Orders with EU VAT numbers', 'eu-vat-for-woocommerce' )
		];
	}

}

endif;

return new Alg_WC_EU_VAT_Admin_Order_List();
