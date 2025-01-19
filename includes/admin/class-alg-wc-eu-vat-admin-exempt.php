<?php
/**
 * EU VAT for WooCommerce - Exempt VAT from Admin
 *
 * @version 4.0.0
 * @since   4.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Admin_Exempt' ) ) :

class Alg_WC_EU_VAT_Admin_Exempt {

	/**
	 * Constructor.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function __construct() {

		add_filter( 'woocommerce_order_is_vat_exempt', array( $this, 'exempt'), PHP_INT_MAX, 2 );

		add_action( 'woocommerce_order_item_add_action_buttons', array( $this, 'add_button'), PHP_INT_MAX );

		// JS
		add_action( 'admin_footer', array( $this, 'add_js'), PHP_INT_MAX );

		// AJAX
		add_action( 'wp_ajax_'        . 'exempt_vat_from_admin', array( $this, 'ajax' ) );
		add_action( 'wp_ajax_nopriv_' . 'exempt_vat_from_admin', array( $this, 'ajax' ) );

	}

	/**
	 * exempt.
	 *
	 * @version 4.0.0
	 * @since   2.9.13
	 */
	function exempt( $is_exempt, $order ) {
		return (
			'yes' === $order->get_meta( 'exempt_vat_from_admin' ) ?
			true :
			$is_exempt
		);
	}

	/**
	 * add_button.
	 *
	 * @version 4.0.0
	 * @since   2.9.13
	 */
	function add_button( $order ) {

		if ( 'yes' === $order->get_meta( 'exempt_vat_from_admin' ) ) {
			$exempt = 'yes';
			$title  = __( 'Impose VAT', 'eu-vat-for-woocommerce' );
		} else {
			$exempt = 'never';
			$title  = __( 'Exempt VAT', 'eu-vat-for-woocommerce' );
		}

		echo '<button' .
			' id="exempt_vat_from_admin"' .
			' type="button"' .
			' class="button exempt_vat_from_admin button-primary"' .
			' data-status="' . esc_attr( $exempt ) . '"' .
			' data-order_id="' . esc_attr( $order->get_id() ) . '"' .
		'>' .
			esc_html( $title ) .
		'</button>';

	}

	/**
	 * add_js.
	 *
	 * @version 4.0.0
	 * @since   1.7.0
	 */
	function add_js() {
		$nonce = wp_create_nonce( 'alg-wc-eu-vat-ajax-nonce' );
		?>
		<script type="text/javascript">
		jQuery( 'body' ).on( 'click', '.exempt_vat_from_admin', function () {
			jQuery( '#woocommerce-order-items' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			} );

			var order_id = jQuery( this ).data( 'order_id' );
			var status = jQuery( this ).data( 'status' );
			var data = {
				action: 'exempt_vat_from_admin',
				order_id: order_id,
				status: status,
				nonce: '<?php echo $nonce; ?>'
			};
			jQuery.ajax( {
				url:  woocommerce_admin_meta_boxes.ajax_url,
				data: data,
				type: 'POST',
				success: function ( response ) {
					jQuery( '#woocommerce-order-items' ).unblock();
					if ( 'yes' == response || 'never' == response ) {
						jQuery( '.calculate-action' ).click();
					}
				}
			} );
		} );
		</script>
		<?php
	}

	/**
	 * ajax.
	 *
	 * @version 4.0.0
	 * @since   2.12.13
	 *
	 * @todo    (dev) reload page?
	 */
	function ajax( $param ) {

		if (
			! current_user_can( 'manage_options' ) ||
			! wp_verify_nonce( $_POST['nonce'], 'alg-wc-eu-vat-ajax-nonce' )
		) {
			exit;
		}

		if ( ! empty( $_POST['order_id'] ) ) {
			$order_id = absint( $_POST['order_id'] );
			if ( ( $order = wc_get_order( $order_id ) ) ) {
				if ( isset( $_POST['status'] ) && 'yes' == $_POST['status'] ) {
					$order->update_meta_data( 'exempt_vat_from_admin', 'never' );
					$order->save();
					echo 'never';
					die;
				} elseif ( isset( $_POST['status'] ) && 'never' == $_POST['status'] ) {
					$order->update_meta_data( 'exempt_vat_from_admin', 'yes' );
					$order->save();
					echo 'yes';
					die;
				}
			}
		}
		echo 'never';
		die;

	}

}

endif;

return new Alg_WC_EU_VAT_Admin_Exempt();
