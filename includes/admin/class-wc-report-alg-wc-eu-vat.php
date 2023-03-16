<?php
/**
 * Taxes by EU VAT country report.
 *
 * @version 1.5.0
 * @since   1.5.0
 *
 * @package    WooCommerce/Admin/Reports
 * @wc_version 3.7.0
 * @see        `/woocommerce/includes/admin/reports/class-wc-report-taxes-by-code.php`
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WC_Report_Alg_WC_EU_VAT
 *
 * @package     WooCommerce/Admin/Reports
 * @wc_version  2.1.0
 */
class WC_Report_Alg_WC_EU_VAT extends WC_Admin_Report {

	/**
	 * Get the legend for the main chart sidebar.
	 *
	 * @return array
	 */
	public function get_chart_legend() {
		return array();
	}

	/**
	 * Output an export link.
	 */
	public function get_export_button() {

		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : 'last_month';
		?>
		<a
			href="#"
			download="report-<?php echo esc_attr( $current_range ); ?>-<?php echo esc_attr( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>.csv"
			class="export_csv"
			data-export="table"
		>
			<?php esc_html_e( 'Export CSV', 'woocommerce' ); ?>
		</a>
		<?php
	}

	/**
	 * Output the report.
	 */
	public function output_report() {

		$ranges = array(
			'year'       => __( 'Year', 'woocommerce' ),
			'last_month' => __( 'Last month', 'woocommerce' ),
			'month'      => __( 'This month', 'woocommerce' ),
		);

		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : 'last_month';

		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ) ) ) {
			$current_range = 'last_month';
		}

		$this->check_current_range_nonce( $current_range );
		$this->calculate_current_range( $current_range );

		$hide_sidebar = true;

		include WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php';
	}

	/**
	 * sort_by_tax.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	public function sort_by_tax( $a, $b ) {
		if ( $a['tax'] == $b['tax'] ) {
			return 0;
		}
		return ( $a['tax'] < $b['tax'] ) ? 1 : -1;
	}

	/**
	 * get_flag_img.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	public function get_flag_img( $country ) {
		$path   = '/assets/images/flag-icons/' . strtolower( $country ) . '.png';
		$return = ( file_exists( alg_wc_eu_vat()->plugin_path() . $path ) ?
			'<img title="' . alg_wc_eu_vat_get_country_name_by_code( $country ) . ' (' . strtoupper( $country ) . ')" src="' . alg_wc_eu_vat()->plugin_url() . $path . '"> ' : '' );
		return $return . alg_wc_eu_vat_get_country_name_by_code( $country ) . ' (' . strtoupper( $country ) . ')';
	}

	/**
	 * Get the main chart.
	 *
	 * @version 1.5.0
	 */
	public function get_main_chart() {
		$tax_rows = array();
		$orders   = wc_get_orders( array(
			'limit'      => -1,
			'type'       => 'shop_order',
			'status'     => array_map( 'wc_get_order_status_name', wc_get_is_paid_statuses() ),
			'date_query' => array(
				'after'  => date( 'Y-m-d H:i:s', $this->start_date ),
				'before' => date( 'Y-m-d H:i:s', $this->end_date ),
			),
			'meta_key'     => '_billing_country',
			'meta_value'   => WC()->countries->get_european_union_countries( 'eu_vat' ),
			'meta_compare' => 'IN',
		) );
		foreach ( $orders as $order ) {
			$country = $order->get_billing_country();
			$total   = $order->get_total();
			$tax     = $order->get_total_tax();
			if ( ! isset( $tax_rows[ $country ] ) ) {
				$tax_rows[ $country ] = array(
					'count'      => 0,
					'sum'        => 0,
					'tax'        => 0,
					'sum_no_tax' => 0,
				);
			}
			$tax_rows[ $country ]['count']++;
			$tax_rows[ $country ]['sum'] += $total;
			$tax_rows[ $country ]['tax'] += $tax;
			if ( 0 == $tax ) {
				$tax_rows[ $country ]['sum_no_tax'] += $total;
			}
		}
		uasort( $tax_rows, array( $this, 'sort_by_tax' ) );
		?>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php _e( 'Country', 'woocommerce' ); ?></th>
					<th class="total_row"><?php _e( 'Number of orders', 'woocommerce' ); ?></th>
					<th class="total_row"><?php _e( 'Total sales', 'woocommerce' ); ?></th>
					<th class="total_row"><?php _e( 'Total sales with zero tax', 'woocommerce' ); ?></th>
					<th class="total_row"><?php _e( 'Total tax', 'woocommerce' ); ?></th>
				</tr>
			</thead>
			<?php if ( ! empty( $tax_rows ) ) : ?>
				<tbody>
					<?php
					foreach ( $tax_rows as $country => $tax_row ) {
						?>
						<tr>
							<th scope="row"><?php echo $this->get_flag_img( $country ); ?></th>
							<td class="total_row"><?php echo $tax_row['count']; ?></td>
							<td class="total_row"><?php echo wc_price( $tax_row['sum'] ); ?></td>
							<td class="total_row"><?php echo wc_price( $tax_row['sum_no_tax'] ); ?></td>
							<td class="total_row"><?php echo wc_price( $tax_row['tax'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th scope="row"><?php _e( 'Totals', 'woocommerce' ); ?></th>
						<th class="total_row"><?php echo array_sum( wp_list_pluck( $tax_rows, 'count' ) ); ?></th>
						<th class="total_row"><?php echo wc_price( array_sum( wp_list_pluck( $tax_rows, 'sum' ) ) ); ?></th>
						<th class="total_row"><?php echo wc_price( array_sum( wp_list_pluck( $tax_rows, 'sum_no_tax' ) ) ); ?></th>
						<th class="total_row"><?php echo wc_price( array_sum( wp_list_pluck( $tax_rows, 'tax' ) ) ); ?></th>
					</tr>
				</tfoot>
			<?php else : ?>
				<tbody>
					<tr>
						<td><?php _e( 'No taxes found in this period', 'woocommerce' ); ?></td>
					</tr>
				</tbody>
			<?php endif; ?>
		</table>
		<?php
	}
}
