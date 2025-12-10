/**
 * Grid Manager Entry Point
 * Initializes React app for homepage grid management
 */
import { render } from '@wordpress/element';
import GridManagerApp from './GridManagerApp';
import './style.scss';

document.addEventListener('DOMContentLoaded', () => {
	const root = document.getElementById('altra-grid-manager-root');

	if (root) {
		console.log('Grid Manager initializing...');
		render(<GridManagerApp />, root);
	}
});
