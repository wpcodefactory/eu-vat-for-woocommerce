/**
 * EU VAT for WooCommerce - External dependencies
 *
 * @version 4.0.0
 *
 * @author  WPFactory
 */
import { useEffect, useState, useCallback } from '@wordpress/element';
import { CheckboxControl, ValidatedTextInput } from '@woocommerce/blocks-checkout';
import { getSetting } from '@woocommerce/settings';
import { select, useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const { optInDefaultText } = getSetting('eu-vat-for-woocommerce_data', '');

// Global import
const { registerCheckoutBlock, extensionCartUpdate } = wc.blocksCheckout;

const { hasError } = false;

const algWcBlockEuVatValidateVat = ( vat_number, refresh ) => {

		const { CART_STORE_KEY } = window.wc.wcBlocksData;

		const store = select( CART_STORE_KEY );
		const cartData = store.getCartData();

		const billingCountry = cartData.billingAddress.country;
		const billingCompany = cartData.billingAddress.company;

		var progress = document.getElementById('alg_wc_eu_vat_progress');
		var eu_vat_field = document.getElementById('alg_eu_vat_for_woocommerce_field');
		var place_order_button = document.getElementsByClassName("wc-block-components-checkout-place-order-button")[0];

		const previous_country = document.getElementById('store_previous_country');

		place_order_button.disabled = true;

		var payLoad = new URLSearchParams({
				'action': 'alg_wc_eu_vat_validate_action',
				'channel': 'bloock_api',
				'alg_wc_eu_vat_to_check': vat_number,
				'billing_country': billingCountry,
				'billing_company': billingCompany,
			});

		progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_validating;

		progress.classList.remove("alg-wc-eu-vat-not-valid");
		progress.classList.remove("alg-wc-eu-vat-validating");
		progress.classList.remove("alg-wc-eu-vat-valid");

		progress.classList.add("alg-wc-eu-vat-validating");

		eu_vat_field.classList.remove( 'woocommerce-invalid' );
		eu_vat_field.classList.remove( 'woocommerce-validated' );
		eu_vat_field.classList.remove( 'woocommerce-invalid-mismatch' );

		fetch( alg_wc_eu_vat_ajax_object.ajax_url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: payLoad,
		})
		.then((response) => response.json())
		.then((data) => {

			if( data.status == '1' ) {
				eu_vat_field.classList.add( 'woocommerce-validated' );

				progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_valid;
				progress.classList.remove("alg-wc-eu-vat-not-valid");
				progress.classList.remove("alg-wc-eu-vat-validating");
				progress.classList.add("alg-wc-eu-vat-valid");

			} else if( data.status == '0' ) {

				eu_vat_field.classList.add( 'woocommerce-invalid' );

				progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_not_valid;

				progress.classList.remove("alg-wc-eu-vat-valid");
				progress.classList.remove("alg-wc-eu-vat-validating");
				progress.classList.add("alg-wc-eu-vat-not-valid");
			} else if( data.status == '4' ) {

				eu_vat_field.classList.add( 'woocommerce-invalid' );

				progress.innerHTML = alg_wc_eu_vat_ajax_object.text_shipping_billing_countries;
				progress.classList.remove("alg-wc-eu-vat-valid");
				progress.classList.remove("alg-wc-eu-vat-validating");
				progress.classList.add("alg-wc-eu-vat-not-valid");
			} else if( data.status == '5' ) {

				eu_vat_field.classList.add( 'woocommerce-invalid' );
				eu_vat_field.classList.add( 'woocommerce-invalid-mismatch' );

				progress.innerHTML = alg_wc_eu_vat_ajax_object.company_name_mismatch;
				progress.classList.remove("alg-wc-eu-vat-valid");
				progress.classList.remove("alg-wc-eu-vat-validating");
				progress.classList.add("alg-wc-eu-vat-not-valid");
			} else if( data.status == '6' ) {

				eu_vat_field.classList.remove( 'woocommerce-invalid' );
				eu_vat_field.classList.remove( 'woocommerce-validated' );

				progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_validation_failed;
				progress.classList.remove("alg-wc-eu-vat-valid");
				progress.classList.remove("alg-wc-eu-vat-validating");
				progress.classList.remove("alg-wc-eu-vat-not-valid");

				// clearValidationError('billing_eu_vat_number');

			} else {

				eu_vat_field.classList.add( 'woocommerce-invalid' );

				progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_validation_failed;
				progress.classList.remove("alg-wc-eu-vat-valid");
				progress.classList.remove("alg-wc-eu-vat-validating");
				progress.classList.add("alg-wc-eu-vat-not-valid");
			}

			// if( previous_country.value !== billingCountry ) {
				previous_country.value =  billingCountry;

				// this line is to save changes in checkout customer data.
				if( refresh ) {
					// wp.data.dispatch('wc/store/cart').invalidateResolutionForStore();
				}
			// }
			place_order_button.disabled = false;

		 });
};
export { algWcBlockEuVatValidateVat };

