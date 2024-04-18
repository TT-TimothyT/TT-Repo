const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const WooCommerceDependencyExtractionWebpackPlugin = require('@woocommerce/dependency-extraction-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

// Remove SASS rule from the default config so we can define our own.
const defaultRules = defaultConfig.module.rules.filter((rule) => {
	return String(rule.test) !== String(/\.(sc|sa)ss$/);
});

module.exports = {
	...defaultConfig,
	entry: {
		index: path.resolve( process.cwd(), 'src', 'index.js' ),
		'blocks-guest-capture':
			path.resolve(
				process.cwd(),
				'src',
				'blocks-guest-capture.js'
			),
		'blocks-sms-consent-frontend':
			path.resolve(
				process.cwd(),
				'src',
				'blocks-gdpr-sms-consent',
				'frontend.js'
			),
		'wcap-blocks-gdpr-frontend':
			path.resolve(
				process.cwd(),
				'src',
				'blocks-gdpr-email-compliance',
				'frontend.js'
			),
	},
	output: {
		path: path.resolve( __dirname, 'build' ),
		filename: '[name].js',
	},
	plugins: [
		...defaultConfig.plugins.filter(
			(plugin) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new WooCommerceDependencyExtractionWebpackPlugin(),
		new MiniCssExtractPlugin({
			filename: `[name].css`,
		}),
	],
};