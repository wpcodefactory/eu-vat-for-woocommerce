/**
 * EU VAT for WooCommerce - Checkout block VAT validation
 *
 * @version 4.0.0
 * @since   2.11.6
 *
 * @author  WPFactory
 */
import {useEffect, useState, useCallback} from '@wordpress/element';
import {CheckboxControl, ValidatedTextInput} from '@woocommerce/blocks-checkout';
import {getSetting} from '@woocommerce/settings';
import {select, useSelect, useDispatch} from '@wordpress/data';
import {__} from '@wordpress/i18n';

const {optInDefaultText} = getSetting( 'eu-vat-for-woocommerce_data', '' );

// Global import
const {registerCheckoutBlock, extensionCartUpdate} = wc.blocksCheckout;

const {CART_STORE_KEY} = window.wc.wcBlocksData;

const {hasError} = false;

const algReloadOnFirst = () => {

	const store_cb = select( CART_STORE_KEY );
	const cartData_cb = store_cb.getCartData();

	const billCountry = cartData_cb.billingAddress.country;
	const billCompany = cartData_cb.billingAddress.company;

	var payLoad = new URLSearchParams( {
		'action': 'alg_wc_eu_vat_validate_action',
		'channel': 'bloock_api',
		'alg_wc_eu_vat_to_check': 'checkout_block_first_load',
		'billing_country': billCountry,
		'billing_company': billCompany,
	} );

	fetch( alg_wc_eu_vat_ajax_object.ajax_url, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
		},
		body: payLoad,
	} )
		.then( ( response ) => response.json() )
		.then( ( data ) => {
			extensionCartUpdate( {
				namespace: 'alg-wc-eu-vat-extension-namespace-reload-first',
				data: {
					eu_vat_number: ''
				},
			} );

		} );
};
export {algReloadOnFirst};

