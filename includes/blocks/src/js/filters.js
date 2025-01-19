/**
 * External dependencies
 */
import { registerCheckoutFilters  } from '@woocommerce/blocks-checkout';
import { registerPaymentMethodExtensionCallbacks } from '@woocommerce/blocks-registry';
import { __, sprintf } from '@wordpress/i18n';
import { algWcBlockEuVatValidateVat } from './checkout-eu-vat-field-block/block';

export const registerFilters = (pointsLabelPlural, discountRegex) => {
	registerCheckoutFilters('eu-vat-for-woocommerce', {
		itemName: ( name, extensions, args ) => {
			
			// const euvat_val = document.getElementById('billing_eu_vat_number').value;
			
			// if( euvat_val !== '' ) {
				// algWcBlockEuVatValidateVat( euvat_val, false );
			// }
			// return `${name} + extra data!`;
			return name;
		},
	});
	
	/*
	registerPaymentMethodExtensionCallbacks('eu-vat-for-woocommerce', {
		cod: (arg) => { console.log(arg); return arg.billingData.city !== 'Denver' },
	});
	*/
};
