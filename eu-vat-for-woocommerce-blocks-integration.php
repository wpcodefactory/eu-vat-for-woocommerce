<?php
use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

define ( 'EuVatForWoocommerce_VERSION', '2.11.7' );

/**
 * Class for integrating with WooCommerce Blocks
 */
class EuVatForWoocommerce_Blocks_Integration implements IntegrationInterface {

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'eu-vat-for-woocommerce';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {
		$this->register_euvat_block_frontend_scripts();
		$this->register_euvat_block_editor_scripts();
        $this->register_euvat_block_editor_styles();
        $this->register_main_integration();
		
		add_action( 'wp_enqueue_scripts', array( $this, 'eu_vat_country_enqueue_script' ) );
	}
	
	/**
	 * Enqueue country array.
	 */
	public function eu_vat_country_enqueue_script() {
		
		$wc_countries = new WC_Countries();
		$countries = $wc_countries->get_countries();
		$flipped_countries = array_flip( $countries );
		
		wp_localize_script( 'eu-vat-for-woocommerce-checkout-eu-vat-field-block-frontend', 'alg_wc_eu_frontend_countries_object', $flipped_countries );
		
	}

	/**
	 * Registers the main JS file required to add filters and Slot/Fills.
	 */
	public function register_main_integration() {
		$script_path = '/build/index.js';
		$style_path  = '/build/style-index.css';

		$script_url = plugins_url( $script_path, __FILE__ );
		$style_url  = plugins_url( $style_path, __FILE__ );

		$script_asset_path = dirname( __FILE__ ) . '/build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_path ),
			);

		wp_enqueue_style(
			'eu-vat-for-woocommerce-blocks-integration',
			$style_url,
			[],
			$this->get_file_version( $style_path )
		);

		wp_register_script(
			'eu-vat-for-woocommerce-blocks-integration',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_set_script_translations(
			'eu-vat-for-woocommerce-blocks-integration',
			'eu-vat-for-woocommerce',
			dirname( __FILE__ ) . '/languages'
		);
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'eu-vat-for-woocommerce-blocks-integration', 'eu-vat-for-woocommerce-checkout-eu-vat-field-block-frontend' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'eu-vat-for-woocommerce-blocks-integration', 'eu-vat-for-woocommerce-checkout-eu-vat-field-block-editor' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		$data = array(
			'eu-vat-for-woocommerce-active' => true,
			'example-data' => __( 'This is some example data from the server', 'eu-vat-for-woocommerce' ),
            'optInDefaultText' => __( 'I want to receive updates about products and promotions.', 'eu-vat-for-woocommerce' ),
		);

		return $data;

	}

    public function register_euvat_block_editor_styles() {
        $style_path  = '/build/style-eu-vat-for-woocommerce-checkout-eu-vat-field-block.css';

        $style_url  = plugins_url( $style_path, __FILE__ );
        wp_enqueue_style(
            'eu-vat-for-woocommerce-checkout-eu-vat-field-block',
            $style_url,
            [],
            $this->get_file_version( $style_path )
        );
    }

    public function register_euvat_block_editor_scripts() {
        $script_path       = '/build/eu-vat-for-woocommerce-checkout-eu-vat-field-block.js';
        $script_url        = plugins_url( $script_path, __FILE__ );
        $script_asset_path = dirname( __FILE__ ) . '/build/eu-vat-for-woocommerce-checkout-eu-vat-field-block.asset.php';
        $script_asset      = file_exists( $script_asset_path )
            ? require $script_asset_path
            : array(
                'dependencies' => array(),
                'version'      => $this->get_file_version( $script_asset_path ),
            );

        wp_register_script(
            'eu-vat-for-woocommerce-checkout-eu-vat-field-block-editor',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        wp_set_script_translations(
            'eu-vat-for-woocommerce-eu-vat-field-block-editor', // script handle
            'eu-vat-for-woocommerce', // text domain
            dirname( __FILE__ ) . '/languages'
        );
    }

    public function register_euvat_block_frontend_scripts() {
        $script_path       = '/build/eu-vat-for-woocommerce-checkout-eu-vat-field-block-frontend.js';
        $script_url        = plugins_url( $script_path, __FILE__ );
        $script_asset_path = dirname( __FILE__ ) . '/build/eu-vat-field-block-frontend.asset.php';
        $script_asset      = file_exists( $script_asset_path )
            ? require $script_asset_path
            : array(
                'dependencies' => array(),
                'version'      => $this->get_file_version( $script_asset_path ),
            );

        wp_register_script(
            'eu-vat-for-woocommerce-checkout-eu-vat-field-block-frontend',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );
        wp_set_script_translations(
            'eu-vat-for-woocommerce-checkout-eu-vat-field-block-frontend', // script handle
            'eu-vat-for-woocommerce', // text domain
            dirname( __FILE__ ) . '/languages'
        );
		
		
		
		
    }

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		return EuVatForWoocommerce_VERSION;
	}
}
