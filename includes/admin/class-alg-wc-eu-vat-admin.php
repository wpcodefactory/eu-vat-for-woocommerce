<?php
/**
 * EU VAT for WooCommerce - Admin Class
 *
 * @version 4.3.0
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Admin' ) ) :

class Alg_WC_EU_VAT_Admin {

	/**
	 * meta_boxes.
	 *
	 * @version 4.3.0
	 * @since   4.3.0
	 */
	public $meta_boxes;

	/**
	 * Constructor.
	 *
	 * @version 4.3.0
	 * @since   1.0.0
	 */
	function __construct() {

		// Admin order edit
		add_filter( 'woocommerce_admin_billing_fields', array( $this, 'add_to_admin_order_display' ), PHP_INT_MAX );

		// Admin order edit - "Load billing address" button
		add_filter( 'woocommerce_ajax_get_customer_details', array( $this, 'add_to_ajax_get_customer_details' ), PHP_INT_MAX, 3 );

		// Customer decide
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'order_customer_decide' ) );

		// Admin VAT validation
		add_action( 'admin_print_scripts', array( $this, 'admin_inline_js' ), PHP_INT_MAX );

		// VAT validation for orders created manually from the admin side
		if (
			'yes' === get_option( 'alg_wc_eu_vat_validate_vat_admin_side', 'no' ) &&
			is_admin()
		) {
			add_filter(
				'woocommerce_order_is_vat_exempt',
				array( $this, 'admin_order_is_vat_exempt' ),
				PHP_INT_MAX,
				2
			);
		}

		// Reports
		add_filter( 'woocommerce_admin_reports', array( $this, 'add_eu_vat_reports' ), PHP_INT_MAX );

		// Admin new order email
		if ( 'yes' === get_option( 'alg_wc_eu_vat_admin_new_order_email', 'no' ) ) {
			add_action(
				'woocommerce_email_customer_details',
				array( $this, 'add_eu_vat_data_to_admin_new_order_email' ),
				100,
				4
			);
		}

		// Meta boxes
		$this->meta_boxes = require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-meta-boxes.php';

		// Admin order list
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-admin-order-list.php';

		// Admin user list
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-admin-user-list.php';

		// Exempt VAT from admin
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-admin-exempt.php';

		// Advertise
		require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-advertise.php';

	}

	/**
	 * add_eu_vat_data_to_admin_new_order_email.
	 *
	 * @version 4.3.0
	 * @since   4.3.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/9.6.2/plugins/woocommerce/templates/emails/admin-new-order.php#L46
	 * @see     https://github.com/woocommerce/woocommerce/blob/9.6.2/plugins/woocommerce/templates/emails/email-customer-details.php
	 * @see     https://github.com/woocommerce/woocommerce/blob/9.6.2/plugins/woocommerce/templates/emails/plain/email-customer-details.php
	 *
	 * @todo    (v4.3.0) do not add the summary if customer EU VAT number is empty?
	 */
	function add_eu_vat_data_to_admin_new_order_email( $order, $sent_to_admin, $plain_text, $email ) {

		if (
			! $sent_to_admin ||
			'new_order' !== $email->id
		) {
			return;
		}

		$table_data = $this->meta_boxes->output_meta_box_data( $order, true );

		if ( $plain_text ) {

			echo "\n" . esc_html( wc_strtoupper( esc_html__( 'EU VAT', 'eu-vat-for-woocommerce' ) ) ) . "\n\n";
			foreach ( $table_data as $row ) {
				echo wp_kses_post( $row[0] ?? '' ) . ': ' . wp_kses_post( $row[1] ?? '' ) . "\n";
			}

		} else {

			?>
			<div style="font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;">
				<h2><?php esc_html_e( 'EU VAT', 'eu-vat-for-woocommerce' ); ?></h2>
				<?php
				echo alg_wc_eu_vat_get_table_html(
					$table_data,
					array(
						'table_heading_type' => 'vertical',
						'table_style'        => 'border-collapse: collapse;',
						'row_styles'         => 'border: 1px solid #e5e5e5;',
					)
				);
				?>
			</div>
			<?php

		}

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
	 * order_customer_decide.
	 *
	 * @version 4.2.0
	 * @since   2.9.13
	 */
	function order_customer_decide( $order ) {
		if ( 1 != $order->get_meta( '_' . alg_wc_eu_vat_get_field_id() . '_customer_decide' ) ) {
			return;
		}
		?><br><p><strong><?php esc_html_e( 'Let Customer Decide:', 'eu-vat-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Yes', 'eu-vat-for-woocommerce' ); ?></p><br><?php
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
	 * @version 4.2.0
	 * @since   1.5.0
	 */
	function output_eu_vat_report() {
		require_once plugin_dir_path( __FILE__ ) . 'class-wc-report-alg-wc-eu-vat.php';
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
	 * @version 4.2.0
	 * @since   1.0.0
	 */
	function add_to_ajax_get_customer_details( $data, $customer, $user_id ) {
		$id  = alg_wc_eu_vat_get_field_id( true );
		$key = alg_wc_eu_vat_get_field_id();
		$data['billing'][ $id ]                      = get_user_meta( $user_id, $key, true );
		$data['billing'][ $id . '_customer_decide' ] = get_user_meta( $user_id, $key . '_customer_decide', true );
		return $data;
	}

}

endif;

return new Alg_WC_EU_VAT_Admin();
