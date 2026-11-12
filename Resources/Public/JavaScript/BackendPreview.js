/*
 * Klimadashboard TYPO3 extension: live preview in the editing form.
 *
 * Rebuilds the preview iframe URL while the editor changes the chart fields, mirroring
 * Klimadashboard\Typo3\Service\EmbedUrlBuilder. Sends nothing, stores nothing.
 *
 * UMD: loaded through RequireJS on TYPO3 v11 and as an ES module on v12+.
 */
(function (factory) {
	'use strict';
	if (typeof define === 'function' && define.amd) {
		define([], factory);
	} else {
		factory();
	}
})(function () {
	'use strict';

	var CONTAINER = '[data-klimadashboard-preview]';
	var FIELDS = {
		site: 'tx_klimadashboard_site',
		chart: 'tx_klimadashboard_chart',
		language: 'tx_klimadashboard_language',
		region: 'tx_klimadashboard_region',
		regionCustom: 'tx_klimadashboard_region_custom',
		skin: 'tx_klimadashboard_skin',
		hidetext: 'tx_klimadashboard_hidetext',
		height: 'tx_klimadashboard_height',
		options: 'tx_klimadashboard_options'
	};

	function parseJson(value, fallback) {
		try {
			return JSON.parse(value);
		} catch (e) {
			return fallback;
		}
	}

	/* FormEngine keeps the committed value in a hidden input next to the visible one. */
	function readValue(name) {
		var nodes = document.querySelectorAll('[name="' + name + '"]');
		var chosen = null;
		for (var i = 0; i < nodes.length; i++) {
			if (!chosen || nodes[i].type === 'hidden') {
				chosen = nodes[i];
			}
		}
		if (!chosen) {
			return '';
		}
		if (chosen.type === 'checkbox') {
			return chosen.checked ? chosen.value : '';
		}
		return chosen.value || '';
	}

	function readOptions(prefix) {
		var start = prefix + '[' + FIELDS.options + '][';
		var result = {};
		var nodes = document.querySelectorAll('[name^="' + start + '"]');
		for (var i = 0; i < nodes.length; i++) {
			var name = nodes[i].getAttribute('name') || '';
			var key = name.slice(start.length).replace(/\]$/, '');
			if (key && key !== '_' && nodes[i].value) {
				result[key] = nodes[i].value;
			}
		}
		return result;
	}

	function buildUrl(container) {
		var prefix = container.getAttribute('data-klimadashboard-prefix') || '';
		var sites = parseJson(container.getAttribute('data-klimadashboard-sites') || '{}', {});
		var defaultSite = container.getAttribute('data-klimadashboard-default-site') || 'de';
		var pageLanguage = container.getAttribute('data-klimadashboard-language') || 'de';
		var reserved = (container.getAttribute('data-klimadashboard-reserved') || '').split(',');
		var field = function (key) {
			return readValue(prefix + '[' + FIELDS[key] + ']');
		};

		var chart = field('chart');
		if (!/^[0-9a-zA-Z-]+$/.test(chart)) {
			return '';
		}
		var site = sites[field('site')] ? field('site') : defaultSite;
		var base = sites[site] || sites[defaultSite] || '';
		if (!base) {
			return '';
		}

		// No "auto": the preview uses a fixed height so the embed does not post its height
		// to the window, which FormEngine rejects with an uncaught error.
		var params = new URLSearchParams();
		var region = (field('regionCustom') || '').trim() || field('region');
		if (/^[0-9a-zA-Z-]+$/.test(region)) {
			params.set('region', region);
		}
		params.set('lang', (field('language') || pageLanguage) === 'en' ? 'en' : 'de');
		var skin = field('skin');
		if (/^[a-z0-9-]+$/.test(skin)) {
			params.set('skin', skin);
		}
		if (field('hidetext') && field('hidetext') !== '0') {
			params.set('showtext', 'false');
		}
		var options = readOptions(prefix);
		for (var key in options) {
			if (Object.prototype.hasOwnProperty.call(options, key)
				&& /^[a-zA-Z0-9_-]+$/.test(key)
				&& reserved.indexOf(key) === -1) {
				params.set(key, options[key]);
			}
		}

		return base + '/embed/' + encodeURIComponent(chart) + '?' + params.toString();
	}

	function refresh(container) {
		var frame = container.querySelector('[data-klimadashboard-preview-frame]');
		var empty = container.querySelector('[data-klimadashboard-preview-empty]');
		var open = container.querySelector('[data-klimadashboard-preview-open]');
		if (!frame) {
			return;
		}
		var url = buildUrl(container);
		var hasUrl = url !== '';

		if (hasUrl && frame.getAttribute('src') !== url) {
			frame.setAttribute('src', url);
		}
		frame.hidden = !hasUrl;
		if (empty) {
			empty.hidden = hasUrl;
		}
		if (open) {
			open.hidden = !hasUrl;
			open.setAttribute('href', hasUrl ? url : '#');
		}

		var fallback = parseInt(container.getAttribute('data-klimadashboard-preview-height'), 10) || 500;
		var height = parseInt(readValue((container.getAttribute('data-klimadashboard-prefix') || '')
			+ '[' + FIELDS.height + ']'), 10) || 0;
		frame.style.height = (height > 0 ? height : fallback) + 'px';
	}

	function refreshAll() {
		var containers = document.querySelectorAll(CONTAINER);
		for (var i = 0; i < containers.length; i++) {
			refresh(containers[i]);
		}
	}

	var timer = null;
	function scheduleRefresh() {
		if (!document.querySelector(CONTAINER)) {
			return;
		}
		window.clearTimeout(timer);
		timer = window.setTimeout(refreshAll, 300);
	}

	document.addEventListener('change', scheduleRefresh);
	document.addEventListener('input', scheduleRefresh);

	document.addEventListener('click', function (event) {
		var button = event.target.closest ? event.target.closest('[data-klimadashboard-preview-reload]') : null;
		if (!button) {
			return;
		}
		var container = button.closest(CONTAINER);
		if (!container) {
			return;
		}
		var frame = container.querySelector('[data-klimadashboard-preview-frame]');
		if (frame) {
			// Force a reload even when the URL did not change
			frame.setAttribute('src', 'about:blank');
		}
		window.setTimeout(function () {
			refresh(container);
		}, 50);
	});

	refreshAll();
});
