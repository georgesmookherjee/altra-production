/**
 * Project Sidebar Component
 * Shows available projects that can be added to the grid
 */
export default function ProjectSidebar({ allProjects, gridProjects, onAddToGrid }) {
	// Filter out projects already in grid
	const gridProjectIds = gridProjects.map(p => p.id);
	const availableProjects = allProjects.filter(p => !gridProjectIds.includes(p.id));

	return (
		<div className="project-sidebar">
			<h3>Available Projects</h3>
			<p className="sidebar-info">
				{availableProjects.length} project{availableProjects.length !== 1 ? 's' : ''} available
			</p>

			<div className="projects-list">
				{availableProjects.length === 0 ? (
					<p className="no-projects">All projects are in the grid</p>
				) : (
					availableProjects.map(project => (
						<div
							key={project.id}
							className="sidebar-project"
							onClick={() => onAddToGrid(project)}
						>
							<img src={project.thumbnail} alt={project.title} />
							<span>{project.title}</span>
							<button type="button" className="add-btn" title="Add to grid">
								+
							</button>
						</div>
					))
				)}
			</div>

			{gridProjects.length > 0 && (
				<div className="sidebar-stats">
					<p className="stats-title">Grid Stats:</p>
					<ul>
						<li>{gridProjects.length} projects in grid</li>
						<li>
							{gridProjects.filter(p => p.width === 'small').length} small,{' '}
							{gridProjects.filter(p => p.width === 'medium').length} medium,{' '}
							{gridProjects.filter(p => p.width === 'large').length} large
						</li>
					</ul>
				</div>
			)}
		</div>
	);
}
