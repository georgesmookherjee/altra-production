/**
 * Grid Manager App - Main Component
 * Manages homepage project grid with drag & drop
 */
import { useState, useEffect } from '@wordpress/element';
import { fetchProjects, saveGridPositions } from './utils/api';
import GridContainer from './components/GridContainer';
import ProjectSidebar from './components/ProjectSidebar';

export default function GridManagerApp() {
	const [isEditMode, setIsEditMode] = useState(false);
	const [allProjects, setAllProjects] = useState([]);
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
			setAllProjects(data);

			// Get projects with positions or fallback to all
			const positioned = data.filter(p => p.gridPosition);

			if (positioned.length > 0) {
				setGridItems(positioned);
			} else {
				// First time: add first 6 projects to grid
				setGridItems(data.slice(0, 6));
			}

			console.log('Projects loaded:', data.length);
		} catch (error) {
			console.error('Failed to load projects:', error);
			showMessage('Failed to load projects: ' + error.message, 'error');
		} finally {
			setLoading(false);
		}
	}

	function handleLayoutChange(layoutItems) {
		// Update grid items with new positions
		setGridItems(prevItems => {
			return prevItems.map(item => {
				const layoutItem = layoutItems.find(l => l.id === item.id);
				if (layoutItem) {
					return {
						...item,
						gridPosition: {
							x: layoutItem.x,
							y: layoutItem.y,
							w: layoutItem.w,
							h: layoutItem.h,
						},
					};
				}
				return item;
			});
		});
	}

	// Width is now uniform - all items take 1 column in 4-column grid
	// No need for handleWidthChange anymore

	function handleAddToGrid(project) {
		if (gridItems.find(item => item.id === project.id)) {
			return; // Already in grid
		}

		setGridItems(prev => [...prev, project]);
		showMessage(`Added "${project.title}" to grid`, 'success');
	}

	function handleRemoveFromGrid(projectId) {
		setGridItems(prev => prev.filter(item => item.id !== projectId));
		const project = allProjects.find(p => p.id === projectId);
		if (project) {
			showMessage(`Removed "${project.title}" from grid`, 'success');
		}
	}

	async function handleSaveGrid() {
		setSaving(true);
		setMessage(null);

		try {
			// Build positions array from current grid state
			// Sort by Y position first, then X position (reading order: top to bottom, left to right)
			const sortedItems = [...gridItems].sort((a, b) => {
				const aY = a.gridPosition?.y || 0;
				const bY = b.gridPosition?.y || 0;
				const aX = a.gridPosition?.x || 0;
				const bX = b.gridPosition?.x || 0;

				if (aY !== bY) {
					return aY - bY; // Sort by row first
				}
				return aX - bX; // Then by column
			});

			const positions = sortedItems.map((item, index) => ({
				id: item.id,
				x: item.gridPosition?.x || 0,
				y: item.gridPosition?.y || 0,
				w: item.gridPosition?.w || 1, // All items are 1 column wide in 4-column grid
				h: item.gridPosition?.h || 2,
				order: index, // Now index represents visual order
			}));

			const result = await saveGridPositions(positions);
			showMessage(result.message, 'success');

			// Reload to get fresh data
			await loadProjects();

			// Close edit mode after 2 seconds
			setTimeout(() => {
				setIsEditMode(false);
			}, 2000);
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
						<ProjectSidebar
							allProjects={allProjects}
							gridProjects={gridItems}
							onAddToGrid={handleAddToGrid}
						/>

						<GridContainer
							items={gridItems}
							onLayoutChange={handleLayoutChange}
							onRemove={handleRemoveFromGrid}
						/>
					</div>
				</div>
			)}
		</div>
	);
}
