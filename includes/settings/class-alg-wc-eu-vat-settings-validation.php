<?php
/**
 * EU VAT for WooCommerce - Validation Section Settings
 *
 * @version 4.2.9
 * @since   1.5.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Settings_Validation' ) ) :

class Alg_WC_EU_VAT_Settings_Validation extends Alg_WC_EU_VAT_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 4.2.3
	 * @since   1.5.0
	 */
	function __construct() {
		$this->id   = 'validation';
		$this->desc = __( 'Validation', 'eu-vat-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 4.2.9
	 * @since   1.5.0
	 *
	 * @todo    (feature) Message if customer's check for IP location country has failed!
	 * @todo    (feature) add "Check company address" option (similar to "Check company name")
	 * @todo    (feature) "Require Country Code in VAT Number"?
	 */
	function get_settings() {
		return array(

			// Validation Options
			array(
				'title'             => __( 'Validation Options', 'eu-vat-for-woocommerce' ),
				'type'              => 'title',
				'id'                => 'alg_wc_eu_vat_validation_options',
			),
			array(
				'title'             => __( 'Validate', 'eu-vat-for-woocommerce' ),
				'desc_tip'          => __( 'Enables/disables EU VAT validation.', 'eu-vat-for-woocommerce' ),
				'desc'              => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'                => 'alg_wc_eu_vat_validate',
				'default'           => 'yes',
				'type'              => 'checkbox',
			),
			array(
				'title'             => __( 'Validate at sign-up form', 'eu-vat-for-woocommerce' ),
				'desc_tip'          => __( 'Enables/disables EU VAT validation at sign-up page.', 'eu-vat-for-woocommerce' ),
				'desc'              => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'                => 'alg_wc_eu_vat_validate_sign_up_page',
				'default'           => 'yes',
				'type'              => 'checkbox',
			),
			array(
				'title'             => __( 'Validate in "My account"', 'eu-vat-for-woocommerce' ),
				'desc_tip'          => __( 'Enables/disables EU VAT validation in "My account".', 'eu-vat-for-woocommerce' ),
				'desc'              => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'                => 'alg_wc_eu_vat_validate_my_account',
				'default'           => 'no',
				'type'              => 'checkbox',
			),
			array(
				'desc'              => (
					__( 'Message on not valid.', 'eu-vat-for-woocommerce' ) . ' ' .
					sprintf(
						/* Translators: %s: Placeholder name. */
						__( 'Replaced value: %s', 'eu-vat-for-woocommerce' ),
						'<code>%eu_vat_number%</code>'
					)
				),
				'desc_tip'          => __( 'Message will be displayed, when customer tries to checkout with invalid VAT number ("Validate" option must be enabled).', 'eu-vat-for-woocommerce' ),
				'id'                => 'alg_wc_eu_vat_not_valid_message',
				'default'           => __( '<strong>EU VAT Number</strong> is not valid.', 'eu-vat-for-woocommerce' ),
				'type'              => 'textarea',
				'alg_wc_eu_vat_raw' => true,
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_eu_vat_validation_options',
			),

			// VAT Exemption
			array(
				'title'    => __( 'VAT Exemption', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_validation_vat_exemption_options',
			),
			array(
				'title'    => __( 'Remove VAT for validated numbers', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Enables/disabled VAT exemption.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_disable_for_valid',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Keep VAT if shipping country is different from billing country', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_preserv_vat_for_different_shipping',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Keep VAT in selected countries', 'eu-vat-for-woocommerce' ),
				'desc_tip' => (
					__( 'This will validate the VAT, but won\'t exempt VAT for selected countries.', 'eu-vat-for-woocommerce' ) . ' ' .
					sprintf(
						/* Translators: %s: Settings path. */
						__( 'Country for "Base (i.e., store) country" option is set in "%s".', 'eu-vat-for-woocommerce' ),
						__( 'WooCommerce > Settings > General > Store Address', 'eu-vat-for-woocommerce' )
					)
				),
				'id'       => 'alg_wc_eu_vat_preserve_in_base_country',
				'default'  => 'no',
				'type'     => 'select',
				'options'  => array(
					'yes'  => __( 'Base (i.e., store) country', 'eu-vat-for-woocommerce' ),
					'list' => __( 'Comma separated list', 'eu-vat-for-woocommerce' ),
					'no'   => __( 'Disable', 'eu-vat-for-woocommerce' ),
				),
			),
			array(
				'desc_tip' => __( 'Ignored unless "Comma separated list" option is selected above.', 'eu-vat-for-woocommerce' ),
				'desc'     => sprintf(
					/* Translators: %s: Country code list example. */
					__( 'Enter country codes as comma separated list, e.g., %s.', 'eu-vat-for-woocommerce' ),
					'<code>IT,NL</code>'
				),
				'id'       => 'alg_wc_eu_vat_preserve_in_base_country_locations',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_validation_vat_exemption_options',
			),

			// Country & Company
			array(
				'title'             => __( 'Country & Company', 'eu-vat-for-woocommerce' ),
				'type'              => 'title',
				'id'                => 'alg_wc_eu_vat_validation_country_and_company_options',
			),
			array(
				'title'             => __( 'Check country by IP', 'eu-vat-for-woocommerce' ),
				'desc_tip'          => (
					__( 'This will check if customer\'s country (located by customer\'s IP) matches the country in entered VAT number.', 'eu-vat-for-woocommerce' ) .
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
				'id'                => 'alg_wc_eu_vat_check_ip_location_country',
				'default'           => 'no',
				'type'              => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_eu_vat_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'             => __( 'Check company name', 'eu-vat-for-woocommerce' ),
				'desc_tip'          => (
					__( 'This will check if company name matches the VAT number.', 'eu-vat-for-woocommerce' ) .
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
				'id'                => 'alg_wc_eu_vat_check_company_name',
				'default'           => 'no',
				'type'              => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_eu_vat_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'             => __( 'Check for matching billing country code', 'eu-vat-for-woocommerce' ),
				'desc_tip'          => __( 'This will check if country code in VAT number matches billing country code.', 'eu-vat-for-woocommerce' ),
				'desc'              => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'                => 'alg_wc_eu_vat_check_billing_country_code',
				'default'           => 'no',
				'type'              => 'checkbox',
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_eu_vat_validation_country_and_company_options',
			),

			// User Roles
			array(
				'title'    => __( 'User Roles', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_validation_user_role_options',
			),
			array(
				'title'    => __( 'Always exempt VAT for selected user roles', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Ignored if empty.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_exempt_for_user_roles',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'wc-enhanced-select',
				'options'  => $this->get_all_user_roles(),
			),
			array(
				'title'    => __( 'Always not exempt VAT for selected user roles', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'Ignored if empty.', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_not_exempt_for_user_roles',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'wc-enhanced-select',
				'options'  => $this->get_all_user_roles(),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_validation_user_role_options',
			),

			// Advanced
			array(
				'title'    => __( 'Advanced', 'eu-vat-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_eu_vat_validation_advanced_options',
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
				'title'    => __( 'Allow VAT number input without country code', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'This will allow customers to enter VAT number without leading country code letters and still get VAT validated. In this case country will be automatically retrieved from billing country input.', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_allow_without_country_code',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Skip VAT validation for selected countries', 'eu-vat-for-woocommerce' ),
				'desc_tip' => __( 'List all countries you want VAT validation to be skipped for (i.e., VAT always valid). Ignored if empty.', 'eu-vat-for-woocommerce' ),
				'desc'     => sprintf(
					/* Translators: %s: Country code list example. */
					__( 'Enter country codes as comma separated list, e.g., %s.', 'eu-vat-for-woocommerce' ),
					'<code>IT,NL</code>'
				),
				'id'       => 'alg_wc_eu_vat_advanced_skip_countries',
				'default'  => '',
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Validate action trigger', 'eu-vat-for-woocommerce' ),
				'desc_tip' => (
					__( 'Validate action will trigger based on your choice.', 'eu-vat-for-woocommerce' ) . ' ' .
					__( 'Default: "On Input".', 'eu-vat-for-woocommerce' )

				),
				'id'       => 'alg_wc_eu_vat_validate_action_trigger',
				'default'  => 'oninput',
				'type'     => 'select',
				'options'  => array(
					'oninput' => __( 'On Input', 'eu-vat-for-woocommerce' ),
					'onblur'  => __( 'On Blur', 'eu-vat-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Force validate on cart and checkout page load/reload', 'eu-vat-for-woocommerce' ),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_validate_force_page_reload',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Accept the VAT number if VIES is not available', 'eu-vat-for-woocommerce' ),
				'desc_tip' => sprintf(
					/* Translators: %s: Error codes. */
					__( 'This will accept the VAT number if VIES is not available for error codes: %s.', 'eu-vat-for-woocommerce' ),
					'<code>MS_UNAVAILABLE</code>, <code>GLOBAL_MAX_CONCURRENT_REQ</code>, <code>MS_MAX_CONCURRENT_REQ</code>'
				),
				'desc'     => __( 'Yes', 'eu-vat-for-woocommerce' ),
				'id'       => 'alg_wc_eu_vat_validate_vies_not_available',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_eu_vat_validation_advanced_options',
			),

		);
	}

}

endif;

return new Alg_WC_EU_VAT_Settings_Validation();
