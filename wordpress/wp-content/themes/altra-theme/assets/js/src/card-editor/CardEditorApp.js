/**
 * Card Editor App
 * Main component for visual card customization
 */
import { useState, useEffect } from '@wordpress/element';
import FocalPointPicker from './components/FocalPointPicker';
import ImageZoomControl from './components/ImageZoomControl';
import PreviewCard from './components/PreviewCard';

export default function CardEditorApp() {
	const editorData = window.altraCardEditorData || {};

	// Initialize state from window data
	const [focalPoint, setFocalPoint] = useState(
		editorData.currentSettings?.focalPoint || { x: 50, y: 50 }
	);
	const [zoom, setZoom] = useState(
		editorData.currentSettings?.zoom || 1.0
	);
	const [textLayers, setTextLayers] = useState(
		editorData.currentSettings?.textLayers || []
	);

	// Detect image orientation for proper aspect ratio
	const imageOrientation = editorData.imageOrientation || 'portrait';
	const aspectRatio = imageOrientation === 'landscape' ? 3 / 1.9 : 3 / 4;

	// Update hidden input whenever settings change
	useEffect(() => {
		const settings = {
			focalPoint,
			zoom,
			textLayers,
		};

		const hiddenInput = document.getElementById('altra_visual_settings_input');
		if (hiddenInput) {
			hiddenInput.value = JSON.stringify(settings);
		}
	}, [focalPoint, zoom, textLayers]);

	function handleReset() {
		setFocalPoint({ x: 50, y: 50 });
		setZoom(1.0);
		setTextLayers([]);
	}

	if (!editorData.featuredImage) {
		return (
			<div className="card-editor-notice">
				<p><strong>No featured image set.</strong></p>
				<p>Please set a featured image for this project to use the Visual Card Editor.</p>
			</div>
		);
	}

	return (
		<div className="card-editor-container">
			<div className="card-editor-layout">
				{/* Left: Preview */}
				<div className="card-editor-preview">
					<h3>Preview ({imageOrientation})</h3>
					<PreviewCard
						image={editorData.featuredImage}
						title={editorData.projectTitle}
						focalPoint={focalPoint}
						zoom={zoom}
						textLayers={textLayers}
						aspectRatio={aspectRatio}
					/>
				</div>

				{/* Right: Controls */}
				<div className="card-editor-controls">
					<div className="control-section">
						<h3>Image Settings</h3>

						<FocalPointPicker
							image={editorData.featuredImage}
							focalPoint={focalPoint}
							onFocalPointChange={setFocalPoint}
							aspectRatio={aspectRatio}
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
	);
}
