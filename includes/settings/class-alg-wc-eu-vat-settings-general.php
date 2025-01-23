<?php
/**
 * EU VAT for WooCommerce - General Section Settings
 *
 * @version 4.1.0
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

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
		$all_roles  = array_merge(
			$guest_role,
			apply_filters( 'editable_roles', ( isset( $wp_roles ) && is_object( $wp_roles ) ? $wp_roles->roles : array() ) )
		);
		return wp_list_pluck( $all_roles, 'name' );
	}

	/**
	 * get_settings.
	 *
	 * @version 4.1.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) Belgium Compatibility: rename?
	 * @todo    (dev) check if `clear` is still working (and if yes - change desc)
	 * @todo    (dev) add link to plugin site `'<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">' . __( 'Visit plugin site', 'eu-vat-for-woocommerce' ) . '</a>'`?
	 * @todo    (dev) set `alg_wc_eu_vat_display_position` default to `in_billing_address` (instead of `after_order_table`)?
	 * @todo    (dev) change "CSS class" to "Alignment"?
	 * @todo    (feature) option to change default meta key?
	 */
	function get_settings() {

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
			),
			array(
				'title'    => __( 'Placeholder', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Placeholder visible to the customer.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_placeholder',
				'default'  => __( 'EU VAT Number', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Description', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Description visible to the customer.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_description',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'VAT field will be', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Sets if EU VAT field is required on checkout.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_required',
				'default'  => 'no',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'no'                => __( 'Optional', 'eu-vat-for-woocommerce' ),
					'yes'               => __( 'Required', 'eu-vat-for-woocommerce' ),
					'yes_for_countries' => __( 'Required in the following countries', 'eu-vat-for-woocommerce' ),
					'no_for_countries'  => __( 'Required in all countries except following countries', 'eu-vat-for-woocommerce' ),
					'yes_for_company'   => __( 'Required if customer fills the company field', 'eu-vat-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Required / Optional in countries', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Only works for ( Required in the following countries / Required in all countries except following countries ) for above settings.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_required_countries',
				'default'  => '',
				'type'     => 'multiselect',
				'class'    => 'wc-enhanced-select',
				'options'  => $this->get_all_countries()
			),
			array(
				'title'    => __( 'Remove the VAT field if the tax status is "none"', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Remove VAT from the checkout if any product in the cart has a tax status of "none".', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_hide_tax_status_none',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Confirmation notice', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Will add an additional confirmation notice on the checkout on <strong>empty VAT ID</strong>. <br> For example you can enable this if EU VAT field is not required, but you still want to display a confirmation notice to the customer when no VAT ID was entered.', 'eu-vat-for-woocommerce' ),
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
				'title'    => __( 'Add EU VAT field to sign-up form', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Add EU VAT field to sign-up form.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_signup_form',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'             => __( 'Allow checkout on unregistered VAT numbers', 'eu-vat-for-woocommerce' ),
				'desc_tip'          => __( 'Allow checkout on unregistered VAT numbers.', 'eu-vat-for-woocommerce' ),
				'desc'              => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'                => 'alg_wc_eu_vat_field_allow_unregistered_vat_no',
				'default'           => 'no',
				'type'              => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_eu_vat_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_frontend_options',
			),

			array(
				'title'    => __( 'Payment Methods Control', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_pay_control_title',
			),
			array(
				'title'             => __( 'Show specific payment method ONLY if VAT is valid', 'eu-vat-for-woocommerce' ),
				'desc_tip'          => (
					__( 'Choose selected payment method from following field.', 'eu-vat-for-woocommerce' ) .
					apply_filters(
						'alg_wc_eu_vat_settings',
						'<br>' . sprintf(
							/* Translators: %s: Plugin link. */
							__( 'You will need %s plugin to enable this option.', 'eu-vat-for-woocommerce' ),
							'<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">' .
								__( 'EU/UK VAT Validation Manager for WooCommerce Pro', 'eu-vat-for-woocommerce' ) .
							'</a>'
						)
					)
				),
				'desc'              => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'                => 'alg_wc_eu_vat_allow_specific_payment',
				'default'           => 'no',
				'type'              => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_eu_vat_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'             => __( 'Method to show for valid VAT only', 'eu-vat-for-woocommerce' ),
				'desc_tip'          => __( 'Allow specific payment methods if VAT is valid.', 'eu-vat-for-woocommerce' ),
				'id'                => 'alg_wc_eu_vat_allowed_payment_gateway',
				'default'           => '',
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select',
				'options'           => $this->get_all_payment_gateways(),
				'custom_attributes' => apply_filters( 'alg_wc_eu_vat_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_pay_control_title',
			),

			array(
				'title'    => __( 'Belgium Compatibility', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_belgium_compatibility_title',
			),
			array(
				'title'    => __( 'Let customer decide', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'This option will allow customer to select if they are individual or business, <br> which makes the option mandatory or optional based on selection, this option might be needed in Belgium.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_let_customer_decide',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Let customer decide field label', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Label visible to the customer.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_field_let_customer_decide_label',
				'default'  => __( 'I don\'t have a VAT ID', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Valid VAT but still paying ?', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Valid VAT but still paying, Belgium Compatibility.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_belgium_compatibility',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Valid VAT but still paying field label', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Label visible to the customer.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_belgium_compatibility_label',
				'default'  => __( 'I have a valid VAT but not exempted', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_belgium_compatibility_title',
			),

			array(
				'title'    => __( 'Field Option', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_frontend_two_options',
			),
			array(
				'title'    => __( 'Priority (i.e., position)', 'eu-vat-for-woocommerce' ),
				'desc_tip' => (
					__( 'Sets EU VAT field\'s position in the billing section of the checkout page.', 'eu-vat-for-woocommerce' ) . ' ' .
					sprintf(
						/* Translators: %s: Priority list. */
						__( 'Here are the default fields priorities: %s.', 'eu-vat-for-woocommerce' ),
						'<br>' . implode( '<br>', array(
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
						) )
					)
				),
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
				'title'             => __( 'Show field for selected countries only', 'eu-vat-for-woocommerce' ),
				'desc_tip'          => __( 'Ignored if empty (i.e., field is shown for all countries).', 'eu-vat-for-woocommerce' ),
				'desc'              => (
					sprintf(
						/* Translators: %s: Country list example. */
						__( 'Enter country codes as comma separated list, e.g., %s.', 'eu-vat-for-woocommerce' ),
						sprintf(
							/* Translators: %s: Country list. */
							__( 'to show field for EU VAT countries only enter: %s', 'eu-vat-for-woocommerce' ),
							'<code>' .
								(
									version_compare( $this->eu_get_woo_version_number(), '4.0.0', '<=' ) ?
									implode( ',', WC()->countries->get_european_union_countries() ) :
									implode( ',', WC()->countries->get_european_union_countries( 'eu_vat' ) )
								) .
							'</code>'
						)
					) .
					apply_filters(
						'alg_wc_eu_vat_settings',
						'<br>' . sprintf(
							'You will need %s plugin to enable this option.',
							'<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">EU/UK VAT Validation Manager for WooCommerce Pro</a>'
						)
					)
				),
				'id'                => 'alg_wc_eu_vat_show_in_countries',
				'default'           => '',
				'type'              => 'text',
				'css'               => 'width:100%;',
				'custom_attributes' => apply_filters( 'alg_wc_eu_vat_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'             => __( 'Show field for selected user roles only', 'eu-vat-for-woocommerce' ),
				'desc_tip'          => __( 'Ignored if empty (i.e., field is shown for all user roles).', 'eu-vat-for-woocommerce' ),
				'desc'              => apply_filters(
					'alg_wc_eu_vat_settings',
					sprintf(
						'You will need %s plugin to enable this option.',
						'<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">EU/UK VAT Validation Manager for WooCommerce Pro</a>'
					)
				),
				'id'                => 'alg_wc_eu_vat_show_for_user_roles',
				'default'           => array(),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select',
				'options'           => $this->get_all_user_roles(),
				'custom_attributes' => apply_filters( 'alg_wc_eu_vat_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_frontend_two_options',
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
				'title'    => __( 'Hide EU VAT field from checkout', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'This option might be needed when you\'re customizing your checkout page using a page builder plugin and want to fully-control field visibility', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_hide_eu_vat',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Show VAT details in checkout', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Enable', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Retrieves and shows business name, address, etc. in checkout', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_show_vat_details',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_display_options',
			),
		);

		return array_merge( $frontend_settings, $display_settings );
	}

	/**
	 * eu_get_woo_version_number.
	 *
	 * @version 4.1.0
	 */
	function eu_get_woo_version_number() {

		// If `get_plugins()` isn't available, require it
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Create the plugins folder and file variables
		$plugin_folder = get_plugins( '/' . 'woocommerce' );
		$plugin_file   = 'woocommerce.php';

		// If the plugin version number is set, return it; otherwise return null
		return ( $plugin_folder[ $plugin_file ]['Version'] ?? NULL );

	}

	/**
	 * get_all_payment_gateways.
	 *
	 * @version 4.1.0
	 *
	 * @todo    (dev) `alg_wc_pgpp_pay_titles`: rename?
	 */
	function get_all_payment_gateways() {
		$available_gateways = WC()->payment_gateways->payment_gateways();
		$gateways_settings  = array();
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			$gateways_settings[ $gateway_id ] = (
				! empty( $gateway->method_title ) ?
				$gateway->method_title . ' - ' . $gateway->title :
				$gateway->title
			);
		}
		update_option( 'alg_wc_pgpp_pay_titles', $gateways_settings );
		return $gateways_settings;
	}

	/**
	 * get_all_countries.
	 *
	 * @version 4.1.0
	 */
	function get_all_countries() {
		$countries_obj    = new WC_Countries();
		return $countries = $countries_obj->__get('countries');
	}

}

endif;

return new Alg_WC_EU_VAT_Settings_General();
