/**
 * alg-wc-eu-vat.js
 *
 * @version 4.4.6
 * @since   1.0.0
 *
 * @author  WPFactory
 *
 * @todo    (dev) replace `billing_eu_vat_number` and `billing_eu_vat_number_field` with `alg_wc_eu_vat_get_field_id()`
 * @todo    (dev) customizable event for `billing_company` (currently `input`; could be e.g., `change`)
 */

jQuery( function ( $ ) {

	// Setup before functions
	var input_timer;                 // timer identifier
	var input_timer_company_require; // timer identifier (company require)
	var input_timer_company_load;    // timer identifier (company require)
	var input_timer_company;         // timer identifier (company)
	var done_input_interval = 1000;  // time in ms

	var valid_vat_but_not_exempted = 'no';

	// Elements
	var vat_input;
	var billing_company;
	var vat_input_customer_choice;
	var valid_vat_but_not_exempted_input;
	var vat_input_billing_country;
	var vat_paragraph;
	var vat_input_label;

	// Init elements
	init_elements();

	// Add progress text
	if ( 'yes' === alg_wc_eu_vat_ajax_object.add_progress_text ) {
		vat_paragraph.append( '<div id="alg_wc_eu_vat_progress"></div>' );
		var progress_text = $( 'div[id="alg_wc_eu_vat_progress"]' );
	}

	// Display VAT details
	if ( 'yes' === alg_wc_eu_vat_ajax_object.show_vat_details ) {
		vat_paragraph.append( '<div id="alg_wc_eu_vat_details"></div>' );
	}

	if ( 'yes_for_company' === alg_wc_eu_vat_ajax_object.is_required ) {
		billing_company.blur( function () {
			is_company_name_not_empty();
		} );

		billing_company.on( 'input', function () {
			clearTimeout( input_timer_company_require );
			input_timer_company_require = setTimeout( alg_wc_eu_vat_require_on_company_fill, done_input_interval );
		} );

		clearTimeout( input_timer_company_load );
		input_timer_company_load = setTimeout( alg_wc_eu_vat_require_on_company_fill, done_input_interval );
	}

	// Show/hide by billing company
	if ( alg_wc_eu_vat_ajax_object.do_show_hide_by_billing_company ) {
		show_hide_by_billing_company();
		jQuery( '#billing_company' ).on( 'change keyup paste', show_hide_by_billing_company );
	}

	// Initial validate
	alg_wc_eu_vat_validate_vat( true );

	// Attach event handlers
	attach_event_handlers();

	// "Fluid Checkout for WooCommerce" plugin compatibility
	if ( alg_wc_eu_vat_ajax_object.do_compatibility_fluid_checkout ) {
		compatibility_fluid_checkout();
	}

	/**
	 * show_hide_by_billing_company.
	 *
	 * @version  4.4.6
	 * @since    4.4.6
	 */
	function show_hide_by_billing_company() {

		if ( '' === jQuery( '#billing_company' ).val() ) {

			jQuery( '#billing_eu_vat_number_field' ).hide();
			jQuery( '#billing_eu_vat_number' ).val( '' );
			alg_wc_eu_vat_validate_vat();

		} else {

			jQuery( '#billing_eu_vat_number_field' ).show();

		}

	}

	/**
	 * compatibility_fluid_checkout.
	 *
	 * @version  4.2.3
	 * @since    4.2.3
	 */
	function compatibility_fluid_checkout() {

		// Vars
		var saved_progress_text;
		var saved_progress_class;
		var saved_vat_paragraph_class;

		/**
		 * fc_checkout_fragments_replace_before.
		 */
		$( document.body ).on( 'fc_checkout_fragments_replace_before', function () {
			saved_progress_text       = progress_text.text();
			saved_progress_class      = progress_text.attr( 'class' );
			saved_vat_paragraph_class = vat_paragraph.attr( 'class' );
		} );

		/**
		 * fc_checkout_fragments_replace_after.
		 */
		$( document.body ).on( 'fc_checkout_fragments_replace_after', function () {

			// Re-init elements
			init_elements();

			// Re-set class
			vat_paragraph.addClass( saved_vat_paragraph_class );

			// Re-add progress div
			if (
				'yes' === alg_wc_eu_vat_ajax_object.add_progress_text &&
				0 == $( '#alg_wc_eu_vat_progress' ).length
			) {
				vat_paragraph.append( '<div id="alg_wc_eu_vat_progress"></div>' );
				progress_text = $( 'div[id="alg_wc_eu_vat_progress"]' );
				// Re-set text and class
				progress_text.text( saved_progress_text );
				progress_text.addClass( saved_progress_class );
			}

			// Re-attach event handlers
			attach_event_handlers();

		} );

	}

	/**
	 * init_elements.
	 *
	 * @version  4.4.0
	 * @since    4.2.3
	 */
	function init_elements() {
		vat_input                        = $( 'input[name="billing_eu_vat_number"]' );
		billing_company                  = $( 'input[name="billing_company"]' );
		vat_input_customer_choice        = $( 'input[name="billing_eu_vat_number_customer_decide"]' );
		valid_vat_but_not_exempted_input = $( 'input[name="billing_eu_vat_number_valid_vat_but_not_exempted"]' );
		vat_input_billing_country        = $( 'select[name="billing_country"]' );
		vat_paragraph                    = $( 'p[id="billing_eu_vat_number_field"]' );
		vat_input_label                  = $( 'label[for="billing_eu_vat_number"]' );
	}

	/**
	 * attach_event_handlers.
	 *
	 * @version  4.4.0
	 * @since    4.2.3
	 */
	function attach_event_handlers() {

		// On blur/input, start the countdown
		vat_input.on(
			(
				'onblur' === alg_wc_eu_vat_ajax_object.action_trigger ?
				'blur' :
				'input'
			),
			function () {
				clearTimeout( input_timer );
				input_timer = setTimeout(
					alg_wc_eu_vat_validate_vat,
					done_input_interval
				);
			}
		);

		// On country change - re-validate
		$( '#billing_country' ).on( 'change', alg_wc_eu_vat_validate_vat );
		$( '#shipping_country' ).on( 'change', alg_wc_eu_vat_validate_vat );
		$( '#ship-to-different-address' ).on( 'click', alg_wc_eu_vat_validate_vat );

		// Company name - re-validate
		if ( alg_wc_eu_vat_ajax_object.do_check_company_name ) {
			$( '#billing_company' ).on( 'input', function () {
				clearTimeout( input_timer_company );
				input_timer_company = setTimeout(
					alg_wc_eu_vat_validate_vat,
					done_input_interval
				);
			} );
		}

		// Customer choice
		vat_input_customer_choice.change( function () {
			alg_wc_eu_vat_validate_vat();
		} );

		// Valid VAT but not exempted
		valid_vat_but_not_exempted_input.change( function () {
			alg_wc_eu_vat_validate_vat();
		} );

	}

	/**
	 * alg_wc_eu_vat_require_on_company_fill.
	 *
	 * @todo    (dev) remove this (and use `is_company_name_not_empty()` directly)?
	 */
	function alg_wc_eu_vat_require_on_company_fill() {
		is_company_name_not_empty();
	}

	/**
	 * is_company_name_not_empty.
	 */
	function is_company_name_not_empty() {
		if ( '' != billing_company.val() ) {
			vat_paragraph.removeClass( 'woocommerce-invalid' );
			vat_paragraph.removeClass( 'woocommerce-validated' );
			vat_paragraph.addClass( 'validate-required' );
			vat_input.addClass( 'field-required' );
			vat_input_label.find( "span.optional" ).remove();
			vat_input_label.find( "abbr" ).remove();
			vat_input_label.append( '<abbr class="required" title="required">*</abbr>' );
		} else {
			vat_paragraph.removeClass( 'woocommerce-invalid' );
			vat_paragraph.removeClass( 'woocommerce-validated' );
			vat_paragraph.removeClass( 'validate-required' );
			vat_input.removeClass( 'field-required' );
			vat_input_label.find( "abbr" ).hide();
			vat_input_label.find( "span.optional" ).remove();
			vat_input_label.append( '<span class="optional">' + alg_wc_eu_vat_ajax_object.optional_text + '</span>' );
		}
	}

	/**
	 * alg_wc_eu_vat_validate_vat.
	 *
	 * @version 4.4.0
	 * @since   1.0.0
	 */
	function alg_wc_eu_vat_validate_vat( load = false ) {

		if (
			load &&
			alg_wc_eu_vat_ajax_object.do_compatibility_fluid_checkout
		) {
			load = false;
		}

		if ( 'billing_country' === $( this ).attr( 'name' ) ) {
			vat_input.trigger( 'input' );
		}

		if (
			'yes' === alg_wc_eu_vat_ajax_object.add_progress_text &&
			'yes' === alg_wc_eu_vat_ajax_object.hide_message_on_preserved_countries &&
			alg_wc_eu_vat_ajax_object.preserve_countries.length > 0
		) {
			if ( jQuery.inArray( vat_input_billing_country.val(), alg_wc_eu_vat_ajax_object.preserve_countries ) >= 0 ) {
				progress_text.hide();
			} else {
				progress_text.show();
			}
		}

		if ( vat_input_customer_choice.length > 0 ) {
			if ( vat_input_customer_choice.is( ':checked' ) ) {
				vat_paragraph.removeClass( 'woocommerce-invalid' );
				vat_paragraph.removeClass( 'woocommerce-validated' );
				vat_paragraph.removeClass( 'validate-required' );
				vat_input.removeClass( 'field-required' );
				vat_input_label.find( "abbr" ).hide();
				vat_input_label.find( "span.optional" ).remove();
				vat_input_label.append( '<span class="optional">' + alg_wc_eu_vat_ajax_object.optional_text + '</span>' );
				vat_paragraph.hide();
				return;
			} else {
				vat_paragraph.removeClass( 'woocommerce-invalid' );
				vat_paragraph.removeClass( 'woocommerce-validated' );
				vat_paragraph.addClass( 'validate-required' );
				vat_input.addClass( 'field-required' );
				vat_input_label.find( "span.optional" ).remove();
				vat_input_label.find( "abbr" ).show();
				vat_paragraph.show();
			}
		}

		if ( valid_vat_but_not_exempted_input.length > 0 ) {
			if ( valid_vat_but_not_exempted_input.is( ':checked' ) ) {
				valid_vat_but_not_exempted = 'yes';
			} else {
				valid_vat_but_not_exempted = 'no';
			}
		}

		vat_paragraph.removeClass( 'woocommerce-invalid' );
		vat_paragraph.removeClass( 'woocommerce-validated' );
		vat_paragraph.removeClass( 'woocommerce-invalid-mismatch' );

		var vat_number_to_check = vat_input.val();

		if ( load && vat_number_to_check === '' ) {
			vat_number_to_check = undefined;
		}

		if ( undefined != vat_number_to_check ) {
			// Validating EU VAT Number through AJAX call
			if ( 'yes' == alg_wc_eu_vat_ajax_object.add_progress_text ) {
				progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_validating );
				progress_text.removeClass();
				progress_text.addClass( 'alg-wc-eu-vat-validating' );
			}

			const vatDetailsDiv = document.getElementById( 'alg_wc_eu_vat_details' );
			if ( vatDetailsDiv ) {
				vatDetailsDiv.innerHTML = '';
			}

			var data = {
				'action'                                  : 'alg_wc_eu_vat_validate_action',
				'alg_wc_eu_vat_to_check'                  : vat_number_to_check,
				'alg_wc_eu_vat_valid_vat_but_not_exempted': valid_vat_but_not_exempted,
				'billing_country'                         : $( '#billing_country' ).val(),
				'shipping_country'                        : $( '#shipping_country' ).val(),
				'billing_company'                         : $( '#billing_company' ).val(),
			};
			$.ajax( {
				type: "POST",
				url: alg_wc_eu_vat_ajax_object.ajax_url,
				data: data,
				success: function ( resp ) {
					var response     = resp.status;
					var err          = resp.error;
					var company_name = resp.company;

					response = response.replace( "</pre>", "" );
					response = response.trim();
					var splt = response.split( "|" );
					response = splt[0];

					if ( alg_wc_eu_vat_ajax_object.status_codes['VAT_VALID'] === response ) {
						vat_paragraph.addClass( 'woocommerce-validated' );
						if ( 'yes' === alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_valid );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-valid alg-wc-eu-vat-valid-color' );
						}
					} else if ( alg_wc_eu_vat_ajax_object.status_codes['VAT_NOT_VALID'] === response ) {
						vat_paragraph.addClass( 'woocommerce-invalid' );
						if ( 'yes' === alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_not_valid );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-not-valid alg-wc-eu-vat-error-color' );
						}
					} else if ( alg_wc_eu_vat_ajax_object.status_codes['WRONG_BILLING_COUNTRY'] === response ) {
						vat_paragraph.addClass( 'woocommerce-invalid' );
						if ( 'yes' === alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_wrong_billing_country );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-not-valid-billing-country alg-wc-eu-vat-error-color' );
						}
					} else if ( alg_wc_eu_vat_ajax_object.status_codes['KEEP_VAT_SHIPPING_COUNTRY'] === response ) {
						vat_paragraph.addClass( 'woocommerce-invalid' );
						if ( 'yes' === alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.text_shipping_billing_countries );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-not-valid-billing-country alg-wc-eu-vat-error-color' );
						}
					} else if ( alg_wc_eu_vat_ajax_object.status_codes['COMPANY_NAME'] === response ) {
						var com = splt[1];
						vat_paragraph.addClass( 'woocommerce-invalid' );
						vat_paragraph.addClass( 'woocommerce-invalid-mismatch' );
						if ( 'yes' === alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.company_name_mismatch.replace( "%company_name%", com ) );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-not-valid-company-mismatch alg-wc-eu-vat-error-color' );
						}
					} else if ( alg_wc_eu_vat_ajax_object.status_codes['EMPTY_VAT'] === response ) {
						if ( vat_paragraph.hasClass( 'validate-required' ) ) {
							vat_paragraph.removeClass( 'woocommerce-validated' );
							vat_paragraph.addClass( 'woocommerce-invalid' );
							if ( 'yes' === alg_wc_eu_vat_ajax_object.add_progress_text ) {
								progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_is_required );
								progress_text.removeClass();
								progress_text.addClass( 'alg-wc-eu-vat-validation-failed alg-wc-eu-vat-error-color' );
							}
						} else {
							vat_paragraph.removeClass( 'woocommerce-invalid woocommerce-validated' );
							if ( 'yes' === alg_wc_eu_vat_ajax_object.add_progress_text ) {
								progress_text.removeClass().text( '' );
							}
						}
					} else if ( alg_wc_eu_vat_ajax_object.status_codes['KEEP_VAT_COUNTRIES'] === response ) {
						vat_paragraph.removeClass( 'woocommerce-invalid' );
						vat_paragraph.removeClass( 'woocommerce-validated' );
						if ( 'yes' === alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_validation_preserv );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-validation-failed alg-wc-eu-vat-error-color' );
						}
					} else if ( alg_wc_eu_vat_ajax_object.status_codes['VIES_UNAVAILABLE'] === response ) {
						vat_paragraph.removeClass( 'woocommerce-invalid' );
						vat_paragraph.removeClass( 'woocommerce-validated' );
						if ( 'yes' === alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.vies_not_available.replace( "%vies_error%", err ) );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-validation-failed alg-wc-eu-vat-error-color' );
						}
					} else {
						vat_paragraph.addClass( 'woocommerce-invalid' );
						if ( 'yes' === alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_validation_failed );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-validation-failed alg-wc-eu-vat-error-color' );
						}
					}

					if (
						'yes' === alg_wc_eu_vat_ajax_object.autofill_company_name &&
						'' !== company_name
					) {
						$( '#billing_company' ).val( company_name ).change();
						vat_paragraph.removeClass( 'woocommerce-invalid' );
						vat_paragraph.removeClass( 'woocommerce-invalid-mismatch' );
						vat_paragraph.addClass( 'woocommerce-validated' );
						progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_valid );
					}

					if ( resp.vat_details && vatDetailsDiv ) {
						let vat_details = resp.vat_details;
						let ulElement = document.createElement( 'ul' );
						for ( let key in vat_details ) {
							if ( vat_details.hasOwnProperty( key ) ) {
								let liElement = document.createElement( 'li' );
								liElement.textContent = `${vat_details[key].label}: ${vat_details[key].data}`;
								ulElement.appendChild( liElement );
							}
						}
						vatDetailsDiv?.replaceChildren( ulElement );
					}

					var refresh_checkout = function () {
						$( 'body' ).trigger( 'update_checkout' );
					};

					setTimeout( refresh_checkout, 800 );

				},
			} );
		} else {
			// VAT input is empty
			if ( 'yes' === alg_wc_eu_vat_ajax_object.add_progress_text ) {
				progress_text.text( '' );
			}
			if ( vat_paragraph.hasClass( 'validate-required' ) ) {
				// Required
				vat_paragraph.addClass( 'woocommerce-invalid' );
			}

			var refresh_checkout_end = function () {
				$( 'body' ).trigger( 'update_checkout' );
			};
			setTimeout( refresh_checkout_end, 800 );
		}

	}

} );

/**
 * For VAT Blocks.
 *
 * Move VAT validation process message section into `wc-block-components-address-form__alg_eu_vat-billing_eu_vat_number`.
 *
 * @version 4.0.0
 * @since   3.0.1
 */
// Wait for all resources to load
window.onload = () => {
	const sourceDiv = document.getElementById( 'alg_eu_vat_for_woocommerce_field' );
	const targetDiv = document.querySelector( '.wc-block-components-address-form__alg_eu_vat-billing_eu_vat_number' );

	// Check if both elements exist
	if ( sourceDiv && targetDiv ) {
		// Set margin-top style
		sourceDiv.style.marginTop = '16px';

		// Move the sourceDiv into the targetDiv
		targetDiv.appendChild( sourceDiv ); // Append sourceDiv as a child of targetDiv
	}
};
