/**
 * Inline Card Editor
 * Alt + Drag to set focal point, slider to zoom in
 */
import './style.scss';

class InlineCardEditor {
	constructor() {
		this.isActive = false;
		this.editedCards = new Map();
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
		cards.forEach(card => this.makeCardEditable(card));

		const links = document.querySelectorAll('.project-link');
		links.forEach(link => link.addEventListener('click', this.preventClick));

		document.getElementById('altra-card-editor-save').style.display = 'flex';
	}

	deactivate() {
		document.querySelectorAll('.card-edit-overlay').forEach(el => {
			if (el._cleanup) el._cleanup();
			el.remove();
		});
		document.querySelectorAll('.zoom-control-inline').forEach(el => el.remove());

		const links = document.querySelectorAll('.project-link');
		links.forEach(link => link.removeEventListener('click', this.preventClick));

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

		const transformTarget = img || videoWrapper;
		if (!transformTarget) return;

		const overlay = document.createElement('div');
		overlay.className = 'card-edit-overlay';
		overlay.innerHTML = `<div class="edit-hint">Alt + Glisser pour cadrer</div>`;
		imageContainer.appendChild(overlay);

		// Drag state — focal point [0,1]
		let isDragging = false;
		let dragStartX, dragStartY;
		let startFpX, startFpY;

		const onMouseDown = (e) => {
			if (!e.altKey) return;
			e.preventDefault();
			isDragging = true;
			dragStartX = e.clientX;
			dragStartY = e.clientY;
			startFpX = parseFloat(card.dataset.focalX) || 0.5;
			startFpY = parseFloat(card.dataset.focalY) || 0.5;
			overlay.classList.add('is-dragging');
		};

		const onMouseMove = (e) => {
			if (!isDragging) return;
			const dx = e.clientX - dragStartX;
			const dy = e.clientY - dragStartY;
			this.dragFocalPoint(card, transformTarget, startFpX, startFpY, dx, dy);
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
		overlay._cleanup = () => {
			document.removeEventListener('mousemove', onMouseMove);
			document.removeEventListener('mouseup', onMouseUp);
		};

		// Zoom control
		const zoomControl = document.createElement('div');
		const currentZoom = Math.max(1.0, parseFloat(card.dataset.zoom) || 1.0);
		zoomControl.className = 'zoom-control-inline';
		zoomControl.innerHTML = `
			<label>Zoom</label>
			<input type="range" min="1.0" max="2.5" step="0.01" value="${currentZoom}" class="zoom-slider">
			<span class="zoom-value">${currentZoom.toFixed(2)}x</span>
			<button type="button" class="center-button">Centrer</button>
		`;
		card.appendChild(zoomControl);

		zoomControl.addEventListener('mousedown', (e) => e.stopPropagation());
		zoomControl.addEventListener('pointerdown', (e) => e.stopPropagation());

		const zoomSlider = zoomControl.querySelector('.zoom-slider');
		const zoomValue  = zoomControl.querySelector('.zoom-value');

		zoomSlider.addEventListener('input', (e) => {
			const zoom = parseFloat(e.target.value);
			card.dataset.zoom = zoom;
			this.applyTransform(card, transformTarget);
			zoomValue.textContent = zoom.toFixed(2) + 'x';
			this.trackChange(projectId, card);
		});

		const centerButton = zoomControl.querySelector('.center-button');
		centerButton.addEventListener('click', () => {
			card.dataset.focalX = 0.5;
			card.dataset.focalY = 0.5;
			this.applyTransform(card, transformTarget);
			this.trackChange(projectId, card);
		});

		// Apply initial transform
		this.applyTransform(card, transformTarget);
	}

	// Drag focal point: dx/dy in screen pixels
	dragFocalPoint(card, target, startFpX, startFpY, dx, dy) {
		if (target.tagName !== 'IMG' || !target.naturalWidth) return;
		const container = target.closest('.project-image');
		const cW = container.offsetWidth;
		const cH = container.offsetHeight;
		const zoom = Math.max(1.0, parseFloat(card.dataset.zoom) || 1);
		const cs = Math.max(cW / target.naturalWidth, cH / target.naturalHeight);
		const iW = target.naturalWidth * cs;
		const iH = target.naturalHeight * cs;

		// Drag right (dx>0) = image moves right = reveals left side = fpX decreases
		// Sensitivity: 1 pixel drag = 1/(iW-cW) focal fraction change
		const overflowX = iW - cW;
		const overflowY = iH - cH;
		const newFpX = overflowX > 0 ? startFpX - dx / overflowX : 0.5;
		const newFpY = overflowY > 0 ? startFpY - dy / overflowY : 0.5;

		card.dataset.focalX = Math.max(0, Math.min(1, newFpX));
		card.dataset.focalY = Math.max(0, Math.min(1, newFpY));
		this.applyTransform(card, target);
	}

	// Apply transform from card's current focalX/Y and zoom data attributes
	applyTransform(card, target) {
		const fpX  = Math.max(0, Math.min(1, parseFloat(card.dataset.focalX) || 0.5));
		const fpY  = Math.max(0, Math.min(1, parseFloat(card.dataset.focalY) || 0.5));
		const zoom = Math.max(1.0, parseFloat(card.dataset.zoom) || 1.0);

		if (target.tagName === 'IMG' && target.naturalWidth) {
			const container = target.closest('.project-image');
			const cW = container.offsetWidth;
			const cH = container.offsetHeight;
			const cs = Math.max(cW / target.naturalWidth, cH / target.naturalHeight);
			const iW = target.naturalWidth * cs;
			const iH = target.naturalHeight * cs;

			const centerTx = fpX * (cW - iW);
			const centerTy = fpY * (cH - iH);

			target.style.objectFit      = 'none';
			target.style.position       = 'absolute';
			target.style.width          = iW + 'px';
			target.style.height         = iH + 'px';
			target.style.top            = '0';
			target.style.left           = '0';
			target.style.right          = '';
			target.style.bottom         = '';
			target.style.margin         = '0';
			target.style.transformOrigin = `${fpX * 100}% ${fpY * 100}%`;
			target.style.transform      = `translate(${centerTx}px, ${centerTy}px) scale(${zoom})`;
		} else {
			target.style.transformOrigin = '50% 50%';
			target.style.transform      = `scale(${zoom})`;
		}
	}

	updateZoom(card, target, zoom) {
		card.dataset.zoom = zoom;
		this.applyTransform(card, target);
	}

	trackChange(projectId, card) {
		this.editedCards.set(parseInt(projectId), {
			focalPoint: {
				x: parseFloat(card.dataset.focalX) || 0.5,
				y: parseFloat(card.dataset.focalY) || 0.5,
			},
			zoom: parseFloat(card.dataset.zoom) || 1.0
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
			promises.push(fetch(`/wp-json/altra/v1/project/${projectId}/visual-settings`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.altraCardEditorData?.nonce || '',
				},
				body: JSON.stringify({
					visualSettings: {
						focalPoint: settings.focalPoint,
						zoom: settings.zoom,
						textLayers: []
					}
				}),
			}));
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

document.addEventListener('DOMContentLoaded', () => {
	if (window.altraCardEditorData) {
		new InlineCardEditor();
	}
});
