/**
 * Image Zoom Control Component
 * Slider and preset buttons to control image zoom level
 */
export default function ImageZoomControl({ zoom, onZoomChange }) {
	const presets = [
		{ label: 'Fit', value: 1.0 },
		{ label: '1.5x', value: 1.5 },
		{ label: '2x', value: 2.0 },
		{ label: '2.5x', value: 2.5 },
	];

	return (
		<div className="image-zoom-control">
			<label>
				<strong>Image Zoom</strong>
				<span className="description">
					Zoom in or out without cropping the image
				</span>
			</label>

			<div className="zoom-slider">
				<input
					type="range"
					min="0.50"
					max="2.5"
					step="0.01"
					value={zoom}
					onChange={(e) => onZoomChange(parseFloat(e.target.value))}
				/>
				<span className="zoom-value">{zoom.toFixed(2)}x</span>
			</div>

			<div className="zoom-presets">
				{presets.map((preset) => (
					<button
						key={preset.value}
						type="button"
						className={`button button-small ${zoom === preset.value ? 'active' : ''}`}
						onClick={() => onZoomChange(preset.value)}
					>
						{preset.label}
					</button>
				))}
			</div>
		</div>
	);
}
