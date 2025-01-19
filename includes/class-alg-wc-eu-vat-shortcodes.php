<?php
/**
 * Product Quantity for WooCommerce - Shortcodes Class
 *
 * @version 4.0.0
 * @since   1.6.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT_Shortcodes' ) ) :

class Alg_WC_EU_VAT_Shortcodes {

	/**
	 * Constructor.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function __construct() {
		add_shortcode( 'alg_wc_eu_vat_translate', array( $this, 'language_shortcode' ) );
	}

	/**
	 * language_shortcode.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @todo    (dev) `pll_current_language()`?
	 */
	function language_shortcode( $atts, $content = '' ) {

		// E.g.: `[alg_wc_eu_vat_translate lang="DE,NL" lang_text="EU-Steuernummer" not_lang_text="EU VAT Number"]`
		if ( isset( $atts['lang_text'] ) && isset( $atts['not_lang_text'] ) && ! empty( $atts['lang'] ) ) {
			return ( ! defined( 'ICL_LANGUAGE_CODE' ) || ! in_array( strtolower( ICL_LANGUAGE_CODE ), array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) ) ) ) ?
				$atts['not_lang_text'] : $atts['lang_text'];
		}

		// E.g.: `[alg_wc_eu_vat_translate lang="DE"]EU-Steuernummer[/alg_wc_eu_vat_translate][alg_wc_eu_vat_translate lang="NL"]BTW nummer van de EU[/alg_wc_eu_vat_translate][alg_wc_eu_vat_translate not_lang="DE,NL"]EU VAT Number[/alg_wc_eu_vat_translate]`
		return (
			( ! empty( $atts['lang'] )     && ( ! defined( 'ICL_LANGUAGE_CODE' ) || ! in_array( strtolower( ICL_LANGUAGE_CODE ), array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) ) ) ) ) ||
			( ! empty( $atts['not_lang'] ) &&     defined( 'ICL_LANGUAGE_CODE' ) &&   in_array( strtolower( ICL_LANGUAGE_CODE ), array_map( 'trim', explode( ',', strtolower( $atts['not_lang'] ) ) ) ) )
		) ? '' : $content;

	}

}

endif;

return new Alg_WC_EU_VAT_Shortcodes();
