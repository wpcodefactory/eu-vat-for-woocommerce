/**
 * EU VAT for WooCommerce - Checkout block VAT validation
 *
 * @version 4.5.6
 * @since   2.11.6
 *
 * @author  WPFactory
 */

import { useEffect, useRef, useState, useCallback } from '@wordpress/element';
import { select, useSelect, useDispatch } from '@wordpress/data';
import { CART_STORE_KEY } from '@woocommerce/block-data';
import { extensionCartUpdate } from '@woocommerce/blocks-checkout';
import { getSetting } from '@woocommerce/settings';

const { alg_wc_eu_vat_field_id, alg_wc_eu_vat_field_position_id } = getSetting(
	'eu-vat-for-woocommerce_data',
	{},
);
const sameForBillingEl = '.wc-block-checkout__use-address-for-billing input[type="checkbox"]';

/**
 * isUseBillingChecked.
 *
 * @version 4.5.6
 * @since   4.5.6
 */
const isUseBillingChecked = () => {
	const useSameForBillingEl = document.querySelector(
		sameForBillingEl
	);
	return useSameForBillingEl ? useSameForBillingEl.checked : false;
};

/**
 * moveVatFieldOnce.
 *
 * @version 4.5.6
 * @since   4.5.6
 */
const moveVatFieldOnce = ( vatNumber ) => {
	if ( vatNumber.dataset.algVatMoved === 'yes' ) {
		return;
	}

	if ( ! alg_wc_eu_vat_field_position_id ) {
		return null;
	}

	const positionedTarget = document
		.querySelector( alg_wc_eu_vat_field_position_id )
		?.closest( 'div' );

	let target = null;
	if ( positionedTarget ) {
		target = positionedTarget;
	} else {
		target = document.querySelector( alg_wc_eu_vat_field_id )?.closest( 'div' ) || null;
	}

	if ( ! target ) {
		return;
	}

	target.insertAdjacentElement(
		'afterend',
		vatNumber.closest( 'div' )
	);

	vatNumber.dataset.algVatMoved = 'yes';
}

/**
 * createVatInformationContainer.
 *
 * @version 4.5.6
 * @since   4.5.6
 */
const createVatInformationContainer = ( vatNumber, billingCountry ) => {
	const containerId = 'alg_eu_vat_for_woocommerce_field';

	if ( document.getElementById( containerId ) ) {
		return;
	}

	const wrapper = document.createElement( 'div' );
	wrapper.id = containerId;
	wrapper.className = 'alg-eu-vat-for-woocommerce-fields';
	wrapper.innerHTML = `
		${
			alg_wc_eu_vat_ajax_object.add_progress_text === 'yes'
			? '<div id="alg_wc_eu_vat_progress"></div>'
			: ''
		}
		${
			alg_wc_eu_vat_ajax_object.show_vat_details === 'yes'
			? '<div id="alg_wc_eu_vat_details"></div>'
			: ''
		}
		<div id="custom-checkout"></div>
		<input type="hidden"
			id="store_previous_country"
			name="store_previous_country"
			value="${billingCountry}"
		/>
	`;

	vatNumber.closest( 'div' ).insertAdjacentElement( 'afterend', wrapper );
}


/**
 * createVatInformationContainer.
 *
 * @version 4.5.6
 */
