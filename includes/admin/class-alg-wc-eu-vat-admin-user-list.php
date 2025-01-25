<?php
/**
 * EU VAT for WooCommerce - Admin User List
 *
 * @version 4.2.0
 * @since   4.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Admin_User_List' ) ) :

class Alg_WC_EU_VAT_Admin_User_List {

	/**
	 * Constructor.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function __construct() {
		add_action( 'restrict_manage_users', array( $this, 'add_users_filter' ) );
		add_filter( 'pre_get_users', array( $this, 'filter_users' ) );
	}

	/**
	 * get_value.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function get_value() {
		return (
			(
				isset( $_GET['billing_eu_vat_number'] ) &&
				isset( $_GET['billing_eu_vat_number'][0] ) &&
				'yes' === sanitize_text_field( wp_unslash( $_GET['billing_eu_vat_number'][0] ) )
			) ?
			'yes' :
			'no'
		);
	}

	/**
	 * add_users_filter.
	 *
	 * @version 4.1.0
	 * @since   2.9.11
	 *
	 * @todo    (fix) rename "EU VAT not provided" to "All users"
	 */
	function add_users_filter() {
		?>
		<select name="billing_eu_vat_number[]" style="float:none;">
			<option value=""><?php echo esc_html__( 'EU VAT not provided', 'eu-vat-for-woocommerce' ); ?></option>
			<option value="yes"<?php selected( $this->get_value(), 'yes' ); ?>><?php echo esc_html__( 'EU VAT provided', 'eu-vat-for-woocommerce' ); ?></option>
		</select><input type="submit" class="button" value="<?php echo esc_attr__( 'Filter', 'eu-vat-for-woocommerce' ); ?>">
		<?php
	}

	/**
	 * filter_users.
	 *
	 * @version 4.0.0
	 * @since   2.9.11
	 */
	function filter_users( $query ) {
		global $pagenow;
		if ( is_admin() && 'users.php' === $pagenow ) {
			if ( 'no' !== $this->get_value() ) {
				$meta_query = array(
					array(
						'key'     => 'billing_eu_vat_number',
						'value'   => '',
						'compare' => '!=',
					)
				);
				$query->set( 'meta_key', 'billing_eu_vat_number' );
				$query->set( 'meta_query', $meta_query );
			}
		}
	}

}

endif;

return new Alg_WC_EU_VAT_Admin_User_List();
