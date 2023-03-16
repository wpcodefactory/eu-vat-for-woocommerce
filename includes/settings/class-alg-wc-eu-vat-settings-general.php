<?php
/**
 * EU VAT for WooCommerce - General Section Settings
 *
 * @version 1.7.2
 * @since   1.0.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_EU_VAT_Settings_General' ) ) :

class Alg_WC_EU_VAT_Settings_General extends Alg_WC_EU_VAT_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'General', 'eu-vat-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_all_user_roles.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function get_all_user_roles() {
		global $wp_roles;
		$guest_role = array( 'guest' => array( 'name' => __( 'Guest', 'eu-vat-for-woocommerce' ), 'capabilities' => array() ) );
		$all_roles  = array_merge( $guest_role, apply_filters( 'editable_roles', ( isset( $wp_roles ) && is_object( $wp_roles ) ? $wp_roles->roles : array() ) ) );
		return wp_list_pluck( $all_roles, 'name' );
	}

	/**
	 * get_settings.
	 *
	 * @version 1.7.2
	 * @since   1.0.0
	 * @todo    [dev] check if `clear` is still working (and if yes - change desc)
	 * @todo    [dev] (maybe) add link to plugin site `'<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">' . __( 'Visit plugin site', 'eu-vat-for-woocommerce' ) . '</a>'`
	 * @todo    [dev] (maybe) set `alg_wc_eu_vat_display_position` default to `in_billing_address` (instead of `after_order_table`)
	 * @todo    [dev] (maybe) change "CSS class" to "Alignment"
	 * @todo    [feature] (maybe) option to change default meta key
	 */
	function get_settings() {

		$plugin_settings = array(
			array(
				'title'    => __( 'EU VAT Options', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_plugin_options',
			),
			array(
				'title'    => __( 'EU VAT for WooCommerce', 'eu-vat-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable plugin', 'eu-vat-for-woocommerce' ) . '</strong>',
				'desc_tip' => __( 'Manage EU VAT in WooCommerce. Beautifully.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_plugin_enabled',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_plugin_options',
			),
		);

		$frontend_settings = array(
			array(
				'title'    => __( 'Frontend Options', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_frontend_options',
			),
			array(
				'title'    => __( 'Field label', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Label visible to the customer.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_label',
				'default'  => __( 'EU VAT Number', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Placeholder', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Placeholder visible to the customer.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_placeholder',
				'default'  => __( 'EU VAT Number', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Description', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Description visible to the customer.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_description',
				'default'  => '',
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Required', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Sets if EU VAT field is required on checkout.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_required',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Confirmation notice', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Will add an additional confirmation notice on the checkout on <strong>empty VAT ID</strong>. For example you can enable this if EU VAT field is not required, but you still want to display a confirmation notice to the customer when no VAT ID was entered.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_confirmation',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Confirmation notice text', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_confirmation_text',
				'default'  => __( 'You didn\'t set your VAT ID. Are you sure you want to continue?', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Priority (i.e. position)', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Sets EU VAT field\'s position in the billing section of the checkout page.', 'eu-vat-for-woocommerce' ) . ' ' .
					sprintf( __( 'Here are the default fields priorities: %s.', 'eu-vat-for-woocommerce' ), '<br>' . implode( '<br>', array(
						__( 'First name', 'eu-vat-for-woocommerce' )  . ' - 10',
						__( 'Last name', 'eu-vat-for-woocommerce' )   . ' - 20',
						__( 'Company', 'eu-vat-for-woocommerce' )     . ' - 30',
						__( 'Country', 'eu-vat-for-woocommerce' )     . ' - 40',
						__( 'Address 1', 'eu-vat-for-woocommerce' )   . ' - 50',
						__( 'Address 2', 'eu-vat-for-woocommerce' )   . ' - 60',
						__( 'City', 'eu-vat-for-woocommerce' )        . ' - 70',
						__( 'State', 'eu-vat-for-woocommerce' )       . ' - 80',
						__( 'Postcode', 'eu-vat-for-woocommerce' )    . ' - 90',
						__( 'Phone', 'eu-vat-for-woocommerce' )       . ' - 100',
						__( 'Email', 'eu-vat-for-woocommerce' )       . ' - 110',
					) ) ),
				'id'       => 'alg_wc_eu_vat_field_priority',
				'default'  => 200,
				'type'     => 'number',
			),
			array(
				'title'    => __( 'Max length', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Specifies the maximum number of characters allowed in the field.', 'eu-vat-for-woocommerce' ) . ' ' .
					__( 'Ignored if set to zero.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_maxlength',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0 ),
			),
			array(
				'title'    => __( 'Clear', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'CSS clear option.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_clear',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'CSS class', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'CSS class option.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_class',
				'default'  => 'form-row-wide',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'form-row-wide'  => __( 'Wide', 'eu-vat-for-woocommerce' ),
					'form-row-first' => __( 'First', 'eu-vat-for-woocommerce' ),
					'form-row-last'  => __( 'Last', 'eu-vat-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Label CSS class', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Label CSS class option.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_label_class',
				'default'  => '',
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Always show zero VAT', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Will always show zero VAT amount in order review on checkout.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_always_show_zero_vat',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Show field for selected countries only', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Ignored if empty (i.e. field is shown for all countries).', 'eu-vat-for-woocommerce' ),
				'desc'     => sprintf( __( 'Enter country codes as comma separated list, e.g. %s.', 'eu-vat-for-woocommerce' ),
					sprintf( __( 'to show field for EU VAT countries only enter: %s', 'eu-vat-for-woocommerce' ),
						'<code>' . implode( ',', WC()->countries->get_european_union_countries( 'eu_vat' ) ) . '</code>' ) ) .
					apply_filters( 'alg_wc_eu_vat_settings', '<br>' . sprintf( 'You will need %s plugin to enable this option.',
						'<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">EU VAT for WooCommerce Pro</a>' ) ),
				'id'       => 'alg_wc_eu_vat_show_in_countries',
				'default'  => '',
				'type'     => 'text',
				'css'      => 'width:100%;',
				'custom_attributes' => apply_filters( 'alg_wc_eu_vat_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Show field for selected user roles only', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Ignored if empty (i.e. field is shown for all user roles).', 'eu-vat-for-woocommerce' ),
				'desc'     => apply_filters( 'alg_wc_eu_vat_settings', sprintf( 'You will need %s plugin to enable this option.',
					'<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">EU VAT for WooCommerce Pro</a>' ) ),
				'id'       => 'alg_wc_eu_vat_show_for_user_roles',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'wc-enhanced-select',
				'options'  => $this->get_all_user_roles(),
				'custom_attributes' => apply_filters( 'alg_wc_eu_vat_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_frontend_options',
			),
		);

		$display_settings = array(
			array(
				'title'    => __( 'Display Options', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_display_options',
			),
			array(
				'title'    => __( 'Display', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'If empty - will display after order table.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_display_position',
				'default'  => array( 'after_order_table' ),
				'type'     => 'multiselect',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'after_order_table'  => __( 'After order table', 'eu-vat-for-woocommerce' ),
					'in_billing_address' => __( 'In billing address', 'eu-vat-for-woocommerce' ),
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_display_options',
			),
		);

		return array_merge( $plugin_settings, $frontend_settings, $display_settings );
	}

}

endif;

return new Alg_WC_EU_VAT_Settings_General();
