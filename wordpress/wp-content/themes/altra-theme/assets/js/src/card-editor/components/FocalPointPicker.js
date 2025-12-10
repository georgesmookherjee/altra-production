/**
 * Focal Point Picker Component
 * Allows user to select the focal point of the image using react-easy-crop
 */
import { useState, useCallback } from '@wordpress/element';
import Cropper from 'react-easy-crop';

export default function FocalPointPicker({ image, focalPoint, onFocalPointChange }) {
	const [crop, setCrop] = useState({ x: 0, y: 0 });
	const [zoom, setZoom] = useState(1);

	const onCropComplete = useCallback((croppedArea, croppedAreaPixels) => {
		// Calculate focal point from crop center
		// croppedAreaPixels gives us the position in pixels
		// We want to express it as a percentage of the image
		const focalX = ((croppedAreaPixels.x + croppedAreaPixels.width / 2) / croppedAreaPixels.width) * 100;
		const focalY = ((croppedAreaPixels.y + croppedAreaPixels.height / 2) / croppedAreaPixels.height) * 100;

		onFocalPointChange({
			x: Math.round(focalX * 10) / 10,
			y: Math.round(focalY * 10) / 10,
		});
	}, [onFocalPointChange]);

	return (
		<div className="focal-point-picker">
			<label>
				<strong>Focal Point</strong>
				<span className="description">
					Pan and zoom to select where the image should be centered
				</span>
			</label>

			<div className="focal-point-cropper">
				<Cropper
					image={image}
					crop={crop}
					zoom={zoom}
					aspect={4 / 3}
					onCropChange={setCrop}
					onCropComplete={onCropComplete}
					onZoomChange={setZoom}
					showGrid={false}
					objectFit="contain"
				/>
			</div>

			<div className="focal-point-values">
				<span>
					X: <strong>{focalPoint.x}%</strong>
				</span>
				<span>
					Y: <strong>{focalPoint.y}%</strong>
				</span>
			</div>
		</div>
	);
}
