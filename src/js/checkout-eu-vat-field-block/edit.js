/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { CheckboxControl, ValidatedTextInput } from '@woocommerce/blocks-checkout';
import { getSetting } from '@woocommerce/settings';
/**
 * Internal dependencies
 */
import './style.scss';
const { optInDefaultText } = getSetting('eu-vat-for-woocommerce_data', '');

export const Edit = ({ attributes, setAttributes }) => {
	const { text } = attributes;
	const blockProps = useBlockProps();
	return (
		<div {...blockProps}>
			
			<InspectorControls>
				<PanelBody title={__('Block options', 'eu-vat-for-woocommerce')}>
					Options for the block go here.
				</PanelBody>
			</InspectorControls>
			
			<div {...blockProps}> 
				<ValidatedTextInput
						id="billing_eu_vat_number"
						type="text"
						required={false}
						className={'billing-eu-vat-number'}
						label={
							__( 'EU VAT Number', 'eu-vat-for-woocommerce' )
						}
			
				/>
				<div id="alg_wc_eu_vat_progress"></div>
				
			</div>
			
			
		</div>
	);
};

export const Save = ({ attributes }) => {
	const { text } = attributes;
	return (
		<div {...useBlockProps.save()}>
			<RichText.Content value={text} />
		</div>
	);
};
