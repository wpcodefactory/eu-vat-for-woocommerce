<?php
/**
 * EU VAT for WooCommerce - Customer Meta Field
 *
 * @version 4.4.1
 * @since   4.4.1
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Customer_Meta_Field' ) ) :

class Alg_WC_EU_VAT_Customer_Meta_Field {

	/**
	 * Constructor.
	 *
	 * @version 4.4.1
	 * @since   4.4.1
	 *
	 * @todo    (v4.4.1) validate on `woocommerce_customer_save_address`?
	 */
	function __construct() {

		// Add field
		add_filter( 'woocommerce_customer_meta_fields', array( $this, 'add' ) );

		// Validate
		add_action( 'admin_init', array( $this, 'validate' ) );
		add_action( 'admin_notices', array( $this, 'validation_notice' ) );

	}

	/**
	 * add.
	 *
	 * @version 4.4.1
	 * @since   1.0.0
	 */
	function add( $fields ) {

		// Label
		$label = do_shortcode(
			get_option(
				'alg_wc_eu_vat_field_label',
				__( 'EU VAT Number', 'eu-vat-for-woocommerce' )
			)
		);

		// Description
		$description = '';
		if (
			current_user_can( 'manage_woocommerce' ) &&
			( $user_id = $this->get_user_id() ) &&
			( $customer = new WC_Customer( $user_id ) ) &&
			'' !== $customer->get_meta( alg_wc_eu_vat_get_field_id() )
		) {
			$description = sprintf(
				'<a href="%1$s">%2$s</a>%3$s',
				wp_nonce_url(
					add_query_arg( 'alg_wc_eu_vat_validate_user_profile', $user_id ),
					'alg_wc_eu_vat_validate_user_profile_nonce',
					'_alg_wc_eu_vat_validate_user_profile_nonce'
				),
				__( 'Validate VAT ID', 'eu-vat-for-woocommerce' ),
				$this->validation_message( 'icon', true )
			);
		}

		// Add field
		$fields['billing']['fields'][ alg_wc_eu_vat_get_field_id() ] = array(
			'label'       => $label,
			'description' => $description,
		);

		return $fields;
	}

	/**
	 * validate.
	 *
	 * @version 4.4.1
	 * @since   4.4.1
	 */
	function validate() {

		if ( ! isset( $_GET['alg_wc_eu_vat_validate_user_profile'] ) ) {
			return;
		}

		// Nonce
		if (
			! isset( $_GET['_alg_wc_eu_vat_validate_user_profile_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_GET['_alg_wc_eu_vat_validate_user_profile_nonce'] ) ),
				'alg_wc_eu_vat_validate_user_profile_nonce'
			)
		) {
			wp_die( esc_html__( 'Link expired.', 'eu-vat-for-woocommerce' ) );
		}

		// User capabilities
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Insufficient user capabilities.', 'eu-vat-for-woocommerce' ) );
		}

		// Customer
		if (
			! ( $user_id  = sanitize_text_field( wp_unslash( $_GET['alg_wc_eu_vat_validate_user_profile'] ) ) ) ||
			! ( $customer = new WC_Customer( $user_id ) )
		) {
			wp_die( esc_html__( 'User error.', 'eu-vat-for-woocommerce' ) );
		}

		// VAT ID
		if ( '' === ( $vat_id = $customer->get_meta( alg_wc_eu_vat_get_field_id() ) ) ) {
			wp_safe_redirect(
				remove_query_arg(
					array(
						'alg_wc_eu_vat_validate_user_profile',
						'_alg_wc_eu_vat_validate_user_profile_nonce',
					)
				)
			);
			exit;
		}

		// Parse
		$eu_vat_number = alg_wc_eu_vat_parse_vat( $vat_id, $customer->get_billing_country() );

		// Validate
		$result = (
			(
				'' !== $eu_vat_number['country'] &&
				'' !== $eu_vat_number['number'] &&
				alg_wc_eu_vat_validate_vat(
					$eu_vat_number['country'],
					$eu_vat_number['number'],
					$customer->get_billing_company()
				)
			) ?
			'success' :
			'error'
		);

		// Redirect
		wp_safe_redirect(
			add_query_arg(
				array(
					'alg_wc_eu_vat_validate_user_profile_result'        => $result,
					'alg_wc_eu_vat_validate_user_profile_result_vat_id' => $vat_id,
				),
				remove_query_arg(
					array(
						'alg_wc_eu_vat_validate_user_profile',
						'_alg_wc_eu_vat_validate_user_profile_nonce',
					)
				)
			)
		);
		exit;

	}

	/**
	 * validation_notice.
	 *
	 * @version 4.4.1
	 * @since   4.4.1
	 */
	function validation_notice() {
		$this->validation_message( 'notice', false );
	}

	/**
	 * validation_message.
	 *
	 * @version 4.4.1
	 * @since   4.4.1
	 */
	function validation_message( $type, $do_return ) {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! isset(
			$_GET['alg_wc_eu_vat_validate_user_profile_result'],
			$_GET['alg_wc_eu_vat_validate_user_profile_result_vat_id']
		) ) {
			return '';
		}

		$result = sanitize_text_field( wp_unslash( $_GET['alg_wc_eu_vat_validate_user_profile_result'] ) );
		$vat_id = sanitize_text_field( wp_unslash( $_GET['alg_wc_eu_vat_validate_user_profile_result_vat_id'] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$msg = sprintf(
			(
				'success' === $result ?
				/* Translators: %s: VAT ID. */
				__( '%s VAT ID is valid.', 'eu-vat-for-woocommerce' ) :
				/* Translators: %s: VAT ID. */
				__( '%s VAT ID is not valid.', 'eu-vat-for-woocommerce' )
			),
			(
				'notice' === $type ?
				"<strong>{$vat_id}</strong>" :
				$vat_id // 'icon'
			)
		);

		if ( $do_return ) {
			ob_start();
		}

		if ( 'notice' === $type ) {

			?>
			<div class="notice notice-<?php echo esc_attr( $result ); ?> is-dismissible">
				<p><?php echo wp_kses_post( $msg ); ?></p>
			</div>
			<?php

		} else { // 'icon'

			printf(
				' <span class="dashicons dashicons-%1$s" style="color:%2$s;" title="%3$s"></span>',
				( 'success' === $result ? 'yes'   : 'no' ),
				( 'success' === $result ? 'green' : 'red' ),
				esc_attr( $msg )
			);

		}

		if ( $do_return ) {
			return ob_get_clean();
		}

	}

	/**
	 * get_user_id.
	 *
	 * @version 4.4.1
	 * @since   4.4.1
	 */
	function get_user_id() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['user_id'] ) ) {
			return absint( $_REQUEST['user_id'] );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( ( $user = wp_get_current_user() ) ) {
			return $user->ID;
		}

		return false;

	}

}

endif;

return new Alg_WC_EU_VAT_Customer_Meta_Field();
