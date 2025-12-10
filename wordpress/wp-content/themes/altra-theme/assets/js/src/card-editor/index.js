/**
 * Card Editor Entry Point
 * Initializes React app for visual card customization in admin
 */
import { render } from '@wordpress/element';
import CardEditorApp from './CardEditorApp';
import './style.scss';

document.addEventListener('DOMContentLoaded', () => {
	const root = document.getElementById('altra-card-editor-root');

	if (root && window.altraCardEditorData) {
		console.log('Card Editor initializing...');
		render(<CardEditorApp />, root);
	}
});
