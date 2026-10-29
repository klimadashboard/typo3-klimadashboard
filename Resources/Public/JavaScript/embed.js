/*
 * Klimadashboard TYPO3 extension: auto-height and click-to-load for embedded charts.
 * No dependencies, no cookies, no requests of its own.
 */
(function () {
	'use strict';

	var CONTAINER = '.klimadashboard-embed';

	function isAllowedOrigin(frame, origin) {
		var container = frame.closest(CONTAINER);
		var origins = container ? (container.getAttribute('data-klimadashboard-origins') || '').split(' ') : [];
		return origins.indexOf(origin) !== -1;
	}

	// Embeds opened with ?auto=true report their content height via postMessage
	window.addEventListener('message', function (event) {
		var data = event.data;
		if (!data || data.type !== 'klimadashboard-resize' || typeof data.height !== 'number') return;

		var frames = document.querySelectorAll('iframe[data-klimadashboard-auto]');
		for (var i = 0; i < frames.length; i++) {
			var frame = frames[i];
			if (frame.contentWindow === event.source && isAllowedOrigin(frame, event.origin)) {
				var height = Math.min(Math.max(Math.ceil(data.height), 100), 10000);
				frame.style.height = height + 'px';
				return;
			}
		}
	});

	// Optional two-click mode: load the iframe only after the visitor agreed
	document.addEventListener('click', function (event) {
		var button = event.target.closest ? event.target.closest('[data-klimadashboard-load]') : null;
		if (!button) return;

		var container = button.closest(CONTAINER);
		var template = container && container.querySelector('template[data-klimadashboard-template]');
		var consent = container && container.querySelector('[data-klimadashboard-consent]');
		if (!template || !consent) return;

		container.replaceChild(template.content.cloneNode(true), consent);
		template.parentNode.removeChild(template);
		var frame = container.querySelector('iframe');
		if (frame) {
			frame.setAttribute('tabindex', '-1');
			frame.focus();
		}
	});
})();
