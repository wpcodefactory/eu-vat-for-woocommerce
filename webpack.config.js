const defaultConfig = require( "@wordpress/scripts/config/webpack.config" );
const path = require( "path" );
const RemoveEmptyScriptsPlugin = require( "webpack-remove-empty-scripts" );

module.exports = {
	...defaultConfig,
	entry: {
		"js/wpfactory-wc-eu-vat": "./assets/js/wpfactory-wc-eu-vat.js",
		"js/wpfactory-wc-eu-vat-confirmo": "./assets/js/wpfactory-wc-eu-vat-confirmo.js",
		"js/wpfactory-wc-eu-vat-place-order": "./assets/js/wpfactory-wc-eu-vat-place-order.js",
		"css/wpfactory-wc-eu-vat-confirmo": "./assets/css/wpfactory-wc-eu-vat-confirmo.css",
	},
	output: {
		path: path.resolve( __dirname, "assets/build" ),
		clean: true,
	},
	plugins: [
		...defaultConfig.plugins.filter(
			( p ) =>
				p.constructor.name !== "DependencyExtractionWebpackPlugin" // remove asset.php
				&& p.constructor.name !== "RtlCssPlugin"  // remove rtl CSS
		),
		new RemoveEmptyScriptsPlugin()
	],
};