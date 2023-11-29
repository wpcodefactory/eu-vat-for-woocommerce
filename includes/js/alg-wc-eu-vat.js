/**
 * alg-wc-eu-vat.js
 *
 * @version 2.9.16
 * @since   1.0.0
 * @author  WPFactory
 * @todo    [dev] replace `billing_eu_vat_number` and `billing_eu_vat_number_field` with `alg_wc_eu_vat_get_field_id()`
 * @todo    [dev] customizable event for `billing_company` (currently `input`; could be e.g. `change`)
 */

jQuery( function( $ ) {

	// Setup before functions
	var input_timer;                                                      // timer identifier
	var input_timer_company_require;                                      // timer identifier (company require)
	var input_timer_company_load;                                      // timer identifier (company require)
	var input_timer_company;                                              // timer identifier (company)
	var done_input_interval = 1000;                                       // time in ms
	var vat_input           = $( 'input[name="billing_eu_vat_number"]' );
	var billing_company           = $( 'input[name="billing_company"]' );
	var vat_input_customer_choice    = $( 'input[name="billing_eu_vat_number_customer_decide"]' );
	
	var vat_input_belgium_compatibility    = $( 'input[name="billing_eu_vat_number_belgium_compatibility"]' );
	var vat_input_billing_country    = $( 'select[name="billing_country"]' );
	var var_belgium_compatibility = 'no';
	var vat_paragraph       = $( 'p[id="billing_eu_vat_number_field"]' );
	var vat_input_label = $('label[for="billing_eu_vat_number"]');

	// Add progress text
	if ( 'yes' == alg_wc_eu_vat_ajax_object.add_progress_text ) {
		vat_paragraph.append( '<div id="alg_wc_eu_vat_progress"></div>' );
		var progress_text = $( 'div[id="alg_wc_eu_vat_progress"]' );
	}
	
	if ( 'yes_for_company' == alg_wc_eu_vat_ajax_object.is_required ) {
		billing_company.blur(function(){
		  // vat_input_billing_country.change();
		  is_company_name_not_empty();
		  // $( 'body' ).trigger( 'update_checkout' );
		});
		
		billing_company.on( 'input', function() {
			clearTimeout( input_timer_company_require );
			input_timer_company_require = setTimeout( alg_wc_eu_vat_require_on_company_fill, done_input_interval );
		});
		
		clearTimeout( input_timer_company_load );
		input_timer_company_load = setTimeout( alg_wc_eu_vat_require_on_company_fill, done_input_interval );
	}

	// Initial validate
	alg_wc_eu_vat_validate_vat(true);

	if ( 'onblur' == alg_wc_eu_vat_ajax_object.action_trigger ) {
		// On blur, start the countdown
		vat_input.on( 'blur', function() {
			clearTimeout( input_timer );
			input_timer = setTimeout( alg_wc_eu_vat_validate_vat, done_input_interval );
		} );
	} else {
		// On input, start the countdown
		vat_input.on( 'input', function() {
			clearTimeout( input_timer );
			input_timer = setTimeout( alg_wc_eu_vat_validate_vat, done_input_interval );
		} );
	}
	

	// On country change - re-validate
	$( '#billing_country' ).on( 'change', alg_wc_eu_vat_validate_vat );
	$( '#shipping_country' ).on( 'change', alg_wc_eu_vat_validate_vat );
	$( '#ship-to-different-address' ).on( 'click', alg_wc_eu_vat_validate_vat );

	// Company name - re-validate
	if ( alg_wc_eu_vat_ajax_object.do_check_company_name ) {
		$( '#billing_company' ).on( 'input', function() {
			clearTimeout( input_timer_company );
			input_timer_company = setTimeout( alg_wc_eu_vat_validate_vat, done_input_interval );
		} );
	}
	
	vat_input_customer_choice.change(function() {
		alg_wc_eu_vat_validate_vat();
	});
	
	vat_input_belgium_compatibility.change(function() {
		alg_wc_eu_vat_validate_vat();
	});
	
	function alg_wc_eu_vat_require_on_company_fill() {
		// vat_input_billing_country.change();
		is_company_name_not_empty();
		// $( 'body' ).trigger( 'update_checkout' );
	}
	
	function is_company_name_not_empty(){
		if('' != billing_company.val()){
			vat_paragraph.removeClass( 'woocommerce-invalid' );
			vat_paragraph.removeClass( 'woocommerce-validated' );
			vat_paragraph.addClass( 'validate-required' );
			vat_input.addClass('field-required');
			vat_input_label.find("span.optional").remove();
			vat_input_label.find("abbr").remove();
			vat_input_label.append('<abbr class="required" title="required">*</abbr>');
			// vat_paragraph.show();
		}else{
			vat_paragraph.removeClass( 'woocommerce-invalid' );
			vat_paragraph.removeClass( 'woocommerce-validated' );
			vat_paragraph.removeClass( 'validate-required' );
			vat_input.removeClass('field-required');
			vat_input_label.find("abbr").hide();
			vat_input_label.find("span.optional").remove();
			vat_input_label.append('<span class="optional">(optional)</span>');
			// vat_paragraph.hide();
		}
	}
	/**
	 * alg_wc_eu_vat_validate_vat
	 *
	 * @version 2.9.10
	 * @since   1.0.0
	 */
	function alg_wc_eu_vat_validate_vat( load = false ) {
		
		if ( 'yes' == alg_wc_eu_vat_ajax_object.add_progress_text ) {
			if( 'yes' == alg_wc_eu_vat_ajax_object.hide_message_on_preserved_countries ){
				if(alg_wc_eu_vat_ajax_object.preserve_countries.length > 0){
					if(jQuery.inArray( vat_input_billing_country.val(), alg_wc_eu_vat_ajax_object.preserve_countries ) >= 0){
						progress_text.hide();
					}else{
						progress_text.show();
					}
				}
			}
		}
		if(vat_input_customer_choice.length > 0){
			if (vat_input_customer_choice.is(':checked')) {
				vat_paragraph.removeClass( 'woocommerce-invalid' );
				vat_paragraph.removeClass( 'woocommerce-validated' );
				vat_paragraph.removeClass( 'validate-required' );
				vat_input.removeClass('field-required');
				vat_input_label.find("abbr").hide();
				vat_input_label.find("span.optional").remove();
				vat_input_label.append('<span class="optional">(optional)</span>');
				vat_paragraph.hide();
				return;
			}else{
				vat_paragraph.removeClass( 'woocommerce-invalid' );
				vat_paragraph.removeClass( 'woocommerce-validated' );
				vat_paragraph.addClass( 'validate-required' );
				vat_input.addClass('field-required');
				vat_input_label.find("span.optional").remove();
				vat_input_label.find("abbr").show();
				vat_paragraph.show();
			}
		}
		
		if(vat_input_belgium_compatibility.length > 0){
			if (vat_input_belgium_compatibility.is(':checked')) {
				var_belgium_compatibility = 'yes';
			}else{
				var_belgium_compatibility = 'no';
			}
		}
		
		vat_paragraph.removeClass( 'woocommerce-invalid' );
		vat_paragraph.removeClass( 'woocommerce-validated' );
		vat_paragraph.removeClass( 'woocommerce-invalid-mismatch' );
		
		var vat_number_to_check = vat_input.val();
		
		if(load && vat_number_to_check === ''){
			vat_number_to_check = undefined;
		}
		if ( undefined != vat_number_to_check ) {
			// Validating EU VAT Number through AJAX call
			if ( 'yes' == alg_wc_eu_vat_ajax_object.add_progress_text ) {
				progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_validating );
				progress_text.removeClass();
				progress_text.addClass( 'alg-wc-eu-vat-validating' );
			}
			var data = {
				'action': 'alg_wc_eu_vat_validate_action',
				'alg_wc_eu_vat_to_check': vat_number_to_check,
				'alg_wc_eu_vat_belgium_compatibility': var_belgium_compatibility,
				'billing_country': $('#billing_country').val(),
				'shipping_country': $('#shipping_country').val(),
				'billing_company': $('#billing_company').val(),
			};
			$.ajax( {
				type: "POST",
				url: alg_wc_eu_vat_ajax_object.ajax_url,
				data: data,
				success: function( response ) {
					response = response.replace("</pre>", "");
					response = response.trim();
					var splt = response.split("|");
					response = splt[0];
					
					if ( '1' == response ) {
						vat_paragraph.addClass( 'woocommerce-validated' );
						if ( 'yes' == alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_valid );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-valid' );
						}
					} else if ( '0' == response ) {
						vat_paragraph.addClass( 'woocommerce-invalid' );
						if ( 'yes' == alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_not_valid );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-not-valid' );
						}
					} else if ( '4' == response ) {
						vat_paragraph.addClass( 'woocommerce-invalid' );
						if ( 'yes' == alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.text_shipping_billing_countries );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-not-valid-billing-country' );
						}
					} else if ( '5' == response ) {
						var com = splt[1];
						vat_paragraph.addClass( 'woocommerce-invalid' );
						vat_paragraph.addClass( 'woocommerce-invalid-mismatch' );
						if ( 'yes' == alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.company_name_mismatch.replace("%company_name%", com) );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-not-valid-company-mismatch' );
						}
					} else if ( '6' == response ) {
						vat_paragraph.removeClass( 'woocommerce-invalid' );
						vat_paragraph.removeClass( 'woocommerce-validated' );
						if ( 'yes' == alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_validation_failed );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-validation-failed' );
						}
					} else {
						vat_paragraph.addClass( 'woocommerce-invalid' );
						if ( 'yes' == alg_wc_eu_vat_ajax_object.add_progress_text ) {
							progress_text.text( alg_wc_eu_vat_ajax_object.progress_text_validation_failed );
							progress_text.removeClass();
							progress_text.addClass( 'alg-wc-eu-vat-validation-failed' );
						}
					}
					$( 'body' ).trigger( 'update_checkout' );
				},
			} );
		} else {
			// VAT input is empty
			if ( 'yes' == alg_wc_eu_vat_ajax_object.add_progress_text ) {
				progress_text.text( '' );
			}
			if ( vat_paragraph.hasClass( 'validate-required' ) ) {
				// Required
				vat_paragraph.addClass( 'woocommerce-invalid' );
			} else {
				// Not required
				vat_paragraph.addClass( 'woocommerce-validated' );
			}
			$( 'body' ).trigger( 'update_checkout' );
		}
	};
} );
