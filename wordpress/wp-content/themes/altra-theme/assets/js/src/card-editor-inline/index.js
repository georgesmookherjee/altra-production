/**
 * Inline Card Editor
 * Alt + Drag to pan, slider to zoom
 */
import './style.scss';

class InlineCardEditor {
	constructor() {
		this.isActive = false;
		this.editedCards = new Map(); // Track changes: projectId -> {pan, zoom}
		this.init();
	}

	init() {
		this.createToggleButton();
		this.createSaveButton();
	}

	createToggleButton() {
		const button = document.createElement('button');
		button.id = 'altra-card-editor-toggle';
		button.className = 'altra-fixed-button';
		button.innerHTML = `
			<span class="dashicons dashicons-admin-customizer"></span>
			<span class="button-text">Edit Cards</span>
		`;
		button.addEventListener('click', () => this.toggle());
		document.body.appendChild(button);
	}

	createSaveButton() {
		const button = document.createElement('button');
		button.id = 'altra-card-editor-save';
		button.className = 'altra-fixed-button altra-save-button';
		button.innerHTML = `
			<span class="dashicons dashicons-yes"></span>
			<span class="button-text">Save All</span>
		`;
		button.style.display = 'none';
		button.addEventListener('click', () => this.saveAll());
		document.body.appendChild(button);
	}

	toggle() {
		this.isActive = !this.isActive;
		document.body.classList.toggle('altra-card-edit-mode', this.isActive);

		if (this.isActive) {
			this.activate();
		} else {
			this.deactivate();
		}
	}

	activate() {
		const cards = document.querySelectorAll('.project-card');
		cards.forEach(card => {
			this.makeCardEditable(card);
		});

		// Disable project links
		const links = document.querySelectorAll('.project-link');
		links.forEach(link => {
			link.addEventListener('click', this.preventClick);
		});

		document.getElementById('altra-card-editor-save').style.display = 'flex';
	}

	deactivate() {
		// Clean up drag listeners then remove overlays
		document.querySelectorAll('.card-edit-overlay').forEach(el => {
			if (el._cleanup) el._cleanup();
			el.remove();
		});

		// Remove all zoom controls
		document.querySelectorAll('.zoom-control-inline').forEach(el => el.remove());

		// Re-enable project links
		const links = document.querySelectorAll('.project-link');
		links.forEach(link => {
			link.removeEventListener('click', this.preventClick);
		});

		document.getElementById('altra-card-editor-save').style.display = 'none';
	}

	preventClick(e) {
		e.preventDefault();
		e.stopPropagation();
	}

	makeCardEditable(card) {
		const projectId = card.dataset.projectId;
		const imageContainer = card.querySelector('.project-image');
		const img = imageContainer.querySelector('img');
		const videoWrapper = imageContainer.querySelector('.project-video-wrapper');

		// For images use <img>, for videos use the wrapper div as the transform target
		const transformTarget = img || videoWrapper;
		if (!transformTarget) return;

		// Overlay couvrant l'image — ALT+drag pour panner
		const overlay = document.createElement('div');
		overlay.className = 'card-edit-overlay';
		overlay.innerHTML = `<div class="edit-hint">Alt + Glisser pour cadrer</div>`;
		imageContainer.appendChild(overlay);

		// Drag-to-pan state
		let isDragging = false;
		let dragStartX, dragStartY;
		let startPanX, startPanY;

		const onMouseDown = (e) => {
			if (!e.altKey) return;
			e.preventDefault();
			isDragging = true;
			dragStartX = e.clientX;
			dragStartY = e.clientY;
			startPanX = parseFloat(card.dataset.panX) || 0;
			startPanY = parseFloat(card.dataset.panY) || 0;
			overlay.classList.add('is-dragging');
		};

		const onMouseMove = (e) => {
			if (!isDragging) return;
			const dx = e.clientX - dragStartX;
			const dy = e.clientY - dragStartY;
			this.updatePan(card, transformTarget, startPanX + dx, startPanY + dy);
		};

		const onMouseUp = () => {
			if (!isDragging) return;
			isDragging = false;
			overlay.classList.remove('is-dragging');
			this.trackChange(projectId, card);
		};

		overlay.addEventListener('mousedown', onMouseDown);
		document.addEventListener('mousemove', onMouseMove);
		document.addEventListener('mouseup', onMouseUp);

		// Store cleanup so deactivate() can remove global listeners
		overlay._cleanup = () => {
			document.removeEventListener('mousemove', onMouseMove);
			document.removeEventListener('mouseup', onMouseUp);
		};

		// Zoom control — direct child of card (hors .project-link, évite pointer-events:none)
		const zoomControl = document.createElement('div');
		zoomControl.className = 'zoom-control-inline';
		zoomControl.innerHTML = `
			<label>Zoom</label>
			<input type="range" min="0.5" max="2.5" step="0.01" value="${card.dataset.zoom}" class="zoom-slider">
			<span class="zoom-value">${parseFloat(card.dataset.zoom).toFixed(2)}x</span>
			<button type="button" class="center-button">Centrer</button>
		`;
		card.appendChild(zoomControl);

		// Prevent any parent drag handler from stealing slider/button events
		zoomControl.addEventListener('mousedown', (e) => e.stopPropagation());
		zoomControl.addEventListener('pointerdown', (e) => e.stopPropagation());

		const centerButton = zoomControl.querySelector('.center-button');
		centerButton.addEventListener('click', () => {
			this.updatePan(card, transformTarget, 0, 0);
			this.trackChange(projectId, card);
		});

		const zoomSlider = zoomControl.querySelector('.zoom-slider');
		const zoomValue  = zoomControl.querySelector('.zoom-value');

		zoomSlider.addEventListener('input', (e) => {
			const zoom = parseFloat(e.target.value);
			this.updateZoom(card, transformTarget, zoom);
			zoomValue.textContent = zoom.toFixed(2) + 'x';
			this.trackChange(projectId, card);
		});
	}

