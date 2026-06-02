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

		// Drag-to-pan state (pan stored as fraction of overflow, screen-size-independent)
		let isDragging = false;
		let dragStartX, dragStartY;
		let startPanNormX, startPanNormY;

		const onMouseDown = (e) => {
			if (!e.altKey) return;
			e.preventDefault();
			isDragging = true;
			dragStartX = e.clientX;
			dragStartY = e.clientY;
			startPanNormX = parseFloat(card.dataset.panX) || 0;
			startPanNormY = parseFloat(card.dataset.panY) || 0;
			// Legacy detection
			if (Math.abs(startPanNormX) > 1.5) startPanNormX = 0;
			if (Math.abs(startPanNormY) > 1.5) startPanNormY = 0;
			overlay.classList.add('is-dragging');
		};

		const onMouseMove = (e) => {
			if (!isDragging) return;
			const dx = e.clientX - dragStartX;
			const dy = e.clientY - dragStartY;
			// Convert pixel delta → normalized fraction using current overflow
			this.updatePanFromDelta(card, transformTarget, startPanNormX, startPanNormY, dx, dy);
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
			<input type="range" min="1.0" max="2.5" step="0.01" value="${Math.max(1.0, parseFloat(card.dataset.zoom) || 1.0)}" class="zoom-slider">
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

	// normX, normY = fractions [-1,1] de l'overflow — identiques visuellement à toute résolution
	applyTransform(card, target, normX, normY) {
		const zoom = Math.max(1.0, parseFloat(card.dataset.zoom) || 1);
		// Clamp to [-1, 1]
		normX = Math.max(-1, Math.min(1, normX));
		normY = Math.max(-1, Math.min(1, normY));
		if (target.tagName === 'IMG' && target.naturalWidth) {
			const container = target.closest('.project-image');
			const cW = container.offsetWidth;
			const cH = container.offsetHeight;
			const nW = target.naturalWidth;
			const nH = target.naturalHeight;
			const cs = Math.max(cW / nW, cH / nH);
			const iW = nW * cs;
			const iH = nH * cs;
			const overflowX = (iW * zoom - cW) / 2;
			const overflowY = (iH * zoom - cH) / 2;
			const panX = normX * overflowX;
			const panY = normY * overflowY;
			const centerTx = (cW - iW) / 2 + panX;
			const centerTy = (cH - iH) / 2 + panY;
			target.style.objectFit      = 'none';
			target.style.position       = 'absolute';
			target.style.width          = iW + 'px';
			target.style.height         = iH + 'px';
			target.style.top            = '0';
			target.style.left           = '0';
			target.style.right          = '';
			target.style.bottom         = '';
			target.style.margin         = '0';
			target.style.transformOrigin = '50% 50%';
			target.style.transform = `translate(${centerTx}px, ${centerTy}px) scale(${zoom})`;
		} else {
			target.style.transformOrigin = '50% 50%';
			target.style.transform = `translate(0px, 0px) scale(${zoom})`;
		}
		// Store normalized values
		card.dataset.panX = normX;
		card.dataset.panY = normY;
	}

	// x, y = normalized fractions [-1, 1] of overflow
	updatePan(card, target, normX, normY) {
		this.applyTransform(card, target, normX, normY);
	}

	// startNormX/Y = normalized fraction at drag start; dx/dy = pixel delta from mouse
	updatePanFromDelta(card, target, startNormX, startNormY, dx, dy) {
		const zoom = Math.max(1.0, parseFloat(card.dataset.zoom) || 1);
		const container = target.closest('.project-image');
		if (!container || !target.naturalWidth) return;
		const cW = container.offsetWidth;
		const cH = container.offsetHeight;
		const cs = Math.max(cW / target.naturalWidth, cH / target.naturalHeight);
		const iW = target.naturalWidth * cs;
		const iH = target.naturalHeight * cs;
		const overflowX = (iW * zoom - cW) / 2;
		const overflowY = (iH * zoom - cH) / 2;
		// Convert pixel delta to normalized fraction and add to start
		const normX = overflowX > 0 ? startNormX + dx / overflowX : 0;
		const normY = overflowY > 0 ? startNormY + dy / overflowY : 0;
		this.applyTransform(card, target, normX, normY);
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
