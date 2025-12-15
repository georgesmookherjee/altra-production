/**
 * Project Tile Component
 * Individual project card in the grid - uniform size (4 columns grid)
 */
export default function ProjectTile({ project, onRemove }) {
	return (
		<div className="project-tile">
			<div className="tile-image">
				<img src={project.thumbnail} alt={project.title} />
			</div>

			<div className="tile-info">
				<h4 className="tile-title">{project.title}</h4>

				<div className="tile-controls">
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
