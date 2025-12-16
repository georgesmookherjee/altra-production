/**
 * Inline Card Editor
 * InDesign-style editing: hover on images to edit focal point and zoom
 */
import './style.scss';

class InlineCardEditor {
	constructor() {
		this.isActive = false;
		this.editedCards = new Map(); // Track changes: projectId -> {focalPoint, zoom}
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
		// Remove all overlays
		document.querySelectorAll('.card-edit-overlay').forEach(el => el.remove());

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

		if (!img) return;

		// Create overlay
		const overlay = document.createElement('div');
		overlay.className = 'card-edit-overlay';
		overlay.innerHTML = `
			<div class="edit-controls">
				<div class="focal-point-picker">
					<div class="focal-point-crosshair"></div>
				</div>
				<div class="zoom-control">
					<label>Zoom</label>
					<input type="range" min="0.5" max="2.5" step="0.01" value="${card.dataset.zoom}" class="zoom-slider">
					<span class="zoom-value">${parseFloat(card.dataset.zoom).toFixed(2)}x</span>
				</div>
			</div>
		`;

		imageContainer.appendChild(overlay);

		// Setup focal point picker
		const picker = overlay.querySelector('.focal-point-picker');
		const crosshair = overlay.querySelector('.focal-point-crosshair');

		// Position crosshair based on current focal point
		this.updateCrosshairPosition(crosshair, card.dataset.focalX, card.dataset.focalY);

		picker.addEventListener('click', (e) => {
			const rect = picker.getBoundingClientRect();
			const x = ((e.clientX - rect.left) / rect.width) * 100;
			const y = ((e.clientY - rect.top) / rect.height) * 100;

			this.updateFocalPoint(card, img, x, y);
			this.updateCrosshairPosition(crosshair, x, y);
			this.trackChange(projectId, card);
		});

		// Setup zoom control
		const zoomSlider = overlay.querySelector('.zoom-slider');
		const zoomValue = overlay.querySelector('.zoom-value');

		zoomSlider.addEventListener('input', (e) => {
			const zoom = parseFloat(e.target.value);
			this.updateZoom(card, img, zoom);
			zoomValue.textContent = zoom.toFixed(2) + 'x';
			this.trackChange(projectId, card);
		});
	}

	updateCrosshairPosition(crosshair, x, y) {
		crosshair.style.left = x + '%';
		crosshair.style.top = y + '%';
	}

	updateFocalPoint(card, img, x, y) {
		card.dataset.focalX = x;
		card.dataset.focalY = y;
		img.style.transformOrigin = `${x}% ${y}%`;
	}

	updateZoom(card, img, zoom) {
		card.dataset.zoom = zoom;
		const currentTransform = img.style.transform || '';
		const scaleRegex = /scale\([^)]+\)/;

		if (scaleRegex.test(currentTransform)) {
			img.style.transform = currentTransform.replace(scaleRegex, `scale(${zoom})`);
		} else {
			img.style.transform = `scale(${zoom})`;
		}
	}

	trackChange(projectId, card) {
		this.editedCards.set(parseInt(projectId), {
			focalPoint: {
				x: parseFloat(card.dataset.focalX),
				y: parseFloat(card.dataset.focalY)
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
						focalPoint: settings.focalPoint,
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
