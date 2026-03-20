/**
 * Webpack configuration for extrachill-chat
 *
 * Extends @wordpress/scripts defaults for block builds.
 * Resolves @extrachill/chat from source files (not dist).
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

const chatPackageSrc = path.resolve(
	__dirname,
	'node_modules/@extrachill/chat/src',
);

module.exports = {
	...defaultConfig,
	entry: {
		'blocks/chat/index': './src/blocks/chat/index.js',
		'blocks/chat/view': './src/blocks/chat/view.js',
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'build' ),
	},
	resolve: {
		...defaultConfig.resolve,
		alias: {
			...( defaultConfig.resolve?.alias || {} ),
			// Resolve to source files — webpack handles TSX via wp-scripts
			'@extrachill/chat': chatPackageSrc + '/index.ts',
			'@extrachill/chat/css': path.resolve(
				__dirname,
				'node_modules/@extrachill/chat/css/chat.css',
			),
		},
		extensions: [
			'.tsx', '.ts',
			...( defaultConfig.resolve?.extensions || [ '.jsx', '.js', '.json' ] ),
		],
	},
	module: {
		...defaultConfig.module,
		rules: [
			// Add TypeScript support for .ts/.tsx files from @extrachill/chat
			{
				test: /\.tsx?$/,
				include: [ chatPackageSrc ],
				use: [
					{
						loader: require.resolve( 'babel-loader' ),
						options: {
							presets: [
								require.resolve( '@babel/preset-typescript' ),
								require.resolve( '@babel/preset-react' ),
							],
						},
					},
				],
			},
			...( defaultConfig.module?.rules || [] ),
		],
	},
};
