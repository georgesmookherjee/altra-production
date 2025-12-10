/**
 * Grid Manager Entry Point
 * Initializes React app for homepage grid management
 */
import { render } from '@wordpress/element';

console.log('Grid Manager loaded');

// Placeholder for now - we'll build the full component next
document.addEventListener('DOMContentLoaded', () => {
	const root = document.getElementById('altra-grid-manager-root');

	if (root) {
		console.log('Grid Manager root found, ready to initialize');
		// render(<GridManagerApp />, root); // Will add this next
	}
});