	// Ne touche qu'au transform — l'image garde width/height/object-fit du CSS.
	// L'élément est 100%×100% → transform-origin 50% 50% = centrage garanti quand pan=0.
	applyTransform(card, target, x, y) {
		const zoom = parseFloat(card.dataset.zoom) || 1;
		const container = target.closest('.project-image');
		if (container) {
			const cW = container.offsetWidth;
			const cH = container.offsetHeight;
			const overflowX = cW * (zoom - 1) / 2;
			const overflowY = cH * (zoom - 1) / 2;
			if (overflowX > 0) x = Math.max(-overflowX, Math.min(overflowX, x));
			if (overflowY > 0) y = Math.max(-overflowY, Math.min(overflowY, y));
		}
		target.style.transformOrigin = '50% 50%';
		target.style.transform = `translate(${x}px, ${y}px) scale(${zoom})`;
		card.dataset.panX = x;
		card.dataset.panY = y;
	}

	updatePan(card, target, x, y) {
		this.applyTransform(card, target, x, y);
	}

	updateZoom(card, target, zoom) {
		card.dataset.zoom = zoom;
		const panX = parseFloat(card.dataset.panX) || 0;
		const panY = parseFloat(card.dataset.panY) || 0;
		this.applyTransform(card, target, panX, panY);
	}

	trackChange(projectId, card) {
		this.editedCards.set(parseInt(projectId), {
			pan: {
				x: parseFloat(card.dataset.panX) || 0,
				y: parseFloat(card.dataset.panY) || 0,
			},
			zoom: parseFloat(card.dataset.zoom)
		});
	}

	async saveAll() {
		if (this.editedCards.size === 0) {
			alert('No changes to save');
			return;
		}

		const saveButton = document.getElementById('altra-card-editor-save');
		saveButton.disabled = true;
		saveButton.querySelector('.button-text').textContent = 'Saving...';

		const promises = [];

		for (const [projectId, settings] of this.editedCards.entries()) {
			const promise = fetch(`/wp-json/altra/v1/project/${projectId}/visual-settings`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.altraCardEditorData?.nonce || '',
				},
				body: JSON.stringify({
					visualSettings: {
						pan: settings.pan,
						zoom: settings.zoom,
						textLayers: []
					}
				}),
			});

			promises.push(promise);
		}

		try {
			await Promise.all(promises);
			alert(`Successfully saved ${this.editedCards.size} card(s)!`);
			this.editedCards.clear();
			window.location.reload();
		} catch (error) {
			console.error('Save error:', error);
			alert('Failed to save some changes. Please try again.');
			saveButton.disabled = false;
			saveButton.querySelector('.button-text').textContent = 'Save All';
		}
	}
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
	if (window.altraCardEditorData) {
		new InlineCardEditor();
	}
});