const algWcBlockEuVatValidateVat = ( vat_number, refresh ) => {

	var isSameBillingShipping = 'no';
	if ( isUseBillingChecked() ) {
		isSameBillingShipping = 'yes';
	}

	const store    = select( CART_STORE_KEY );
	const cartData = store.getCartData();

	const billingCountry = cartData.billingAddress.country;
	const billingCompany = cartData.billingAddress.company;

	var progress           = document.getElementById( 'alg_wc_eu_vat_progress' );
	var eu_vat_field       = document.getElementById( 'contact-alg_eu_vat-billing_eu_vat_number' );
	var place_order_button = document.getElementsByClassName( "wc-block-components-checkout-place-order-button" )[0];

	const previous_country = document.getElementById( 'store_previous_country' );

	place_order_button.disabled = true;

	var payLoad = new URLSearchParams( {
		'action': 'alg_wc_eu_vat_validate_action',
		'channel': 'bloock_api',
		'alg_wc_eu_vat_to_check': vat_number,
		'billing_country': billingCountry,
		'billing_company': billingCompany,
		'shipping_country': cartData.shippingAddress.country,
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

		if ( alg_wc_eu_vat_ajax_object.status_codes['VAT_VALID'] === data.status ) {

			eu_vat_field.classList.add( 'woocommerce-validated' );
			if ( progress ) {
				progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_valid;
				progress.classList.remove( "alg-wc-eu-vat-not-valid" );
				progress.classList.remove( "alg-wc-eu-vat-validating" );
				progress.classList.add( "alg-wc-eu-vat-valid" );
			}

		} else if ( alg_wc_eu_vat_ajax_object.status_codes['VAT_NOT_VALID'] === data.status ) {

			eu_vat_field.classList.add( 'woocommerce-invalid' );
			if ( progress ) {
				progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_not_valid;
				progress.classList.remove( "alg-wc-eu-vat-valid" );
				progress.classList.remove( "alg-wc-eu-vat-validating" );
				progress.classList.add( "alg-wc-eu-vat-not-valid" );
			}
		} else if ( alg_wc_eu_vat_ajax_object.status_codes['WRONG_BILLING_COUNTRY'] === data.status ) {

			eu_vat_field.classList.add( 'woocommerce-invalid' );
			if ( progress ) {
				progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_wrong_billing_country;
				progress.classList.remove( "alg-wc-eu-vat-valid" );
				progress.classList.remove( "alg-wc-eu-vat-validating" );
				progress.classList.add( "alg-wc-eu-vat-not-valid" );
			}
		} else if ( alg_wc_eu_vat_ajax_object.status_codes['KEEP_VAT_SHIPPING_COUNTRY'] === data.status ) {

			eu_vat_field.classList.add( 'woocommerce-invalid' );
			if ( progress ) {
				progress.innerHTML = alg_wc_eu_vat_ajax_object.text_shipping_billing_countries;
				progress.classList.remove( "alg-wc-eu-vat-valid" );
				progress.classList.remove( "alg-wc-eu-vat-validating" );
				progress.classList.add( "alg-wc-eu-vat-not-valid" );
			}
		} else if ( alg_wc_eu_vat_ajax_object.status_codes['COMPANY_NAME'] === data.status ) {

			eu_vat_field.classList.add( 'woocommerce-invalid' );
			eu_vat_field.classList.add( 'woocommerce-invalid-mismatch' );
			if ( progress ) {
				progress.innerHTML = alg_wc_eu_vat_ajax_object.company_name_mismatch;
				progress.classList.remove( "alg-wc-eu-vat-valid" );
				progress.classList.remove( "alg-wc-eu-vat-validating" );
				progress.classList.add( "alg-wc-eu-vat-not-valid" );
			}
		} else if ( alg_wc_eu_vat_ajax_object.status_codes['EMPTY_VAT'] === data.status ) {

			if ( eu_vat_field.hasAttribute( 'required' ) ) {
				eu_vat_field.classList.add( 'woocommerce-invalid' );
				eu_vat_field.classList.remove( 'woocommerce-validated' );
				if ( progress ) {
					progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_is_required;
					progress.classList.remove( "alg-wc-eu-vat-valid" );
					progress.classList.remove( "alg-wc-eu-vat-validating" );
					progress.classList.remove( "alg-wc-eu-vat-not-valid" );
				}
			} else {
				eu_vat_field.classList.remove( 'woocommerce-invalid' );
				eu_vat_field.classList.remove( 'woocommerce-validated' );
				if ( progress ) {
					progress.innerHTML = '';
					progress.className = '';
				}
			}
		} else if ( alg_wc_eu_vat_ajax_object.status_codes['KEEP_VAT_COUNTRIES'] === data.status ) {

			eu_vat_field.classList.remove( 'woocommerce-invalid' );
			eu_vat_field.classList.remove( 'woocommerce-validated' );
			if ( progress ) {
				progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_validation_preserv;
				progress.classList.remove( "alg-wc-eu-vat-valid" );
				progress.classList.remove( "alg-wc-eu-vat-validating" );
				progress.classList.remove( "alg-wc-eu-vat-not-valid" );
				progress.classList.add( "alg-wc-eu-vat-not-valid" );
			}
		} else if ( alg_wc_eu_vat_ajax_object.status_codes['VIES_UNAVAILABLE'] === data.status ) {

			eu_vat_field.classList.remove( 'woocommerce-invalid' );
			eu_vat_field.classList.remove( 'woocommerce-validated' );
			if ( progress ) {
				progress.innerHTML = alg_wc_eu_vat_ajax_object.vies_not_available.replace( "%vies_error%", data.error );
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

		// This line is to save changes in checkout customer data.
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

/**
 * Block.
 *
 * @version 4.5.6
 */
const Block = () => {

	const store = select( CART_STORE_KEY );
	const cartData = store.getCartData();

	const billingCountry = cartData.billingAddress.country;
	const shippingCountry = cartData.shippingAddress.country;


	useEffect( () => {

		const vatNumber = document.querySelector( alg_wc_eu_vat_field_id );
		if ( ! vatNumber ) {
			return;
		}

		moveVatFieldOnce( vatNumber );
		createVatInformationContainer( vatNumber, billingCountry );

		const verifyOnFirstLoad = function () {
			if ( vatNumber.value !== '' ) {
				algWcBlockEuVatValidateVat( vatNumber.value, true );
			}
		};
		setTimeout( verifyOnFirstLoad, 500 );

		const triggerVatValidate = function() {
			algWcBlockEuVatValidateVat( vatNumber.value, true );
		};

		const vatTrigger =
			alg_wc_eu_vat_ajax_object.action_trigger === 'onblur'
			? 'blur'
			: 'input';
		vatNumber.addEventListener( vatTrigger, triggerVatValidate );

		const checkbox = document.querySelector(
			sameForBillingEl
		);
		if ( checkbox ) {
			checkbox.addEventListener( 'input', triggerVatValidate );
		}

		return () => {
			vatNumber.removeEventListener( vatTrigger, triggerVatValidate );
		};

	}, [billingCountry, shippingCountry] );

	return null;
};

export default Block;