const algWcBlockEuVatValidateVat = ( vat_number, refresh ) => {

	var isSameBillingShipping = 'no';
	if ( isUseBillingChecked() ) {
		isSameBillingShipping = 'yes';
	}

	const store = select( CART_STORE_KEY );
	const cartData = store.getCartData();

	const billingCountry = cartData.billingAddress.country;
	const billingCompany = cartData.billingAddress.company;

	var progress = document.getElementById( 'alg_wc_eu_vat_progress' );
	// var eu_vat_field = document.getElementById('alg_eu_vat_for_woocommerce_field');

	// var eu_vat_field = document.getElementById('contact-alg_eu_vat/billing_eu_vat_number');
	var eu_vat_field = document.getElementById( 'contact-alg_eu_vat-billing_eu_vat_number' );

	var place_order_button = document.getElementsByClassName( "wc-block-components-checkout-place-order-button" )[0];

	const previous_country = document.getElementById( 'store_previous_country' );

	place_order_button.disabled = true;

	var payLoad = new URLSearchParams( {
		'action': 'alg_wc_eu_vat_validate_action',
		'channel': 'bloock_api',
		'alg_wc_eu_vat_to_check': vat_number,
		'billing_country': billingCountry,
		'billing_company': billingCompany
	} );

	if ( progress ) {
		progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_validating;
		progress.classList.remove( "alg-wc-eu-vat-not-valid" );
		progress.classList.remove( "alg-wc-eu-vat-validating" );
		progress.classList.remove( "alg-wc-eu-vat-valid" );
		progress.classList.add( "alg-wc-eu-vat-validating" );
	}

	const vatDetailsDiv = document.getElementById( 'alg_wc_eu_vat_details' );
	if ( vatDetailsDiv ) {
		vatDetailsDiv.innerHTML = ''; // Clear the content
	}

	eu_vat_field.classList.remove( 'woocommerce-invalid' );
	eu_vat_field.classList.remove( 'woocommerce-validated' );
	eu_vat_field.classList.remove( 'woocommerce-invalid-mismatch' );

	fetch( alg_wc_eu_vat_ajax_object.ajax_url, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
		},
		body: payLoad,
	} )
		.then( ( response ) => response.json() )
		.then( ( data ) => {

			if ( data.vat_details && vatDetailsDiv ) {
				let vat_details = data.vat_details;
				let ulElement = document.createElement( 'ul' );

				for ( let key in vat_details ) {
					if ( vat_details.hasOwnProperty( key ) ) {
						let liElement = document.createElement( 'li' );
						liElement.textContent = `${vat_details[key].label}: ${vat_details[key].data}`;
						ulElement.appendChild( liElement );
					}
				}
				vatDetailsDiv?.replaceChildren( ulElement ); // Clear and replace content
			}

			if ( data.status == '1' ) {
				eu_vat_field.classList.add( 'woocommerce-validated' );

				if ( progress ) {
					progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_valid;
					progress.classList.remove( "alg-wc-eu-vat-not-valid" );
					progress.classList.remove( "alg-wc-eu-vat-validating" );
					progress.classList.add( "alg-wc-eu-vat-valid" );
				}

			} else if ( data.status == '0' ) {

				eu_vat_field.classList.add( 'woocommerce-invalid' );
				if ( progress ) {
					progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_not_valid;
					progress.classList.remove( "alg-wc-eu-vat-valid" );
					progress.classList.remove( "alg-wc-eu-vat-validating" );
					progress.classList.add( "alg-wc-eu-vat-not-valid" );
				}
			} else if ( data.status == '4' ) {

				eu_vat_field.classList.add( 'woocommerce-invalid' );
				if ( progress ) {
					progress.innerHTML = alg_wc_eu_vat_ajax_object.text_shipping_billing_countries;
					progress.classList.remove( "alg-wc-eu-vat-valid" );
					progress.classList.remove( "alg-wc-eu-vat-validating" );
					progress.classList.add( "alg-wc-eu-vat-not-valid" );
				}
			} else if ( data.status == '5' ) {

				eu_vat_field.classList.add( 'woocommerce-invalid' );
				eu_vat_field.classList.add( 'woocommerce-invalid-mismatch' );
				if ( progress ) {
					progress.innerHTML = alg_wc_eu_vat_ajax_object.company_name_mismatch;
					progress.classList.remove( "alg-wc-eu-vat-valid" );
					progress.classList.remove( "alg-wc-eu-vat-validating" );
					progress.classList.add( "alg-wc-eu-vat-not-valid" );
				}
			} else if ( data.status == '6' ) {

				eu_vat_field.classList.remove( 'woocommerce-invalid' );
				eu_vat_field.classList.remove( 'woocommerce-validated' );
				if ( progress ) {
					progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_validation_failed;
					progress.classList.remove( "alg-wc-eu-vat-valid" );
					progress.classList.remove( "alg-wc-eu-vat-validating" );
					progress.classList.remove( "alg-wc-eu-vat-not-valid" );
				}
			} else if ( data.status == '7' ) {
				eu_vat_field.classList.remove( 'woocommerce-invalid' );
				eu_vat_field.classList.remove( 'woocommerce-validated' );
				if ( progress ) {
					progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_validation_preserv;
					progress.classList.remove( "alg-wc-eu-vat-valid" );
					progress.classList.remove( "alg-wc-eu-vat-validating" );
					progress.classList.remove( "alg-wc-eu-vat-not-valid" );
					progress.classList.add( "alg-wc-eu-vat-not-valid" );
				}
			} else if ( data.status == '8' ) {
				eu_vat_field.classList.remove( 'woocommerce-invalid' );
				eu_vat_field.classList.remove( 'woocommerce-validated' );
				if ( progress ) {
					progress.innerHTML = alg_wc_eu_vat_ajax_object.vies_not_available;
					progress.classList.remove( "alg-wc-eu-vat-valid" );
					progress.classList.remove( "alg-wc-eu-vat-validating" );
					progress.classList.remove( "alg-wc-eu-vat-not-valid" );

					progress.classList.add( "alg-wc-eu-vat-not-valid" );
				}
			} else {

				eu_vat_field.classList.add( 'woocommerce-invalid' );

				if ( progress ) {
					progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_validation_failed;
					progress.classList.remove( "alg-wc-eu-vat-valid" );
					progress.classList.remove( "alg-wc-eu-vat-validating" );
					progress.classList.remove( "alg-wc-eu-vat-not-valid" );
					progress.classList.add( "alg-wc-eu-vat-not-valid" );
				}
			}

			previous_country.value = billingCountry;

			// this line is to save changes in checkout customer data.
			if ( refresh ) {
				extensionCartUpdate( {
					namespace: 'alg-wc-eu-vat-extension-namespace',
					data: {
						eu_vat_number: vat_number,
						eu_country: billingCountry,
						same_billing_shipping: isSameBillingShipping,
					},
				} );
			}

			place_order_button.disabled = false;

		} );
};
export {algWcBlockEuVatValidateVat};

