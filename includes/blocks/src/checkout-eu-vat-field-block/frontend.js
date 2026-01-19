/**
 * Load scripts for the WooCommerce block-based checkout.
 *
 * @version 4.5.6
 * @since   4.5.6
 */

/**
 * External dependencies
 */
import { registerCheckoutBlock } from '@woocommerce/blocks-checkout';
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import Block from './blockvat';
import ZeroVAT from './zero-vat';
import metadata from './block.json';

registerCheckoutBlock( {
	metadata,
	component: Block,
} );

registerPlugin( 'alg-wc-eu-vat-show-zero-vat', {
	render: ZeroVAT,
	scope: 'woocommerce-checkout',
} );