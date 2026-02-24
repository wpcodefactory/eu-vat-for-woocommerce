/**
 * EU VAT for WooCommerce - Checkout block VAT validation
 *
 * @version 4.5.8
 * @since   2.11.6
 *
 * @author  WPFactory
 */

import { useEffect, useRef, useState, useCallback } from '@wordpress/element';
import { select, useSelect, dispatch, useDispatch } from '@wordpress/data';
import { CART_STORE_KEY } from '@woocommerce/block-data';
import { extensionCartUpdate } from '@woocommerce/blocks-checkout';
import { getSetting } from '@woocommerce/settings';
import { debounce } from 'lodash';

const alg_wc_eu_vat_ajax_object = window.alg_wc_eu_vat_ajax_object || {};
const {
	do_show_hide_by_billing_company,
	is_required,
	optional_text
} = alg_wc_eu_vat_ajax_object;
const VAT_SETTINGS = getSetting( 'eu-vat-for-woocommerce_data', {} );
const {
	alg_wc_eu_vat_field_id: VAT_FIELD_ID,
	alg_wc_eu_vat_field_position_id: VAT_FIELD_POSITION_ID,
	get_show_in_countries: VAT_FIELD_SHOW_IN_COUNTRIES
} = VAT_SETTINGS;
const SAME_FOR_BILLING_SELECTOR = '.wc-block-checkout__use-address-for-billing input[type="checkbox"]';
const VAT_DETAILS_CONTAINER_ID = 'alg_eu_vat_for_woocommerce_field';

/**
 * DOM Utilities
 *
 * @version 4.5.8
 * @since   4.5.8
 */
const DOMUtils = {
	getVatFieldId: () => VAT_FIELD_ID,
	getVatFieldPositionId: () => VAT_FIELD_POSITION_ID,
	getVatDetailsContainerId: () => VAT_DETAILS_CONTAINER_ID,
	getVatField: () => document.querySelector( DOMUtils.getVatFieldId() ),
	getVatDetailsContainer: () => document.getElementById( DOMUtils.getVatDetailsContainerId() ),
	getUseBillingCheckbox: () => document.querySelector( SAME_FOR_BILLING_SELECTOR ),
	isUseBillingChecked: () => {
		const checkbox = DOMUtils.getUseBillingCheckbox();
		return checkbox ? checkbox.checked : false;
	},
}

/**
 * moveVatFieldOnce.
 *
 * @version 4.5.8
 * @since   4.5.6
 */
