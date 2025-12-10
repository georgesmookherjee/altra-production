/**
 * Card Editor Entry Point
 * Initializes React app for project card visual editing
 */
import { render } from '@wordpress/element';

console.log('Card Editor loaded');

// Placeholder for now - we'll build the full component next
document.addEventListener('DOMContentLoaded', () => {
	const root = document.getElementById('altra-card-editor-root');

	if (root && window.altraCardEditorData) {
		console.log('Card Editor root found, ready to initialize');
		// render(<CardEditorApp {...window.altraCardEditorData} />, root); // Will add this next
	}
});
