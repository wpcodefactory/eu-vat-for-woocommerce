/**
 * Always show zero VAT.
 *
 * @version 4.5.6
 * @since   4.2.8
 *
 * @see     https://developer.woocommerce.com/docs/cart-and-checkout-available-slots/#0-experimentalordermeta
 *
 * @todo    (v4.2.8) "VAT $0.00" instead of "VAT 0%"?
 */
import { __ } from '@wordpress/i18n';
import { ExperimentalOrderMeta } from '@woocommerce/blocks-checkout';

export const ZeroVATComponent = ( { cart } ) => {
	if ( '0' === cart.cartTotals.total_tax ) {
		return (
			<div
				className="
				wc-block-components-totals-item
				wc-block-components-totals-footer-item
				alg-eu-vat-for-woocommerce-zero-vat-wrapper
				"
			>
                <span className="wc-block-components-totals-item__label">
                    {__( 'VAT', 'eu-vat-for-woocommerce' )}
                </span>
				<div className="wc-block-components-totals-item__value">
                    <span
	                    className="
	                    wc-block-formatted-money-amount
	                    wc-block-components-formatted-money-amount wc-block-components-totals-footer-item-tax-value
	                    "
                    >
                        0%
                    </span>
				</div>
			</div>
		);
	}

	return null;
};

const ZeroVAT = () => {
	// Check if global object exists and the feature is enabled
	const showZeroVAT =
		typeof alg_wc_eu_vat_ajax_object !== 'undefined' &&
		alg_wc_eu_vat_ajax_object.do_always_show_zero_vat;

	if ( ! showZeroVAT ) {
		return null;
	}

	return (
		<ExperimentalOrderMeta>
			<ZeroVATComponent/>
		</ExperimentalOrderMeta>
	);
};
export default ZeroVAT;