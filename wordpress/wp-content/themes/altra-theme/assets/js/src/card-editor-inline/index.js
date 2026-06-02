/**
 * Inline Card Editor — Alt+Drag to pan, slider to zoom
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
		if (this.isActive) { this.activate(); } else { this.deactivate(); }
	}

	activate() {
		document.querySelectorAll('.project-card').forEach(card => this.makeCardEditable(card));
		document.querySelectorAll('.project-link').forEach(link => link.addEventListener('click', this.preventClick));
		document.getElementById('altra-card-editor-save').style.display = 'flex';
	}

	deactivate() {
		document.querySelectorAll('.card-edit-overlay').forEach(el => { if (el._cleanup) el._cleanup(); el.remove(); });
		document.querySelectorAll('.zoom-control-inline').forEach(el => el.remove());
		document.querySelectorAll('.project-link').forEach(link => link.removeEventListener('click', this.preventClick));
		document.getElementById('altra-card-editor-save').style.display = 'none';
	}

	preventClick(e) { e.preventDefault(); e.stopPropagation(); }

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

		// Drag state
		let isDragging = false;
		let dragStartX, dragStartY, startPanX, startPanY;

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
			this.applyTransform(card, transformTarget,
				startPanX + (e.clientX - dragStartX),
				startPanY + (e.clientY - dragStartY));
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
		const currentZoom = Math.max(1.0, parseFloat(card.dataset.zoom) || 1.0);
		const zoomControl = document.createElement('div');
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

		zoomControl.querySelector('.zoom-slider').addEventListener('input', (e) => {
			card.dataset.zoom = e.target.value;
			zoomControl.querySelector('.zoom-value').textContent = parseFloat(e.target.value).toFixed(2) + 'x';
			this.applyTransform(card, transformTarget, parseFloat(card.dataset.panX) || 0, parseFloat(card.dataset.panY) || 0);
			this.trackChange(projectId, card);
		});

		zoomControl.querySelector('.center-button').addEventListener('click', () => {
			this.applyTransform(card, transformTarget, 0, 0);
			this.trackChange(projectId, card);
		});

		// Apply initial transform if card already has visual settings
		if (card.dataset.hasVisualSettings === '1') {
			this.applyTransform(card, transformTarget, parseFloat(card.dataset.panX) || 0, parseFloat(card.dataset.panY) || 0);
		}
	}

	applyTransform(card, target, panX, panY) {
		const zoom = Math.max(1.0, parseFloat(card.dataset.zoom) || 1.0);

		if (target.tagName === 'IMG' && target.naturalWidth) {
			const container = target.closest('.project-image');
			const cW = container.offsetWidth;
			const cH = container.offsetHeight;
			const cs = Math.max(cW / target.naturalWidth, cH / target.naturalHeight);
			const iW = target.naturalWidth * cs;
			const iH = target.naturalHeight * cs;

			const overflowX = (iW * zoom - cW) / 2;
			const overflowY = (iH * zoom - cH) / 2;
			if (overflowX > 0) panX = Math.max(-overflowX, Math.min(overflowX, panX)); else panX = 0;
			if (overflowY > 0) panY = Math.max(-overflowY, Math.min(overflowY, panY)); else panY = 0;

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
			target.style.transform      = `translate(${centerTx}px, ${centerTy}px) scale(${zoom})`;
		} else {
			target.style.transformOrigin = '50% 50%';
			target.style.transform      = `scale(${zoom})`;
		}

		card.dataset.panX = panX;
		card.dataset.panY = panY;
	}

	trackChange(projectId, card) {
		this.editedCards.set(parseInt(projectId), {
			pan:  { x: parseFloat(card.dataset.panX) || 0, y: parseFloat(card.dataset.panY) || 0 },
			zoom: parseFloat(card.dataset.zoom) || 1.0
		});
	}

	async saveAll() {
		if (this.editedCards.size === 0) { alert('No changes to save'); return; }

		const saveButton = document.getElementById('altra-card-editor-save');
		saveButton.disabled = true;
		saveButton.querySelector('.button-text').textContent = 'Saving...';

		const promises = [];
		for (const [projectId, settings] of this.editedCards.entries()) {
			promises.push(fetch(`/wp-json/altra/v1/project/${projectId}/visual-settings`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.altraCardEditorData?.nonce || '' },
				body: JSON.stringify({ visualSettings: { pan: settings.pan, zoom: settings.zoom, textLayers: [] } }),
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
	if (window.altraCardEditorData) new InlineCardEditor();
});
