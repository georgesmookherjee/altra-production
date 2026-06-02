/**
 * Inline Card Editor — Alt+Drag to pan, slider to zoom
 * Uses focal point (0-100 %) for resolution-independent positioning.
 * Transform formula: translate(tx, ty) scale(zoom) with transformOrigin 50% 50%
 * — mirrors applyCardZoom() in main.js exactly.
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

		// Drag state — startFocal captured at mousedown, delta accumulated from start
		let isDragging = false;
		let dragStartX, dragStartY, startFocalX, startFocalY;

		const onMouseDown = (e) => {
			if (!e.altKey) return;
			e.preventDefault();
			isDragging = true;
			dragStartX  = e.clientX;
			dragStartY  = e.clientY;
			startFocalX = parseFloat(card.dataset.focalX) || 50;
			startFocalY = parseFloat(card.dataset.focalY) || 50;
			overlay.classList.add('is-dragging');
		};

		const onMouseMove = (e) => {
			if (!isDragging || !img || !img.naturalWidth) return;
			const zoom      = Math.max(1.0, parseFloat(card.dataset.zoom) || 1.0);
			const container = img.closest('.project-image');
			const cs        = Math.max(container.offsetWidth / img.naturalWidth, container.offsetHeight / img.naturalHeight);
			const iW        = img.naturalWidth  * cs;
			const iH        = img.naturalHeight * cs;
			// Dragging right shifts image right → focal point (what's centered) moves left
			// 1 px screen drag = 1/(iW*zoom) focal fraction change
			const newFocalX = startFocalX - (e.clientX - dragStartX) / (iW * zoom) * 100;
			const newFocalY = startFocalY - (e.clientY - dragStartY) / (iH * zoom) * 100;
			this.applyTransform(card, transformTarget, newFocalX, newFocalY);
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
			this.applyTransform(card, transformTarget,
				parseFloat(card.dataset.focalX) || 50,
				parseFloat(card.dataset.focalY) || 50);
			this.trackChange(projectId, card);
		});

		zoomControl.querySelector('.center-button').addEventListener('click', () => {
			this.applyTransform(card, transformTarget, 50, 50);
			this.trackChange(projectId, card);
		});

		// Apply initial transform if the card already has focal settings
		if (card.dataset.hasVisualSettings === '1') {
			const fx = parseFloat(card.dataset.focalX) || 50;
			const fy = parseFloat(card.dataset.focalY) || 50;
			this.applyTransform(card, transformTarget, fx, fy);
		}
	}

	/**
	 * Apply focal point + zoom transform.
	 * focalX/Y: 0-100 % (unclamped on input; clamped internally).
	 * Formula mirrors applyCardZoom() in main.js:
	 *   translate(tx, ty) scale(zoom), transformOrigin 50% 50%
	 *   where tx = (cW-iW)/2 - iW*zoom*(fx-0.5)  (iW = cover width, no zoom factor)
	 */
	applyTransform(card, target, focalX, focalY) {
		const zoom = Math.max(1.0, parseFloat(card.dataset.zoom) || 1.0);

		if (target.tagName === 'IMG' && target.naturalWidth) {
			const container = target.closest('.project-image');
			const cW = container.offsetWidth;
			const cH = container.offsetHeight;
			const nW = target.naturalWidth;
			const nH = target.naturalHeight;
			if (!cW || !cH) return;

			const cs = Math.max(cW / nW, cH / nH);
			const iW = nW * cs;  // cover size (zoom applied via scale() in transform)
			const iH = nH * cs;

			const fx = focalX / 100;  // 0-1
			const fy = focalY / 100;

			// Ideal translate to center the focal point
			const txIdeal = (cW - iW) / 2 - iW * zoom * (fx - 0.5);
			const tyIdeal = (cH - iH) / 2 - iH * zoom * (fy - 0.5);

			// Clamp so the (scaled) image always covers the container
			const tx = Math.max(cW - (1 + zoom) * iW / 2, Math.min((zoom - 1) * iW / 2, txIdeal));
			const ty = Math.max(cH - (1 + zoom) * iH / 2, Math.min((zoom - 1) * iH / 2, tyIdeal));

			// Write clamped focal back to dataset (for trackChange to read correct value)
			card.dataset.focalX = (0.5 - (tx - (cW - iW) / 2) / (iW * zoom)) * 100;
			card.dataset.focalY = (0.5 - (ty - (cH - iH) / 2) / (iH * zoom)) * 100;

			// Pas de object-fit:none — Safari rend l'image à sa taille naturelle avec none.
			// object-fit:cover (CSS) + dimensions cover-scale = image remplit le box sans distorsion.
			target.style.position       = 'absolute';
			target.style.width          = iW + 'px';
			target.style.height         = iH + 'px';
			target.style.top            = '0';
			target.style.left           = '0';
			target.style.right          = '';
			target.style.bottom         = '';
			target.style.margin         = '0';
			target.style.transformOrigin = '50% 50%';
			target.style.transform      = `translate(${tx}px, ${ty}px) scale(${zoom})`;
		} else if (target) {
			// Video wrapper — transform-origin approach
			const fx = Math.max(0, Math.min(100, focalX));
			const fy = Math.max(0, Math.min(100, focalY));
			card.dataset.focalX = fx;
			card.dataset.focalY = fy;
			target.style.transformOrigin = `${fx}% ${fy}%`;
			target.style.transform       = `scale(${zoom})`;
		}
	}

	trackChange(projectId, card) {
		this.editedCards.set(parseInt(projectId), {
			focalPoint: {
				x: parseFloat(card.dataset.focalX) || 50,
				y: parseFloat(card.dataset.focalY) || 50,
			},
			zoom: parseFloat(card.dataset.zoom) || 1.0,
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
				body: JSON.stringify({ visualSettings: { focalPoint: settings.focalPoint, zoom: settings.zoom, textLayers: [] } }),
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
