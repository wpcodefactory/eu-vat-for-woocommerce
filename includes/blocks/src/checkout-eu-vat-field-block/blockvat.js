/**
 * EU VAT for WooCommerce - Checkout block VAT validation
 *
 * @version 4.6.2
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
	alg_wc_eu_customer_decide_field_id: CUSTOMER_DECIDE_FIELD_ID,
	alg_wc_eu_valid_vat_but_not_exempted_field_id: NOT_EXEMPTED_FIELD_ID,
	alg_wc_eu_vat_field_position_id: VAT_FIELD_POSITION_ID,
	get_show_in_countries: VAT_FIELD_SHOW_IN_COUNTRIES
} = VAT_SETTINGS;
const SAME_FOR_BILLING_SELECTOR = '.wc-block-checkout__use-address-for-billing input[type="checkbox"]';
const VAT_DETAILS_CONTAINER_ID = 'alg_eu_vat_for_woocommerce_field';

/**
 * DOM Utilities
 *
 * @version 4.6.2
 * @since   4.5.8
 */
const DOMUtils = {
	getVatFieldId: () => VAT_FIELD_ID,
	getCustomerDecideFieldId: () => CUSTOMER_DECIDE_FIELD_ID,
	getNotExemptedFieldId: () => NOT_EXEMPTED_FIELD_ID,
	getVatFieldPositionId: () => VAT_FIELD_POSITION_ID,
	getVatDetailsContainerId: () => VAT_DETAILS_CONTAINER_ID,
	getVatField: () => document.querySelector( DOMUtils.getVatFieldId() ),
	getCustomerDecideField: () => document.querySelector( DOMUtils.getCustomerDecideFieldId() ),
	getNoExemptedField: () => document.querySelector( DOMUtils.getNotExemptedFieldId() ),
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
 * @version 4.6.2
 * @since   4.5.6
 */
const moveVatFieldOnce = () => {

	const vatField = DOMUtils.getVatField();
	if ( 'yes' === vatField.dataset.algVatMoved ) {
		return;
	}
	const notExempted = DOMUtils.getNoExemptedField();
	const customerDecideField = DOMUtils.getCustomerDecideField();

	const positionedTarget = DOMUtils.getVatFieldPositionId() ?
		document.querySelector( DOMUtils.getVatFieldPositionId() )?.closest( 'div' ) :
		null;

	if ( ! positionedTarget ) {
		return;
	}

	const target = positionedTarget;

	if ( ! target ) {
		return;
	}

	const elements = [
		notExempted?.closest( 'div' ),
		customerDecideField?.closest( 'div' ),
		vatField?.closest( 'div' ),

	].filter( Boolean );

	// insert the elements
	elements.forEach( ( el ) => {
		target.insertAdjacentElement( 'afterend', el );
	} );

	vatField.dataset.algVatMoved = 'yes';
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
 * @version 4.6.2
 */
const algWcBlockEuVatValidateVat = async ( vat_number, refresh ) => {

	const same_billing_shipping = DOMUtils.isUseBillingChecked() ? 'yes' : 'no';

	const cartData = select( CART_STORE_KEY ).getCartData();
	const billing_country = cartData.billingAddress.country;
	const shipping_country = cartData.shippingAddress.country;
	const billing_company = cartData.billingAddress.company;

	const vat_customer_decide = DOMUtils.getCustomerDecideField()?.checked ?? false;
	const vat_valid_but_not_exempted = DOMUtils.getNoExemptedField()?.checked ?? false;

	const progress = document.getElementById( 'alg_wc_eu_vat_progress' );
	const eu_vat_field = DOMUtils.getVatField();
	const place_order_button = document.querySelector( '.wc-block-components-checkout-place-order-button' );
	const previous_country = document.getElementById( 'store_previous_country' );
	const vatDetailsDiv = document.getElementById( 'alg_wc_eu_vat_details' );

	if ( ! place_order_button ) {
		return;
	}
	place_order_button.disabled = true;

	if ( eu_vat_field ) {
		eu_vat_field.className = '';
	}
	if ( vatDetailsDiv ) {
		vatDetailsDiv.innerHTML = '';
	}

	if ( progress ) {
		progress.innerHTML = alg_wc_eu_vat_ajax_object.progress_text_validating;
		progress.className = 'alg-wc-eu-vat-validating';
	}

	try {
		const cart = await extensionCartUpdate( {
			namespace: 'alg-wc-eu-vat-extension-namespace',
			data: {
				vat_number,
				vat_customer_decide,
				vat_valid_but_not_exempted,
				billing_country,
				shipping_country,
				same_billing_shipping,
				billing_company,
				block_checkout: true
			},
		} );

		const data = cart?.extensions?.['eu-vat-for-woocommerce-block-example']?.alg_eu_vat_validation;

		if ( ! data ) {
			if ( progress ) {
				progress.innerHTML = '';
			}
			return;
		}

		const isValidation = data.is_validate;
		const cssClasses = data.css_class ? data.css_class.trim().split( /\s+/ ) : [];
		if ( isValidation ) {
			eu_vat_field.classList.add( 'woocommerce-validated' );
			eu_vat_field.closest( 'div' ).classList.remove( 'has-error' );
			cssClasses.push( 'alg-wc-eu-vat-valid', 'alg-wc-eu-vat-valid-color' );
		} else {
			eu_vat_field.classList.add( 'woocommerce-invalid' );
			eu_vat_field.closest( 'div' ).classList.add( 'has-error' );
			cssClasses.push( 'alg-wc-eu-vat-not-valid', 'alg-wc-eu-vat-error-color' );
		}
		if ( progress ) {
			progress.textContent = data.messages;
			progress.className = '';
			progress.className = cssClasses.join( ' ' );
		}

		if ( data.vat_details && vatDetailsDiv ) {
			const ul = document.createElement( 'ul' );
			Object.values( data.vat_details ).forEach( ( {label, data: value} ) => {
				const li = document.createElement( 'li' );
				li.textContent = `${label}: ${value}`;
				ul.appendChild( li );
			} );
			vatDetailsDiv.replaceChildren( ul );
		}

		previous_country.value = billing_country;
	} catch ( error ) {
		if ( progress ) {
			progress.textContent = alg_wc_eu_vat_ajax_object.progress_text_error ?? 'Validation error.';
			progress.className = 'alg-wc-eu-vat-error-color';
		}
	} finally {
		place_order_button.disabled = false;
	}
};
export {algWcBlockEuVatValidateVat};

/**
 * Block.
 *
 * @version 4.6.2
 */
const Block = ( { checkoutExtensionData, extensions } ) => {

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

		moveVatFieldOnce();
		createVatInformationContainer( vatField, billingAddress.country );

		const trigger = alg_wc_eu_vat_ajax_object.action_trigger ?? 'onblur';
		const triggerType = trigger === 'onblur' ? 'blur' : 'input';

		vatField.addEventListener( triggerType, triggerValidation );

		const customerDecideField = DOMUtils.getCustomerDecideField();
		if ( customerDecideField ) {
			customerDecideField.addEventListener( 'input', function (){
				setHideVat( customerDecideField?.checked ?? false );
			} );
		}

		const notExempted = DOMUtils.getNoExemptedField();
		if ( notExempted ) {
			notExempted.addEventListener( 'input', triggerValidation );
		}

		const billingCheckbox = DOMUtils.getUseBillingCheckbox();
		if ( billingCheckbox ) {
			billingCheckbox.addEventListener( 'input', triggerValidation );
		}

		return () => {
			vatField.removeEventListener( triggerType, triggerValidation );
			if ( customerDecideField && triggerValidation ) {
				customerDecideField.removeEventListener( 'input', triggerValidation );
			}
			if ( notExempted && triggerValidation ) {
				notExempted.removeEventListener( 'input', triggerValidation );
			}
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

		const customerDecideField = DOMUtils.getCustomerDecideField()?.checked ?? false

		setHideVat( countryAllowed || companyCheck || customerDecideField );

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
