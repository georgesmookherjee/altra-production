/**
 * Card Editor Modal Component
 * Full-screen modal for editing project card visuals on the frontend
 */
import { useState, useEffect } from '@wordpress/element';
import FocalPointPicker from '../card-editor/components/FocalPointPicker';
import ImageZoomControl from '../card-editor/components/ImageZoomControl';
import PreviewCard from '../card-editor/components/PreviewCard';

export default function CardEditorModal({ projectId, projectData, onClose }) {
	const [focalPoint, setFocalPoint] = useState(
		projectData.currentSettings?.focalPoint || { x: 50, y: 50 }
	);
	const [zoom, setZoom] = useState(
		projectData.currentSettings?.zoom || 1.0
	);
	const [isSaving, setIsSaving] = useState(false);

	// Handle ESC key to close modal
	useEffect(() => {
		function handleEscape(e) {
			if (e.key === 'Escape') {
				onClose();
			}
		}
		document.addEventListener('keydown', handleEscape);
		return () => document.removeEventListener('keydown', handleEscape);
	}, [onClose]);

	// Lock body scroll when modal is open
	useEffect(() => {
		document.body.style.overflow = 'hidden';
		return () => {
			document.body.style.overflow = '';
		};
	}, []);

	function handleReset() {
		if (confirm('Reset all visual settings to default?')) {
			setFocalPoint({ x: 50, y: 50 });
			setZoom(1.0);
		}
	}

	async function handleSave() {
		setIsSaving(true);

		const settings = {
			focalPoint,
			zoom,
			textLayers: [], // Not used for now
		};

		try {
			const response = await fetch(`/wp-json/altra/v1/project/${projectId}/visual-settings`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.altraGridData?.nonce || '',
				},
				body: JSON.stringify({ visualSettings: settings }),
			});

			if (!response.ok) {
				throw new Error('Failed to save settings');
			}

			// Reload page to show updated card
			window.location.reload();
		} catch (error) {
			console.error('Save error:', error);
			alert('Failed to save settings. Please try again.');
			setIsSaving(false);
		}
	}

	return (
		<div className="card-editor-modal-overlay">
			<div className="card-editor-modal">
				{/* Header */}
				<div className="modal-header">
					<h2>Edit Card: {projectData.projectTitle}</h2>
					<button
						type="button"
						className="modal-close"
						onClick={onClose}
						aria-label="Close"
					>
						×
					</button>
				</div>

				{/* Content */}
				<div className="modal-content">
					<div className="card-editor-layout">
						{/* Left: Preview */}
						<div className="card-editor-preview">
							<h3>Preview</h3>
							<PreviewCard
								image={projectData.featuredImage}
								title={projectData.projectTitle}
								focalPoint={focalPoint}
								zoom={zoom}
								textLayers={[]}
							/>
						</div>

						{/* Right: Controls */}
						<div className="card-editor-controls">
							<div className="control-section">
								<h3>Image Settings</h3>

								<FocalPointPicker
									image={projectData.featuredImage}
									focalPoint={focalPoint}
									onFocalPointChange={setFocalPoint}
								/>

								<ImageZoomControl
									zoom={zoom}
									onZoomChange={setZoom}
								/>
							</div>

							<div className="control-actions">
								<button
									type="button"
									className="button button-secondary"
									onClick={handleReset}
								>
									Reset to Defaults
								</button>
							</div>
						</div>
					</div>
				</div>

				{/* Footer */}
				<div className="modal-footer">
					<button
						type="button"
						className="button button-secondary"
						onClick={onClose}
						disabled={isSaving}
					>
						Cancel
					</button>
					<button
						type="button"
						className="button button-primary"
						onClick={handleSave}
						disabled={isSaving}
					>
						{isSaving ? 'Saving...' : 'Save Changes'}
					</button>
				</div>
			</div>
		</div>
	);
}