const moveVatFieldOnce = ( vatNumber ) => {
	if ( vatNumber.dataset.algVatMoved === 'yes' ) {
		return;
	}

	const positionedTarget = DOMUtils.getVatFieldPositionId() ?
		document.querySelector( DOMUtils.getVatFieldPositionId() )?.closest( 'div' ) :
		null;

	let target = null;
	if ( positionedTarget ) {
		target = positionedTarget;
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
 * @version 4.5.8
 * @since   4.5.6
 */
const createVatInformationContainer = ( vatNumber, billingCountry ) => {

	if ( DOMUtils.getVatDetailsContainer() ) {
		return;
	}

	const wrapper = document.createElement( 'div' );
	wrapper.id = VAT_DETAILS_CONTAINER_ID;
	wrapper.className = 'alg-eu-vat-for-woocommerce-fields';
	wrapper.innerHTML = `
		${
		alg_wc_eu_vat_ajax_object.add_progress_text === 'yes' ?
			'<div id="alg_wc_eu_vat_progress"></div>' :
			''
	}
		${
		alg_wc_eu_vat_ajax_object.show_vat_details === 'yes' ?
			'<div id="alg_wc_eu_vat_details"></div>' :
			''
	}
		<div id="custom-checkout"></div>
		<input
			type="hidden"
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
 * @version 4.5.8
 */
const algWcBlockEuVatValidateVat = ( vat_number, refresh ) => {

	let isSameBillingShipping = 'no';
	if ( DOMUtils.isUseBillingChecked() ) {
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
		} else if ( alg_wc_eu_vat_ajax_object.status_codes['KEEP_VAT_BASE_COUNTRY_SHIPPING'] === data.status ) {
			eu_vat_field.classList.add( 'woocommerce-invalid' );
			if ( progress ) {
				progress.innerHTML = alg_wc_eu_vat_ajax_object.text_base_country_shipping_countries;
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
					billing_company: billingCompany,
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
 * @version 4.5.8
 */
const Block = (  { checkoutExtensionData, extensions } ) => {

	const { setExtensionData } = checkoutExtensionData;

	const cartData = useSelect((select) =>
		select(CART_STORE_KEY).getCartData()
	);
	const { billingAddress, shippingAddress } = cartData;

	const [hideVat, setHideVat] = useState(false);
	const [requiredVat, setRequiredVat] = useState(false);
	const [ euVatValue, setEuVatNumber ] = useState( '' );


	/**
	 * Actual validation logic (non-debounced)
	 */
	const performValidation = useCallback( () => {
		const vatField = DOMUtils.getVatField();

		if ( ! vatField ) {
			return;
		}

		if ( hideVat ) {
			vatField.value = '';
		}

		const currentValue = vatField.value;
		setEuVatNumber( currentValue );

		algWcBlockEuVatValidateVat( currentValue, true );
	}, [hideVat] );

	/**
	 * Debounced validation trigger - waits 500ms after last input
	 */
	const debouncedValidation = useRef(
		debounce( ( callback ) => {
			callback();
		}, 500 ) // Adjust delay as needed (500ms = 0.5 seconds)
	).current;

	/**
	 * Cleanup debounced function on unmount
	 */
	useEffect( () => {
		return () => {
			debouncedValidation.cancel();
		};
	}, [debouncedValidation] );

	/**
	 * Stable VAT validation trigger (debounced)
	 */
	const triggerValidation = useCallback( () => {
		debouncedValidation( performValidation );
	}, [debouncedValidation, performValidation] );

	// Update extension data when VAT value changes
	useEffect( () => {
		setExtensionData(
			'eu-vat-for-woocommerce-block-example',
			'billing_eu_vat_number',
			euVatValue
		);
	}, [ setExtensionData, euVatValue ] );

	// VAT field listeners (once, safe)
	useEffect( () => {
		const vatField = DOMUtils.getVatField();
		if ( ! vatField ) {
			return;
		}

		moveVatFieldOnce( vatField );
		createVatInformationContainer( vatField, billingAddress.country );

		const trigger = alg_wc_eu_vat_ajax_object.action_trigger ?? 'onblur';
		const triggerType = trigger === 'onblur' ? 'blur' : 'input';

		vatField.addEventListener( triggerType, triggerValidation );

		const billingCheckbox = DOMUtils.getUseBillingCheckbox();
		if ( billingCheckbox ) {
			billingCheckbox.addEventListener( 'input', triggerValidation );
		}

		return () => {
			vatField.removeEventListener( triggerType, triggerValidation );
			if ( billingCheckbox && triggerValidation ) {
				billingCheckbox.removeEventListener( 'input', triggerValidation );
			}
		};
	}, [billingAddress.country, triggerValidation] );

	// Trigger validation on relevant changes
	useEffect( () => {
		triggerValidation();
	}, [
		billingAddress.country,
		billingAddress.company,
		shippingAddress.country,
		shippingAddress.company,
		triggerValidation
	] );

	// Toggle VAT field visibility + required attribute
	useEffect( () => {
		const vatField = DOMUtils.getVatField();
		const vatContainer = DOMUtils.getVatDetailsContainer();

		if ( vatField ) {
			const container = vatField.closest( 'div' );
			if ( container ) {
				container.style.display = hideVat ? 'none' : '';
			}

			// vatField.required = hideVat? false : requiredVat;
		}

		if ( vatContainer ) {
			vatContainer.style.display = hideVat ? 'none' : '';
		}
	}, [hideVat, requiredVat] );

	// Show/hide VAT + update required status
	useEffect( () => {
		const showCountries = VAT_FIELD_SHOW_IN_COUNTRIES ?
			VAT_FIELD_SHOW_IN_COUNTRIES.split( ',' ) : [];
		const countryAllowed = showCountries.length ?
			! showCountries.includes( billingAddress.country ) : false;

		const companyCheck = do_show_hide_by_billing_company ?
			billingAddress.company === '' : false;

		setHideVat( countryAllowed || companyCheck );

		if ( is_required === 'yes_for_company' ) {
			const progress = document.getElementById( 'alg_wc_eu_vat_progress' );
			const vatField = DOMUtils.getVatField();
			if ( ! vatField ) {
				return;
			}

			const label = vatField.closest( 'div' )?.querySelector( 'label' );
			if ( ! label ) {
				return;
			}

			if ( billingAddress.company !== '' ) {
				// remove (option)
				label.textContent = label.textContent.replace( optional_text, '' );
				setRequiredVat( true );
			} else {
				// add (option)
				if ( ! label.textContent.includes( optional_text ) ) {
					label.textContent += optional_text;
				}
				if ( progress && vatField.value === '' ) {
					progress.textContent = '';
				}
				setRequiredVat( false );
			}
		}
	}, [billingAddress.country, billingAddress.company] );

	return null;
};
export default Block;
