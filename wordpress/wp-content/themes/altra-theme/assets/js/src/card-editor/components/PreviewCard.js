/**
 * Preview Card Component
 * Shows a live preview of how the card will look with current settings
 */
export default function PreviewCard({ image, title, focalPoint, zoom, textLayers }) {
	const imageStyle = {
		transformOrigin: `${focalPoint.x}% ${focalPoint.y}%`,
		transform: `scale(${zoom})`,
		objectFit: 'contain',
	};

	return (
		<div className="preview-card-container">
			<div className="preview-card">
				<div className="preview-card-image">
					<img
						src={image}
						alt={title}
						style={imageStyle}
					/>
				</div>

				{textLayers.length > 0 && (
					<div className="preview-card-info">
						{textLayers.map(layer => (
							<div
								key={layer.id}
								className={`preview-text-layer preview-text-${layer.size}`}
								style={{
									left: `${layer.position.x}%`,
									top: `${layer.position.y}%`,
								}}
							>
								{layer.id === 'title' && <h3>{title}</h3>}
								{layer.id !== 'title' && (
									<span className={`layer-${layer.id}`}>
										{layer.id.charAt(0).toUpperCase() + layer.id.slice(1)}
									</span>
								)}
							</div>
						))}
					</div>
				)}
			</div>

			<p className="preview-note">
				<em>This is a preview. Actual appearance may vary based on the project card template.</em>
			</p>
		</div>
	);
}