const getCountryCode = countryName => {
	return (
		(
			typeof alg_wc_eu_frontend_countries_object[countryName] !== "undefined"
		) ? alg_wc_eu_frontend_countries_object[countryName] : ''
	);
};

const isBillingCountryChanged = countryOnChange => {
	var countryCode = getCountryCode( countryOnChange );
	const country_stored = document.getElementById( 'store_previous_country' );
	if ( countryCode !== country_stored ) {
		country_stored.value = countryCode;
		return true;
	}

	return false;
};

const isUseBillingChecked = () => {
	var checked = false;
	var use_same_for_billing_el = document.getElementsByClassName( 'wc-block-checkout__use-address-for-billing' );

	for ( let m = 0; m < use_same_for_billing_el.length; m ++ ) {

		var checkbx_el = use_same_for_billing_el[m].getElementsByTagName( 'input' );

		for ( let n = 0; n < checkbx_el.length; n ++ ) {
			checked = checkbx_el[n].checked
		}
	}

	return checked;
};

const Block = ( {children, checkoutExtensionData} ) => {

	const {setExtensionData, getExtensionData} = checkoutExtensionData;

	const {setValidationErrors, clearValidationError} = useDispatch(
		'wc/store/validation'
	);

	const {CART_STORE_KEY} = window.wc.wcBlocksData;

	const store = select( CART_STORE_KEY );
	const cartData = store.getCartData();

	const billingCountry = cartData.billingAddress.country;

	useEffect( () => {

		// var vat_number = document.getElementById('contact-alg_eu_vat/billing_eu_vat_number');
		var vat_number = document.getElementById( 'contact-alg_eu_vat-billing_eu_vat_number' );

		var verifyOnFirstLoad = function () {
			if ( vat_number.value !== '' ) {
				algWcBlockEuVatValidateVat( vat_number.value, true );
			}

		};

		setTimeout( verifyOnFirstLoad, 500 );

		var onChange = function ( evt ) {

			algWcBlockEuVatValidateVat( evt.target.value, true );

		};

		vat_number.addEventListener( 'input', onChange, false );

		var onChangeInput = function ( evt ) {

			var $objVal = evt.target.value;
			if ( isBillingCountryChanged( $objVal ) ) {
				if ( vat_number.value !== '' ) {
					algWcBlockEuVatValidateVat( vat_number.value, true );
				}
			}

		};

		var onChangeCheckbox = function ( evt ) {
			addlistenerToCountryField();
		};

		var addlistenerToCountryField = function () {

			var use_same_address = false;
			var addListenerID = 'billing-country';

			if ( isUseBillingChecked() ) {
				use_same_address = true;
				addListenerID = 'shipping-country';
			}

			var countries_input = document.getElementsByClassName( 'wc-block-components-country-input' );

			for ( let i = 0; i < countries_input.length; i ++ ) {

				if (
					countries_input[i].children[0].getAttribute( 'id' ) &&
					countries_input[i].children[0].getAttribute( 'id' ).toString() === addListenerID
				) {
					var inputs = countries_input[i].getElementsByTagName( 'input' );

					for ( let j = 0; j < inputs.length; j ++ ) {
						inputs[j].addEventListener( 'blur', onChangeInput, false );
					}

				}

			}
		};

		window.addEventListener( "load", function ( event ) {

			addlistenerToCountryField();

			var use_same_for_billing = document.getElementsByClassName( 'wc-block-checkout__use-address-for-billing' );

			for ( let k = 0; k < use_same_for_billing.length; k ++ ) {

				var checkbx = use_same_for_billing[k].getElementsByTagName( 'input' );

				for ( let l = 0; l < checkbx.length; l ++ ) {
					checkbx[l].addEventListener( 'change', onChangeCheckbox, false );
				}
			}
		} );

	}, [clearValidationError, setValidationErrors, setExtensionData] );

	return (
		<>
			<div id={'alg_eu_vat_for_woocommerce_field'} className={'alg-eu-vat-for-woocommerce-fields'}>
				{alg_wc_eu_vat_ajax_object.add_progress_text === 'yes' && <div id="alg_wc_eu_vat_progress"></div>}
				{alg_wc_eu_vat_ajax_object.show_vat_details === 'yes' && <div id="alg_wc_eu_vat_details"></div>}
				<div id="custom-checkout"></div>
				<input type="hidden" id="store_previous_country" name="store_previous_country" value={billingCountry}/>
			</div>
		</>

	);
};

export default Block;