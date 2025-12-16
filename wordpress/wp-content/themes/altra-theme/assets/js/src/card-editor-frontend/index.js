/**
 * Card Editor Frontend Entry Point
 * Initializes React modal for visual card customization on frontend
 */
import { render } from '@wordpress/element';
import CardEditorModal from './CardEditorModal';
import './style.scss';

// Store reference to current modal
let modalRoot = null;

/**
 * Open Card Editor modal for a specific project
 */
window.altraOpenCardEditor = function(projectId) {
	// Fetch project data
	fetch(`/wp-json/altra/v1/project/${projectId}/visual-settings`)
		.then(response => response.json())
		.then(data => {
			// Create modal root if it doesn't exist
			if (!modalRoot) {
				modalRoot = document.createElement('div');
				modalRoot.id = 'altra-card-editor-modal-root';
				document.body.appendChild(modalRoot);
			}

			// Render modal
			render(
				<CardEditorModal
					projectId={projectId}
					projectData={data}
					onClose={() => {
						// Unmount and remove modal
						render(null, modalRoot);
					}}
				/>,
				modalRoot
			);
		})
		.catch(error => {
			console.error('Failed to load project data:', error);
			alert('Failed to load project data. Please try again.');
		});
};
