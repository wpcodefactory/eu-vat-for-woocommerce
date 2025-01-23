<?php
/**
 * EU VAT for WooCommerce - Admin Class
 *
 * @version 4.1.0
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Admin' ) ) :

class Alg_WC_EU_VAT_Admin {

	/**
	 * Constructor.
	 *
	 * @version 4.0.0
	 * @since   1.0.0
	 */
	function __construct() {

		// Admin order edit
		add_filter( 'woocommerce_admin_billing_fields', array( $this, 'add_to_admin_order_display' ), PHP_INT_MAX );

		// Admin order edit - "Load billing address" button
		add_filter( 'woocommerce_ajax_get_customer_details', array( $this, 'add_to_ajax_get_customer_details' ), PHP_INT_MAX, 3 );

		// EU VAT number summary on order edit page
		if ( 'yes' === get_option( 'alg_wc_eu_vat_add_order_edit_metabox', 'no' ) ) {
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
			// "Validate VAT and remove taxes" button
			add_action( 'admin_init', array( $this, 'validate_vat_and_maybe_remove_taxes' ), PHP_INT_MAX );
		}

		// Reports
		add_filter( 'woocommerce_admin_reports', array( $this, 'add_eu_vat_reports' ), PHP_INT_MAX );

		// Admin orders list
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
			add_filter( 'woocommerce_shop_order_search_fields',  array( $this, 'shop_order_meta_search_fields'), 10, 1 );

			add_filter( 'pre_get_posts', array( $this, 'euvat_filter_orders' ), 100 );

			// HPOS filter query
			add_filter( 'woocommerce_order_list_table_prepare_items_query_args', array( $this, 'euvat_filter_orders_hpos' ), 100 );

		}

		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'order_phone_backend' ), 10, 1 );

		// Add a popup metabox
		add_action( 'add_meta_boxes', array( $this, 'popup_order_meta_boxes' ) );

		// Get VAT details
		add_action( 'admin_init', array( $this, 'get_vat_details' ), PHP_INT_MAX );
		add_action( 'admin_notices', array( $this, 'admin_notice' ), PHP_INT_MAX );

		// VAT validation for orders created manually from the admin side
		if (
			'yes' === get_option( 'alg_wc_eu_vat_validate_vat_admin_side', 'no' ) &&
			is_admin()
		) {
			add_filter( 'woocommerce_order_is_vat_exempt', array( $this, 'admin_order_is_vat_exempt' ), PHP_INT_MAX, 2 );
		}

		// Admin VAT validation
		add_action( 'admin_print_scripts', array( $this, 'admin_inline_js' ), PHP_INT_MAX );

		// Exempt VAT from admin
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-admin-exempt.php';

		// Admin Users List
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-admin-users-list.php';

		// Advertise
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-advertise.php';

	}

	/**
	 * admin_inline_js.
	 *
	 * @version 4.1.0
	 * @since   1.4.1
	 *
	 * @todo    (dev) `response`?
	 */
	function admin_inline_js() {
		?>
		<script type="text/javascript">
			jQuery( function ( $ ) {
				var admin_input_timer;
				var input_timer_company;
				var done_input_interval = 1000;
				var admin_vat_input = $( 'input[name="_billing_eu_vat_number"]' );

				$( '#_billing_country' ).on( 'change', alg_wc_eu_vat_validate_vat_admin );

				// On input, start the countdown
				admin_vat_input.on( 'input', function () {
					clearTimeout( admin_input_timer );
					admin_input_timer = setTimeout( alg_wc_eu_vat_validate_vat_admin, done_input_interval );
				} );

				$( '#_billing_company' ).on( 'input', function () {
					clearTimeout( input_timer_company );
					input_timer_company = setTimeout( alg_wc_eu_vat_validate_vat_admin, done_input_interval );
				} );

				/**
				 * alg_wc_eu_vat_validate_vat_admin
				 *
				 * @version 1.6.0
				 * @since   1.0.0
				 */
				function alg_wc_eu_vat_validate_vat_admin() {
					$( "#woocommerce-order-data" ).block( { message: null } );
					var admin_vat_number_to_check = admin_vat_input.val();
					// Validating EU VAT Number through AJAX call
					var data = {
						'action': 'alg_wc_eu_vat_validate_action',
						'alg_wc_eu_vat_to_check': admin_vat_number_to_check,
						'billing_country': $( '#_billing_country' ).val(),
						'billing_company': $( '#_billing_company' ).val(),
					};
					$.ajax( {
						type: "POST",
						url: '<?php echo esc_url( admin_url( "admin-ajax.php" ) ); ?>',
						data: data,
						success: function ( response ) {
							$( "#woocommerce-order-data" ).unblock();
						},
						error: function ( XMLHttpRequest, textStatus, errorThrown ) {
							$( "#woocommerce-order-data" ).unblock();
						},
					} );
				};
			} );
		</script>
		<?php
	}

	/**
	 * admin_order_is_vat_exempt.
	 *
	 * @version 4.1.0
	 * @since   1.4.1
	 */
	function admin_order_is_vat_exempt( $is_exempt, $order ) {
		global $pagenow;
		if (
			'admin-ajax.php' === $pagenow &&
			isset( $_REQUEST['action'] ) &&
			'woocommerce_calc_line_taxes' === $_REQUEST['action']
		) {
			if ( alg_wc_eu_vat()->core->check_current_user_roles( get_option( 'alg_wc_eu_vat_exempt_for_user_roles', array() ) ) ) {

				$is_exempt = true;

			} elseif ( alg_wc_eu_vat()->core->check_current_user_roles( get_option( 'alg_wc_eu_vat_not_exempt_for_user_roles', array() ) ) ) {

				$is_exempt = false;

			} elseif ( alg_wc_eu_vat()->core->is_validate_and_exempt() && alg_wc_eu_vat()->core->is_valid_and_exists() ) {

				$is_exempt = apply_filters( 'alg_wc_eu_vat_maybe_exclude_vat', true );

			} else {

				$is_exempt = false;

			}
		}
		return $is_exempt;
	}

	/**
	 * popup_order_meta_boxes.
	 *
	 * @version 4.0.0
	 */
	function popup_order_meta_boxes() {
		 add_meta_box(
			'woocommerce_eu_vat_shop_order_popup',
			__( 'Check VAT Number', 'eu-vat-for-woocommerce' ),
			array( $this, 'popup_order_meta_box_content'),
			'shop_order',
			'side',
			'low'
		);
	}

	/**
	 * popup_order_meta_box_content.
	 *
	 * @version 4.1.0
	 */
	function popup_order_meta_box_content( $post ) {
		add_thickbox();
		?>
		<a href="https://ec.europa.eu/taxation_customs/vies?TB_iframe=true&width=772&height=485" class="thickbox button">Open VIES</a>
		<?php
	}

	/**
	 * order_phone_backend.
	 *
	 * @version 4.1.0
	 * @since   2.9.13
	 */
	function order_phone_backend( $order ) {
		$field_id = alg_wc_eu_vat_get_field_id();
		$value    = $order->get_meta( '_' . $field_id . '_customer_decide' );
		if ( 1 == $value ) {
			echo "<br><p><strong>" . esc_html__( 'Let Customer Decide:', 'eu-vat-for-woocommerce' ) . "</strong> Yes</p><br>";
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
	 * get_filter_shop_order_meta.
	 *
	 * @version 4.0.0
	 * @since   1.5.0
	 */
	function get_filter_shop_order_meta( $domain = 'woocommerce' ){
		// Add below the metakey / label pairs to filter orders
		return [
			'_billing_eu_vat_number' => __( 'Orders with EU VAT numbers', 'eu-vat-for-woocommerce' )
		];
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
	function display_admin_shop_order_by_meta_filter(){
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
	function shop_order_meta_search_fields( $meta_keys ){
		foreach ( $this->get_filter_shop_order_meta() as $meta_key => $label ) {
			$meta_keys[] = $meta_key;
		}
		return $meta_keys;
	}

	/**
	 * euvat_filter_orders_hpos.
	 *
	 * @version 2.11.7
	 * @since   2.11.7
	 */
	function euvat_filter_orders_hpos( $query_args ) {

		$filter_id = 'filter_shop_order_by_meta';

		if( isset( $_GET[ $filter_id ] ) && $_GET[ $filter_id ] ) {
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
	 * add_eu_vat_reports.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function add_eu_vat_reports( $reports ) {
		if ( ! isset( $reports['taxes'] ) ) {
			$reports['taxes'] = array(
				'title'   => __( 'Taxes', 'woocommerce' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
				'reports' => array(),
			);
		}
		$reports['taxes']['reports']['alg_wc_eu_vat'] = array(
			'title'       => __( 'EU VAT', 'eu-vat-for-woocommerce' ),
			'description' => '',
			'hide_title'  => true,
			'callback'    => array( $this, 'output_eu_vat_report' ),
		);
		return $reports;
	}

	/**
	 * output_eu_vat_report.
	 *
	 * @version 4.1.0
	 * @since   1.5.0
	 */
	function output_eu_vat_report() {
		require_once( 'class-wc-report-alg-wc-eu-vat.php' );
		$report = new WC_Report_Alg_WC_EU_VAT();
		$report->output_report();
		echo '<p><em>' .
				esc_html__( 'Report includes all EU VAT countries with existing sales.', 'eu-vat-for-woocommerce' ) . ' ' .
				esc_html__( 'Table is sorted by total tax value.', 'eu-vat-for-woocommerce' ) .
			'</em></p>';
	}

	/**
	 * add_to_admin_order_display.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) make full row?
	 */
	function add_to_admin_order_display( $fields ) {

		$fields[ alg_wc_eu_vat_get_field_id( true ) ] = array(
			'type'  => 'text',
			'label' => do_shortcode(
				get_option(
					'alg_wc_eu_vat_field_label',
					__( 'EU VAT Number', 'eu-vat-for-woocommerce' )
				)
			),
			'show'  => true,
		);

		return $fields;
	}

	/**
	 * add_to_ajax_get_customer_details.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_to_ajax_get_customer_details( $data, $customer, $user_id ) {
		$data['billing'][ alg_wc_eu_vat_get_field_id( true ) ] = get_user_meta( $user_id, alg_wc_eu_vat_get_field_id(), true );
		$data['billing'][ alg_wc_eu_vat_get_field_id( true ) . '_customer_decide' ] = get_user_meta( $user_id, alg_wc_eu_vat_get_field_id() . '_customer_decide', true );
		return $data;
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
	 * @version 4.0.0
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

					if(!empty($preserve_countries)){
						if(in_array($eu_vat_number['country'],$preserve_countries)){
							$preserve_countries_condition = true;
						}
					}

					if ( !$preserve_countries_condition && alg_wc_eu_vat_validate_vat( $eu_vat_number['country'], $eu_vat_number['number'], $billing_company ) ) {
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
}

endif;

return new Alg_WC_EU_VAT_Admin();
