<?php
/**
 * EU VAT for WooCommerce - Compatibility Class
 *
 * @version 4.0.0
 * @since   4.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Compatibility' ) ) :

class Alg_WC_EU_VAT_Compatibility {

	/**
	 * Constructor.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function __construct() {

		// "PDF Invoices & Packing Slips for WooCommerce" by "WP Overnight"
		add_filter( 'wpo_wcpdf_after_billing_address', array( $this, 'wpo_wcpdf_extend_after_billing_address' ), 10, 2  );
		add_action( 'wpo_wcpdf_after_order_details', array( $this, 'wpo_wcpdf_add_vat_exempt_text_pdf_footer'), 10, 2 );

		// YITH WooCommerce PDF Invoices & Packing Slips
		add_filter( 'yith_ywpi_template_editor_customer_info_placeholders', array( $this, 'yith_support_invoice' ), PHP_INT_MAX, 1 );

	}

	/**
	 * wpo_wcpdf_extend_after_billing_address.
	 *
	 * @version 4.0.0
	 * @since   1.7.0
	 *
	 * @see     https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/
	 */
	function wpo_wcpdf_extend_after_billing_address( $type, $pdf_order ) {
		if ( function_exists( 'alg_wc_eu_vat_get_field_id' ) ) {
			$vat_id = $pdf_order->get_meta( '_' . alg_wc_eu_vat_get_field_id() );
			if ( $vat_id && ! empty( $vat_id ) ) {
				?><div class="eu-vat"><?php echo $vat_id; ?></div><?php
			}
		}
	}

	/**
	 * wpo_wcpdf_add_vat_exempt_text_pdf_footer.
	 *
	 * @version 4.0.0
	 * @since   2.9.17
	 *
	 * @see     https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/
	 */
	function wpo_wcpdf_add_vat_exempt_text_pdf_footer( $document_type, $order ) {
		$is_vat_exempt            = $order->get_meta( 'is_vat_exempt' );
		$is_vat_exempt_from_admin = $order->get_meta( 'exempt_vat_from_admin' );
		if (
			'yes' === $is_vat_exempt ||
			'yes' === $is_vat_exempt_from_admin
		) {
			echo get_option(
				'alg_wc_eu_vat_advanced_vat_shifted_text',
				__( 'VAT SHIFTED', 'eu-vat-for-woocommerce' )
			);
		}
	}

	/**
	 * yith_support_invoice.
	 *
	 * @version 4.0.0
	 * @since   2.12.4
	 *
	 * @see     https://yithemes.com/themes/plugins/yith-woocommerce-pdf-invoice/
	 */
	function yith_support_invoice( $fields_billing ) {
		$fields_billing[] = 'billing_eu_vat_number';
		return $fields_billing;
	}

}

endif;

return new Alg_WC_EU_VAT_Compatibility();
