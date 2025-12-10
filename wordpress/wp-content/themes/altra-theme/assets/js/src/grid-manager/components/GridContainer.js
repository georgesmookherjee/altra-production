/**
 * Grid Container Component
 * Manages GridStack instance for drag & drop project positioning
 */
import { useEffect, useRef } from '@wordpress/element';
import { GridStack } from 'gridstack';
import 'gridstack/dist/gridstack.min.css';
import ProjectTile from './ProjectTile';

export default function GridContainer({ items, onLayoutChange, onWidthChange, onRemove }) {
	const gridRef = useRef(null);
	const gridInstanceRef = useRef(null);

	useEffect(() => {
		if (!gridRef.current) return;

		// Initialize GridStack
		gridInstanceRef.current = GridStack.init({
			column: 12,
			cellHeight: 100,
			margin: 10,
			float: false, // Items don't float up
			disableOneColumnMode: true,
			animate: true,
			removeTimeout: 100,
			disableResize: true, // Disable manual resize - use S/M/L buttons only
		}, gridRef.current);

		console.log('GridStack initialized');

		// Listen to changes
		gridInstanceRef.current.on('change', (event, changedItems) => {
			if (changedItems && changedItems.length > 0) {
				console.log('Grid changed:', changedItems);

				// Get all current items with their positions
				const allItems = gridInstanceRef.current.engine.nodes.map(node => ({
					id: parseInt(node.el.dataset.projectId),
					x: node.x,
					y: node.y,
					w: node.w,
					h: node.h,
				}));

				onLayoutChange(allItems);
			}
		});

		return () => {
			if (gridInstanceRef.current) {
				gridInstanceRef.current.destroy(false);
			}
		};
	}, []);

	// Update grid when items change
	useEffect(() => {
		if (!gridInstanceRef.current) return;

		const grid = gridInstanceRef.current;

		// Get current item IDs
		const currentItemIds = items.map(item => item.id);

		// Remove items that are no longer in React state
		const nodesToRemove = grid.engine.nodes.filter(
			node => !currentItemIds.includes(parseInt(node.el.dataset.projectId))
		);

		nodesToRemove.forEach(node => {
			grid.removeWidget(node.el, false); // false = don't trigger change event
		});

		// Sync items from React state to GridStack
		items.forEach(item => {
			const node = grid.engine.nodes.find(
				n => parseInt(n.el.dataset.projectId) === item.id
			);

			if (node) {
				const targetW = item.gridPosition?.w || getWidthColumns(item.width);
				const targetX = item.gridPosition?.x || 0;
				const targetY = item.gridPosition?.y || 0;
				const targetH = item.gridPosition?.h || 2;

				// Only update if changed to avoid infinite loops
				if (node.w !== targetW || node.x !== targetX || node.y !== targetY || node.h !== targetH) {
					grid.update(node.el, {
						x: targetX,
						y: targetY,
						w: targetW,
						h: targetH,
					});
				}
			}
		});
	}, [items]);

	function getWidthColumns(width) {
		const widthMap = {
			small: 4,
			medium: 6,
			large: 12,
		};
		return widthMap[width] || 6;
	}

	function handleWidthChange(projectId, newWidth) {
		const newColumns = getWidthColumns(newWidth);

		// Find the grid item
		const gridItem = gridInstanceRef.current.engine.nodes.find(
			node => parseInt(node.el.dataset.projectId) === projectId
		);

		if (gridItem) {
			gridInstanceRef.current.update(gridItem.el, { w: newColumns });
			onWidthChange(projectId, newWidth);
		}
	}

	function handleRemove(projectId) {
		// Let React handle the removal via state update
		// GridStack will sync automatically when items array changes
		onRemove(projectId);
	}

	return (
		<div className="grid-stack-container">
			<div ref={gridRef} className="grid-stack">
				{items.map(item => (
					<div
						key={item.id}
						className="grid-stack-item"
						data-project-id={item.id}
						data-gs-x={item.gridPosition?.x || 0}
						data-gs-y={item.gridPosition?.y || 0}
						data-gs-w={item.gridPosition?.w || getWidthColumns(item.width)}
						data-gs-h={item.gridPosition?.h || 2}
					>
						<div className="grid-stack-item-content">
							<ProjectTile
								project={item}
								onWidthChange={(newWidth) => handleWidthChange(item.id, newWidth)}
								onRemove={() => handleRemove(item.id)}
							/>
						</div>
					</div>
				))}
			</div>
		</div>
	);
}
