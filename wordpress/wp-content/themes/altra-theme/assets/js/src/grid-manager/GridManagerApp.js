/**
 * Grid Manager App - Main Component
 * Manages homepage project grid with drag & drop
 */
import { useState, useEffect } from '@wordpress/element';
import { fetchProjects, saveGridPositions } from './utils/api';

export default function GridManagerApp() {
	const [isEditMode, setIsEditMode] = useState(false);
	const [projects, setProjects] = useState([]);
	const [gridItems, setGridItems] = useState([]);
	const [loading, setLoading] = useState(false);
	const [saving, setSaving] = useState(false);
	const [message, setMessage] = useState(null);

	// Load projects on mount
	useEffect(() => {
		loadProjects();
	}, []);

	async function loadProjects() {
		setLoading(true);
		try {
			const data = await fetchProjects();
			setProjects(data);

			// Separate projects with grid positions from those without
			const positioned = data.filter(p => p.gridPosition);
			const unpositioned = data.filter(p => !p.gridPosition);

			setGridItems(positioned);

			console.log('Projects loaded:', data.length);
			console.log('Positioned:', positioned.length, 'Unpositioned:', unpositioned.length);
		} catch (error) {
			console.error('Failed to load projects:', error);
			showMessage('Failed to load projects: ' + error.message, 'error');
		} finally {
			setLoading(false);
		}
	}

	async function handleSaveGrid() {
		setSaving(true);
		setMessage(null);

		try {
			// Build positions array from current grid state
			const positions = gridItems.map((item, index) => ({
				id: item.id,
				x: item.gridPosition?.x || 0,
				y: item.gridPosition?.y || 0,
				w: item.gridPosition?.w || (item.width === 'small' ? 4 : item.width === 'large' ? 12 : 6),
				h: item.gridPosition?.h || 2,
				order: index,
			}));

			const result = await saveGridPositions(positions);
			showMessage(result.message, 'success');

			// Reload to get fresh data
			await loadProjects();
			setIsEditMode(false);
		} catch (error) {
			console.error('Failed to save grid:', error);
			showMessage('Failed to save grid: ' + error.message, 'error');
		} finally {
			setSaving(false);
		}
	}

	function showMessage(text, type) {
		setMessage({ text, type });
		setTimeout(() => setMessage(null), 5000);
	}

	function handleCancel() {
		setIsEditMode(false);
		// Reload to reset any unsaved changes
		loadProjects();
	}

	if (loading) {
		return (
			<div className="altra-grid-manager-loading">
				Loading Grid Manager...
			</div>
		);
	}

	return (
		<div className="altra-grid-manager">
			{!isEditMode ? (
				<button
					className="edit-grid-button"
					onClick={() => setIsEditMode(true)}
					type="button"
				>
					Edit Grid
				</button>
			) : (
				<div className="edit-mode-overlay">
					<div className="edit-toolbar">
						<button
							className="save-button"
							onClick={handleSaveGrid}
							disabled={saving}
							type="button"
						>
							{saving ? 'Saving...' : 'Save Grid'}
						</button>
						<button
							className="cancel-button"
							onClick={handleCancel}
							disabled={saving}
							type="button"
						>
							Cancel
						</button>
					</div>

					{message && (
						<div className={`grid-message grid-message-${message.type}`}>
							{message.text}
						</div>
					)}

					<div className="edit-layout">
						<div className="project-sidebar">
							<h3>Available Projects</h3>
							<div className="projects-list">
								{projects.map(project => (
									<div key={project.id} className="sidebar-project">
										<img src={project.thumbnail} alt={project.title} />
										<span>{project.title}</span>
									</div>
								))}
							</div>
						</div>

						<div className="grid-container">
							<h3>Homepage Grid (Coming Soon)</h3>
							<p>GridStack integration will be added next</p>
							<div className="grid-preview">
								{gridItems.map(item => (
									<div key={item.id} className="grid-item-preview">
										<img src={item.thumbnail} alt={item.title} />
										<p>{item.title}</p>
										<small>Width: {item.width}</small>
									</div>
								))}
							</div>
						</div>
					</div>
				</div>
			)}
		</div>
	);
}
