/**
 * Text Layer Editor Component
 * Allows user to manage text layers: visibility, size, and order
 */
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

export default function TextLayerEditor({ layers, onLayersChange }) {
	const availableFields = [
		{ id: 'title', label: 'Title' },
		{ id: 'client', label: 'Client' },
		{ id: 'photographer', label: 'Photographer' },
		{ id: 'project', label: 'Project Name' },
	];

	const sizes = ['small', 'medium', 'large'];

	function handleToggleVisibility(fieldId) {
		const existingIndex = layers.findIndex(l => l.id === fieldId);

		if (existingIndex >= 0) {
			// Remove from layers
			onLayersChange(layers.filter(l => l.id !== fieldId));
		} else {
			// Add to layers
			const newLayer = {
				id: fieldId,
				visible: true,
				size: 'medium',
				position: { x: 20, y: 80 },
			};
			onLayersChange([...layers, newLayer]);
		}
	}

	function handleSizeChange(fieldId, size) {
		onLayersChange(
			layers.map(layer =>
				layer.id === fieldId
					? { ...layer, size }
					: layer
			)
		);
	}

	function handleDragEnd(result) {
		if (!result.destination) {
			return;
		}

		const reordered = Array.from(layers);
		const [removed] = reordered.splice(result.source.index, 1);
		reordered.splice(result.destination.index, 0, removed);

		onLayersChange(reordered);
	}

	return (
		<div className="text-layer-editor">
			<p className="description">
				Select which fields to display and drag to reorder
			</p>

			<div className="available-fields">
				{availableFields.map(field => {
					const isVisible = layers.some(l => l.id === field.id);
					return (
						<label key={field.id} className="field-checkbox">
							<input
								type="checkbox"
								checked={isVisible}
								onChange={() => handleToggleVisibility(field.id)}
							/>
							{field.label}
						</label>
					);
				})}
			</div>

			{layers.length > 0 && (
				<DragDropContext onDragEnd={handleDragEnd}>
					<Droppable droppableId="text-layers">
						{(provided) => (
							<div
								className="text-layers-list"
								{...provided.droppableProps}
								ref={provided.innerRef}
							>
								{layers.map((layer, index) => {
									const field = availableFields.find(f => f.id === layer.id);
									return (
										<Draggable
											key={layer.id}
											draggableId={layer.id}
											index={index}
										>
											{(provided, snapshot) => (
												<div
													className={`text-layer-item ${snapshot.isDragging ? 'dragging' : ''}`}
													ref={provided.innerRef}
													{...provided.draggableProps}
													{...provided.dragHandleProps}
												>
													<span className="drag-handle">☰</span>
													<span className="layer-label">{field?.label}</span>

													<div className="size-selector">
														{sizes.map(size => (
															<button
																key={size}
																type="button"
																className={`button button-small ${layer.size === size ? 'active' : ''}`}
																onClick={() => handleSizeChange(layer.id, size)}
															>
																{size[0].toUpperCase()}
															</button>
														))}
													</div>
												</div>
											)}
										</Draggable>
									);
								})}
								{provided.placeholder}
							</div>
						)}
					</Droppable>
				</DragDropContext>
			)}

			{layers.length === 0 && (
				<p className="no-layers">No text layers selected</p>
			)}
		</div>
	);
}
