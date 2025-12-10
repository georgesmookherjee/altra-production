/**
 * API utilities for Grid Manager
 * Handles REST API calls to WordPress backend
 */

/**
 * Get all projects with grid data
 * @returns {Promise<Array>} Array of project objects
 */
export async function fetchProjects() {
	const response = await fetch('/wp-json/altra/v1/projects', {
		method: 'GET',
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': window.altraGridData?.nonce || '',
		},
		credentials: 'same-origin',
	});

	if (!response.ok) {
		throw new Error(`Failed to fetch projects: ${response.statusText}`);
	}

	return response.json();
}

/**
 * Save grid positions for all projects
 * @param {Array} positions - Array of position objects {id, x, y, w, h, order}
 * @returns {Promise<Object>} Response object
 */
export async function saveGridPositions(positions) {
	const response = await fetch('/wp-json/altra/v1/grid-positions', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': window.altraGridData?.nonce || '',
		},
		credentials: 'same-origin',
		body: JSON.stringify({ positions }),
	});

	if (!response.ok) {
		const error = await response.json();
		throw new Error(error.message || `Failed to save grid positions: ${response.statusText}`);
	}

	return response.json();
}
