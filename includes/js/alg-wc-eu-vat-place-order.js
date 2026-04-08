/**
 * alg-wc-eu-vat-place-order.js
 *
 * @version 4.6.0
 * @since   1.4.1
 *
 * @author  WPFactory
 *
 * @todo    (dev) also `return false;` when not confirmed?
 */
jQuery( function ( $ ) {
	'use strict';

	const VAT_FIELD = place_order_data.vatField;
	const BUTTON_SELECTOR = place_order_data.buttonSelector;
	const BUTTON_WRAPPER_CLASS = 'alg-wc-eu-vat-checkout-button-wrapper';
	const BUTTON_WRAPPER_SELECTOR = '.' + BUTTON_WRAPPER_CLASS;
	const OVERLAY_CLASS = 'alg-wc-eu-vat-checkout-button-overlay';
	const OVERLAY_SELECTOR = '.' + OVERLAY_CLASS;

	const $checkoutForm = $( 'form.checkout' );
	const $vatField = $( `#${VAT_FIELD}` );
	let yn_status = false;

	const algVatConfirmModal = {

		init() {
			this.handleCheckoutPlaceOrderClick();

			if ( BUTTON_SELECTOR !== '' ) {
				this.addOverlays();
				this.handleOverlayClicks();

				$( document.body ).on( 'updated_checkout', () => {
					this.addOverlays();
				} );
			}
		},

		handleCheckoutPlaceOrderClick() {
			$checkoutForm.on( 'checkout_place_order', function () {

				const needsVat = $vatField.is( ':visible' ) && $vatField.val() === '';

				if ( ! needsVat ) {
					return;
				}

				confirmo.init( {
					yesBg: place_order_data.yesBg,
					noBg: place_order_data.noBg,
					leftText: place_order_data.yes_text,
					rightText: place_order_data.no_text,
				} );

				confirmo.show( {
					msg: place_order_data.confirmation_text,
					callback_yes: function () {
						yn_status = true;
						$( '#place_order' ).click();
					},
					callback_no: function () {
						yn_status = false;
					}
				} );

				return yn_status;
			} );
		},

		addOverlays() {
			$( BUTTON_SELECTOR ).each( function () {

				const $button = $( this );

				if ( ! $button.length ) {
					return;
				}

				if ( ! $button.parent( BUTTON_WRAPPER_SELECTOR ).length ) {
					$button.wrap( `<div class="${BUTTON_WRAPPER_CLASS}"></div>` );
				}

				const $wrapper = $button.parent( BUTTON_WRAPPER_SELECTOR ).css( 'position', 'relative' );

				if ( $wrapper.find( OVERLAY_SELECTOR ).length ) {
					return;
				}

				$wrapper.append(
					$( '<span>' ).addClass( OVERLAY_CLASS ).css( {
						position: 'absolute',
						top: 0,
						left: 0,
						width: '100%',
						height: '100%',
						zIndex: 1999,
						cursor: 'pointer',
						background: 'transparent',
					} )
				);
			} );
		},

		handleOverlayClicks() {
			$( document.body ).on( 'click', OVERLAY_SELECTOR, function ( e ) {
				e.preventDefault();
				e.stopPropagation();

				const $overlay = $( this );
				const needsVat = $vatField.is( ':visible' ) && $vatField.val() === '';

				if ( ! needsVat ) {
					$overlay.remove();
					return;
				}

				confirmo.init( {
					yesBg: place_order_data.yesBg,
					noBg: place_order_data.noBg,
					leftText: place_order_data.yes_text,
					rightText: place_order_data.no_text,
				} );

				confirmo.show( {
					msg: place_order_data.confirmation_text,
					callback_yes: function () {
						yn_status = true;
						$overlay.remove();
					},
					callback_no: function () {
						yn_status = false;
					}
				} );
				return yn_status;
			} );
		},
	};

	$( document ).ready( function () {
		algVatConfirmModal.init()
	} );
} );