<?php
/**
 * Product Quantity for WooCommerce - Shortcodes Class
 *
 * @version 1.8.0
 * @since   1.6.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_EU_VAT_Shortcodes' ) ) :

class Alg_WC_EU_VAT_Shortcodes {

	/**
	 * Constructor.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function __construct() {
		add_shortcode( 'alg_wc_eu_vat_translate', 		 array( $this, 'language_shortcode' ) );
	}

	
	/**
	 * language_shortcode.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function language_shortcode( $atts, $content = '' ) {
		
		$current_language = '';
		if (function_exists('pll_current_language')) {
			$current_language = strtolower( pll_current_language() );
		}
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$current_language = strtolower( ICL_LANGUAGE_CODE );
		}
		
		// E.g.: [alg_wc_eu_vat_translate lang="EN,DE" lang_text="Text for EN & DE" not_lang_text="Text for other languages"]
        if ( isset( $atts['lang_text'] ) && isset( $atts['not_lang_text'] ) && ! empty( $atts['lang'] ) ) {
            return ( empty( $current_language ) || ! in_array( $current_language, array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) ) ) ) ?
                $atts['not_lang_text'] : $atts['lang_text'];
        }
		
		// [alg_wc_eu_vat_translate lang="DE"]Die zulässige Menge.[/alg_wc_eu_vat_translate][alg_wc_eu_vat_translate lang="NL"]Toegestane hoeveelheid.[/alg_wc_eu_vat_translate]
		if ( ! empty( $atts['lang'] ) && strlen( trim ( $atts['lang'] ) ) == 2) {
			if ( strtolower( trim ( $atts['lang'] ) ) == $current_language ) {
				return $content;
			}
		}
		
        // E.g.: [alg_wc_eu_vat_translate lang="EN,DE"]Text for EN & DE[/alg_wc_eu_vat_translate][alg_wc_eu_vat_translate not_lang="EN,DE"]Text for other languages[/alg_wc_eu_vat_translate]
        return (
            ( ! empty( $atts['lang'] )     && ( empty( $current_language ) || ! in_array( $current_language, array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) ) ) ) ) ||
            ( ! empty( $atts['not_lang'] ) &&     ! empty( $current_language ) &&   in_array( $current_language , array_map( 'trim', explode( ',', strtolower( $atts['not_lang'] ) ) ) ) )
        ) ? '' : $content;

		
	}

}

endif;

return new Alg_WC_EU_VAT_Shortcodes();
