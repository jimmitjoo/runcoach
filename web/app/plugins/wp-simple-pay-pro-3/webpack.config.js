/**
 * External dependencies
 */
const path = require( 'path' );
const webpack = require( 'webpack' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const FixStyleOnlyEntriesPlugin = require( 'webpack-fix-style-only-entries' );

/**
 * WordPress dependencies
 */
const wordpressConfig = require( '@wordpress/scripts/config/webpack.config.js' );

/**
 * Creates a generic configuration object based on the type of plugin.
 *
 * @param {string} plugin Type of plugin. Used to determine the entry context.
 * @return {Object}
 */
const createConfig = ( plugin ) => ( {
	...wordpressConfig,
	// Override externals so dependencies can be packaged with the assets
	// because the minimum WordPress version is still 4.9.
	externals: {
		jquery: 'jQuery',
	},
	context: path.resolve( __dirname, `includes/${ plugin }/assets` ),
	resolve: {
		...wordpressConfig.resolve,
		modules: [
			path.resolve( __dirname, `includes/${ plugin }/assets/js` ),
			'node_modules',
		],
	},
	output: {
		filename: '[name].min.js',
		path: path.resolve( __dirname, `includes/${ plugin }/assets/js` ),
	},
	module: {
		rules: [
			...wordpressConfig.module.rules,
			{
				test: /\.(scss|css)$/,
				use: [
					MiniCssExtractPlugin.loader,
					{
						loader: 'css-loader',
						options: {
							url: false,
						},
					},
					{
						loader: 'postcss-loader',
						options: {
							plugins: [
								require( 'autoprefixer' ),
							],
						},
					},
					{
						loader: 'sass-loader',
						options: {
							prependData: `
								$base-font: Roboto, 'Open Sans', Segoe UI, sans-serif;
								$base-text-color: #333;
								$error-text-color: #eb1c26;
							`,
						},
					},
				],
				exclude: /node_modules/,
				include: /scss/,
			},
		],
	},
	// Purposely removing included default plugins.
	plugins: [
		new webpack.ProvidePlugin( {
			$: 'jquery',
			jQuery: 'jquery',
		} ),
		new MiniCssExtractPlugin( {
			moduleFilename: ( { name } ) => ( `./../css/${ name.replace( '-css', '' ) }.min.css` ),
		} ),
		new FixStyleOnlyEntriesPlugin(),
	],
} );

const ALIASES = {
	'@wpsimplepay/core': path.resolve( __dirname, 'includes/core/assets/js' ),

	// Pretend we are using a real packages.
	'@wpsimplepay/cart': path.resolve( __dirname, 'includes/core/assets/js/packages/cart/src/index.js' ),
	'@wpsimplepay/utils': path.resolve( __dirname, 'includes/core/assets/js/packages/utils/src/index.js' ),
};

const coreConfig = () => {
	const config = createConfig( 'core' );

	return {
		...config,
		resolve: {
			...config.resolve,
			alias: {
				...ALIASES,
			},
		},
		entry: {
			// Javascript.
			'simpay-polyfill': '@babel/polyfill',
			'simpay-admin': './js/admin',
			'simpay-admin-notices': './js/admin/notices.js',
			'simpay-public': './js/frontend',
			// Create a separate file for `simpay-shared` legacy enqueued script.
			'simpay-public-shared': './js/packages/utils/src/legacy.js',

			// CSS.
			'simpay-admin-css': './css/admin/admin.scss',
			'simpay-admin-all-pages-css': './css/admin/all-pages.scss',
			'simpay-public-css': './css/frontend/public.scss',
		},
	};
};

const proConfig = () => {
	const config = createConfig( 'pro' );

	return {
		...config,
		resolve: {
			...config.resolve,
			alias: {
				'@wpsimplepay/pro': path.resolve( __dirname, 'includes/pro/assets/js' ),
				...ALIASES,
			},
		},
		entry: {
			// Javascript.
			'simpay-admin-pro': './js/admin',
			'simpay-admin-subcription-settings': './js/admin/subscription-settings.js',
			'simpay-public-pro': './js/frontend',
			'simpay-public-pro-recaptcha': './js/frontend/components/recaptcha.js',
			'simpay-public-pro-update-payment-method': './js/frontend/update-payment-method.js',

			// CSS.
			'simpay-admin-pro-css': './css/admin/admin.scss',
			'simpay-public-pro-css': './css/frontend/public.scss',
		},
	};
};

module.exports = [ coreConfig, proConfig ];
