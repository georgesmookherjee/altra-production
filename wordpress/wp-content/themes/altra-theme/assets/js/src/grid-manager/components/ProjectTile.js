/**
 * Project Tile Component
 * Individual project card in the grid with width controls
 */
export default function ProjectTile({ project, onWidthChange, onRemove }) {
	const widthOptions = [
		{ value: 'small', label: 'S', cols: 4 },
		{ value: 'medium', label: 'M', cols: 6 },
		{ value: 'large', label: 'L', cols: 12 },
	];

	return (
		<div className="project-tile">
			<div className="tile-image">
				<img src={project.thumbnail} alt={project.title} />
			</div>

			<div className="tile-info">
				<h4 className="tile-title">{project.title}</h4>

				<div className="tile-controls">
					<div className="width-selector">
						{widthOptions.map(option => (
							<button
								key={option.value}
								type="button"
								className={`width-btn ${project.width === option.value ? 'active' : ''}`}
								onClick={(e) => {
									e.preventDefault();
									e.stopPropagation();
									onWidthChange(option.value);
								}}
								title={`${option.label} - ${option.cols}/12 columns`}
							>
								{option.label}
							</button>
						))}
					</div>

					<button
						type="button"
						className="remove-btn"
						onClick={(e) => {
							e.preventDefault();
							e.stopPropagation();
							onRemove();
						}}
						title="Remove from grid"
					>
						×
					</button>
				</div>
			</div>
		</div>
	);
}
