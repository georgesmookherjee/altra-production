/**
 * WordPress Scripts Webpack Configuration
 * Extends default config for multiple entry points
 */
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
	...defaultConfig,
	entry: {
		'grid-manager': path.resolve(__dirname, 'assets/js/src/grid-manager/index.js'),
		'card-editor': path.resolve(__dirname, 'assets/js/src/card-editor/index.js'),
	},
	output: {
		path: path.resolve(__dirname, 'build'),
		filename: '[name].js',
	},
};
