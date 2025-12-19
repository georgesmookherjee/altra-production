/**
 * Focal Point Picker Component
 * Allows user to select the focal point of the image using react-easy-crop
 */
import { useState, useCallback } from '@wordpress/element';
import Cropper from 'react-easy-crop';

export default function FocalPointPicker({ image, focalPoint, onFocalPointChange, aspectRatio = 3 / 4 }) {
	const [crop, setCrop] = useState({ x: 0, y: 0 });
	const [zoom, setZoom] = useState(1);

	const onCropComplete = useCallback((croppedArea, croppedAreaPixels) => {
		// croppedArea gives us percentages relative to the full image
		// This is what we need for the focal point!
		const focalX = croppedArea.x + (croppedArea.width / 2);
		const focalY = croppedArea.y + (croppedArea.height / 2);

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
					aspect={aspectRatio}
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