const Block = ({ children, checkoutExtensionData }) => {
	const [checked, setChecked] = useState(false);
	const [ billingEuVatNumber, setBillingEuVatNumber ] = useState('');
	const { setExtensionData, getExtensionData } = checkoutExtensionData;

	const { setValidationErrors, clearValidationError } = useDispatch(
		'wc/store/validation'
	);

	const { CART_STORE_KEY } = window.wc.wcBlocksData;

	const store = select( CART_STORE_KEY );
	const cartData = store.getCartData();

	const billingCountry = cartData.billingAddress.country;

	useEffect(() => {

		setExtensionData( 'eu-vat-for-woocommerce-block-example', 'billing_eu_vat_number', billingEuVatNumber  );

		var gov = document.getElementById('contact-namespace/gov-id');
		var onChange = function(evt) {
		  console.info(this.value);
		  // or
		  console.info(evt.target.value);
		  console.info("test");
		  algWcBlockEuVatValidateVat( evt.target.value, true );
		};

		gov.addEventListener('input', onChange, false);

		/*
		if ( !billingEuVatNumber ) {
			setValidationErrors({
				'billing_eu_vat_number': {
					message: __('Please enter a valid EU VAT number.', 'eu-vat-for-woocommerce'),
					hidden: false,
				},
			});
			return;
		}
		*/

		clearValidationError('billing_eu_vat_number');
	}, [clearValidationError, setValidationErrors, checked, setExtensionData]);

	const onInputChange = useCallback(
		( value ) => {
			setBillingEuVatNumber( value );
			setExtensionData( 'eu-vat-for-woocommerce-block-example', 'billing_eu_vat_number', value );
			if(value == ''){
				/*
				setValidationErrors({
					'billing_eu_vat_number': {
						message: __('', 'eu-vat-for-woocommerce'),
						hidden: true,
					}
				});
				return;
				*/
			}
			else
			{
				// console.log(alg_wc_eu_vat_ajax_object.add_progress_text);
				algWcBlockEuVatValidateVat( value, true );

				extensionCartUpdate( {
					namespace: 'alg-wc-eu-vat-extension-namespace',
					data: {
						eu_vat_number: value
					},
				} );
			}
		},
		[ setBillingEuVatNumber. setExtensionData ]
	)

	const onInputBlur = useCallback(
		( value ) => {
			setBillingEuVatNumber( value );
			setExtensionData( 'eu-vat-for-woocommerce-block-example', 'billing_eu_vat_number', value );
			if(value == ''){
				/*
				setValidationErrors({
					'billing_eu_vat_number': {
						message: __('', 'eu-vat-for-woocommerce'),
						hidden: true,
					}
				});
				*/
			}

			// this line is to save changes in checkout customer data.
			// wp.data.dispatch('wc/store/cart').invalidateResolutionForStore();

		},
		[ setBillingEuVatNumber. setExtensionData ]
	)

	const { validationError, validationErrorInput  } = useSelect((select) => {
		const store = select('wc/store/validation');
		return {
			validationError: store.getValidationError('eu-vat-for-woocommerce'),
			validationErrorInput: store.getValidationError('billing_eu_vat_number')
		};
	});

	return (
		<>
			<div id={ 'alg_eu_vat_for_woocommerce_field' } className={ 'alg-eu-vat-for-woocommerce-fields' }>
				<ValidatedTextInput
					id="billing_eu_vat_number"
					type="text"
					required={true}
					className={'billing-eu-vat-number'}
					label={
						__( 'EU VAT Number', 'eu-vat-for-woocommerce' )
					}
					value={ billingEuVatNumber }
					onChange={ onInputChange }
					onBlur={ onInputBlur }
				/>
				<div id="alg_wc_eu_vat_progress"></div>
				<div id="custom-checkout"></div>
				<input type="hidden" id="store_previous_country" name="store_previous_country" value={ billingCountry } />
			</div>
		</>

	);
};

export default Block;
