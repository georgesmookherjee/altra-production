/**
 * Card Editor App
 * Main component for visual card customization
 * Supports both image and Vimeo video featured media
 */
import { useState, useEffect } from '@wordpress/element';
import FocalPointPicker from './components/FocalPointPicker';
import ImageZoomControl from './components/ImageZoomControl';
import PreviewCard from './components/PreviewCard';

function extractVimeoId(url) {
	if (!url) return null;
	const match = url.match(/vimeo\.com\/(\d+)/);
	return match ? match[1] : null;
}

export default function CardEditorApp() {
	const editorData = window.altraCardEditorData || {};
	const mediaType = editorData.mediaType || 'image';
	const isVideo = mediaType === 'video';

	// For video projects, use Vimeo thumbnail as preview image
	const vimeoId = isVideo ? extractVimeoId(editorData.featuredVideoUrl) : null;
	const vimeoThumbnail = vimeoId
		? `https://vumbnail.com/${vimeoId}.jpg`
		: null;

	// The image used for the editor preview
	const previewImage = isVideo ? vimeoThumbnail : editorData.featuredImage;

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

	// Detect orientation for aspect ratio
	// For video, use the video's own orientation; for images, use the image orientation
	const imageOrientation = isVideo
		? (editorData.featuredVideoOrientation || 'landscape')
		: (editorData.imageOrientation || 'portrait');
	const aspectRatio = isVideo && imageOrientation === 'landscape'
		? 3 / 1        // landscape video: 3:1 (matches homepage card)
		: imageOrientation === 'landscape'
			? 3 / 1.9  // landscape image
			: 3 / 4;   // portrait

	// Update hidden input whenever settings change
	useEffect(() => {
		const hiddenInput = document.getElementById('altra_visual_settings_input');
		if (hiddenInput) {
			hiddenInput.value = JSON.stringify({ focalPoint, zoom, textLayers });
		}
	}, [focalPoint, zoom, textLayers]);

	function handleReset() {
		setFocalPoint({ x: 50, y: 50 });
		setZoom(1.0);
		setTextLayers([]);
	}

	if (!previewImage) {
		return (
			<div className="card-editor-notice">
				<p><strong>{ isVideo ? 'Aucune URL Vimeo définie.' : 'No featured image set.' }</strong></p>
				<p>{ isVideo
					? 'Veuillez renseigner une URL Vimeo dans la metabox "Cover Media" pour utiliser le Card Editor.'
					: 'Please set a featured image for this project to use the Visual Card Editor.'
				}</p>
			</div>
		);
	}

	return (
		<div className="card-editor-container">
			{ isVideo && (
				<div className="card-editor-notice" style={{ marginBottom: '12px', background: '#e8f4fd', padding: '8px 12px', borderLeft: '4px solid #2271b1', borderRadius: '2px' }}>
					<p style={{ margin: 0 }}>
						<strong>Mode vidéo</strong> — Le focal point et le zoom s'appliquent au cadrage de la vignette Vimeo dans la card.
					</p>
				</div>
			)}
			<div className="card-editor-layout">
				{/* Left: Preview */}
				<div className="card-editor-preview">
					<h3>Preview ({imageOrientation})</h3>
					<PreviewCard
						image={previewImage}
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
						<h3>{ isVideo ? 'Video Frame Settings' : 'Image Settings' }</h3>

						<FocalPointPicker
							image={previewImage}
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
