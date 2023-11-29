<?php
/**
 * EU VAT for WooCommerce - Validation Section Settings
 *
 * @version 2.9.14
 * @since   1.5.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_EU_VAT_Settings_Validation' ) ) :

class Alg_WC_EU_VAT_Settings_Validation extends Alg_WC_EU_VAT_Settings_Section {

	public $id = '';
	public $desc = '';
	/**
	 * Constructor.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function __construct() {
		$this->id   = 'validation';
		$this->desc = __( 'Validation & Progress', 'eu-vat-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 2.9.16
	 * @since   1.5.0
	 * @todo    [dev] (maybe) set default value for "alg_wc_eu_vat_add_progress_text" to "yes"
	 * @todo    [feature] (important) Message if customer is in base country and VAT is NOT exempted
	 * @todo    [feature] (important) Message if customer's check for IP location country has failed
	 * @todo    [feature] add "Check company address" option (similar to "Check company name")
	 * @todo    [feature] ? "Require Country Code in VAT Number"
	 */
	function get_settings() {

		$validation_settings = array(
			array(
				'title'    => __( 'Validation Options', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_validation_options',
			),
			array(
				'title'    => __( 'Validate', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enabled/disables EU VAT validation.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_validate',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Validate at signup form.', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enabled/disables EU VAT validation at sign up page.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_validate_sign_up_page',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Message on not valid.', 'eu-vat-for-woocommerce' ) . ' ' .
					sprintf( __( 'Replaced value: %s', 'eu-vat-for-woocommerce' ), '<code>%eu_vat_number%</code>' ),
				'desc_tip' => __( 'Message will be displayed, when customer tries to checkout with invalid VAT number ("Validate" option must be enabled).', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_not_valid_message',
				'default'  => __( '<strong>EU VAT Number</strong> is not valid.', 'eu-vat-for-woocommerce' ),
				'type'     => 'textarea',
				// 'css'      => 'width:100%;',
				'alg_wc_eu_vat_raw' => true,
			),
			
			array(
				'title'    => __( 'First validation method', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Change this if you are having issues when validating VAT. This only selects first method to try - if not succeeded, remaining methods will be used for validation.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_first_method',
				'default'  => 'soap',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'soap'              => __( 'SOAP', 'eu-vat-for-woocommerce' ),
					'curl'              => __( 'cURL', 'eu-vat-for-woocommerce' ),
					'file_get_contents' => __( 'Simple', 'eu-vat-for-woocommerce' ),
				),
			),
			
			array(
				'title'    => __( 'Exempt VAT for valid numbers', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enables/disabled VAT exemption.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_disable_for_valid',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			
			array(
				'title'    => __( 'Preserve VAT if shipping country is different from billing country', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enables for preserve VAT if shipping country is different from billing country.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_preserv_vat_for_different_shipping',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			
			array(
				'title'    => __( 'Preserve VAT in selected countries', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'This will validate the VAT, but won\'t exempt VAT for selected countries.', 'eu-vat-for-woocommerce' ) . ' ' .
					sprintf( __( 'Country for "Base (i.e. store) country" option is set in "%s".', 'eu-vat-for-woocommerce' ),
						__( 'WooCommerce > Settings > General > Store Address', 'eu-vat-for-woocommerce' ) ),
				'desc'     => '',
				'id'       => 'alg_wc_eu_vat_preserve_in_base_country',
				'default'  => 'no',
				'type'     => 'select',
				'options'  => array(
					'yes'  => __( 'Base (i.e. store) country', 'eu-vat-for-woocommerce' ),
					'list' => __( 'Comma separated list', 'eu-vat-for-woocommerce' ),
					'no'   => __( 'Disable', 'eu-vat-for-woocommerce' ),
				),
				'custom_attributes' => '',
			),
			
			array(
				'desc_tip' => __( 'Ignored unless "Comma separated list" option is selected above.', 'eu-vat-for-woocommerce' ),
				'desc'     => sprintf( __( 'Enter country codes as comma separated list, e.g. %s.', 'eu-vat-for-woocommerce' ), '<code>IT,NL</code>' ),
				'id'       => 'alg_wc_eu_vat_preserve_in_base_country_locations',
				'default'  => '',
				'type'     => 'text',
				// 'css'      => 'width:100%;',
				'custom_attributes' => '',
			),
			
					
			array(
				'title'    => __( 'Check country by IP', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'This will check if customer\'s country (located by customer\'s IP) matches the country in entered VAT number.', 'eu-vat-for-woocommerce' ) .
					apply_filters( 'alg_wc_eu_vat_settings', '<br>' . sprintf( __( 'You will need %s plugin to enable this option.', 'eu-vat-for-woocommerce' ),
						'<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">' . __( 'EU VAT for WooCommerce Pro', 'eu-vat-for-woocommerce' ) . '</a>' ) ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_check_ip_location_country',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_eu_vat_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Check company name', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'This will check if company name matches the VAT number.', 'eu-vat-for-woocommerce' ) .
					apply_filters( 'alg_wc_eu_vat_settings', '<br>' . sprintf( __( 'You will need %s plugin to enable this option.', 'eu-vat-for-woocommerce' ),
						'<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">' . __( 'EU VAT for WooCommerce Pro', 'eu-vat-for-woocommerce' ) . '</a>' ) ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_check_company_name',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_eu_vat_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Check for matching billing country code', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'This will check if country code in VAT number matches billing country code.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_check_billing_country_code',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Allow VAT number input without country code', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'This will allow customers to enter VAT number without leading country code letters and still get VAT validated. In this case country will be automatically retrieved from billing country input.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_allow_without_country_code',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Always exempt VAT for selected user roles', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Ignored if empty.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_exempt_for_user_roles',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'wc-enhanced-select',
				'options'  => alg_wc_eu_vat()->settings['general']->get_all_user_roles(),
			),
			array(
				'title'    => __( 'Always not exempt VAT for selected user roles', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Ignored if empty.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_not_exempt_for_user_roles',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'wc-enhanced-select',
				'options'  => alg_wc_eu_vat()->settings['general']->get_all_user_roles(),
			),
			array(
				'title'    => __( 'Skip VAT validation for selected countries', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'List all countries you want VAT validation to be skipped for (i.e. VAT always valid). Ignored if empty.', 'eu-vat-for-woocommerce' ),
				'desc'     => sprintf( __( 'Enter country codes as comma separated list, e.g. %s.', 'eu-vat-for-woocommerce' ), '<code>IT,NL</code>' ),
				'id'       => 'alg_wc_eu_vat_advanced_skip_countries',
				'default'  => '',
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Validate action trigger', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Validate action will trigger based on your choice. Default: onInput', 'eu-vat-for-woocommerce' ),
				'desc'     => '',
				'id'       => 'alg_wc_eu_vat_validate_action_trigger',
				'default'  => 'oninput',
				'type'     => 'select',
				'options'  => array(
					'oninput'  => __( 'on Input', 'eu-vat-for-woocommerce' ),
					'onblur' => __( 'on Blur', 'eu-vat-for-woocommerce' ),
				),
				'custom_attributes' => '',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_validation_options',
			),
		);

		$messages_settings = array(
			array(
				'title'    => __( 'Progress Messages', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_messages_options',
			),
			array(
				'title'    => __( 'Add progress messages', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enabled/disables progress messages on checkout.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Add', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_add_progress_text',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Validating', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message when validation is in progress. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-validating</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_progress_text_validating',
				'default'  => __( 'Validating VAT. Please wait...', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Valid', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on valid VAT. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-valid</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_progress_text_valid',
				'default'  => __( 'VAT is valid.', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Not valid', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on invalid VAT. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-not-valid</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_progress_text_not_valid',
				'default'  => __( 'VAT is not valid.', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Validation failed', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on VAT validation server timeout etc. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-validation-failed</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_progress_text_validation_failed',
				'default'  => __( 'Validation failed. Please try again.', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Different shipping & billing countries', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on Different shipping & billing countries. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-not-valid-billing-country</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_shipping_billing_countries',
				'default'  => __( 'Different shipping & billing countries.', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Company name mismatch', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Message on Company name mismatch. If you want to customize the message using CSS, please use class <code>alg-wc-eu-vat-not-valid-company-mismatch</code>', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_company_name_mismatch',
				'default'  => __( 'VAT is valid, but registered to %company_name%.', 'eu-vat-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Hide messages on preserved countries list', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( '', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_hide_message_on_preserved_countries',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_messages_options',
			),
		);

		return array_merge( $validation_settings, $messages_settings );
	}

}

endif;

return new Alg_WC_EU_VAT_Settings_Validation();
