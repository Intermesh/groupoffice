/**
 * Syncfusion library loader.
 *
 * Dynamically loads the Syncfusion ej2 bundle from either CDN or local lib/ directory.
 *
 * CDN mode: loads the combined ej2.min.js + ej2.min.css from the Syncfusion CDN
 * Local mode: loads ej2.min.js + ej2.min.css from the module's lib/ directory
 */
(function () {

	var loaded = false;
	var loading = false;
	var pendingCallbacks = [];

	function getSettings() {
		var mod = go.Modules.get('community', 'syncfusion');
		return mod ? mod.settings : {};
	}

	function getCdnBase() {
		var url = getSettings().cdnUrl || 'https://cdn.syncfusion.com/ej2/32.1.19/';
		return url.replace(/\/?$/, '/');
	}

	function getLocalBase() {
		return BaseHref + 'go/modules/community/syncfusion/lib/';
	}

	function getJsUrl() {
		if (getSettings().librarySource === 'local') {
			return getLocalBase() + 'ej2.min.js';
		}
		return getCdnBase() + 'dist/ej2.min.js';
	}

	function getCssUrl() {
		if (getSettings().librarySource === 'local') {
			return getLocalBase() + 'ej2.min.css';
		}
		return getCdnBase() + 'material.css';
	}

	function loadCss(url) {
		var link = document.createElement('link');
		link.rel = 'stylesheet';
		link.href = url;
		document.head.appendChild(link);
	}

	function registerLicense() {
		if (window.ej && ej.base && ej.base.registerLicense) {
			var key = getSettings().licenseKey;
			if (key) {
				ej.base.registerLicense(key);
			}
		}
	}

	function fireCallbacks(error) {
		var cbs = pendingCallbacks.slice();
		pendingCallbacks = [];
		cbs.forEach(function (cb) {
			cb(error || null);
		});
	}

	/**
	 * Public API: load Syncfusion library, then call callback.
	 *
	 * @param {String} editorType 'document' or 'spreadsheet' (unused, full bundle loaded)
	 * @param {Function} callback Called when the library is ready. Receives an
	 *		Error as first argument when loading failed, null on success.
	 */
	go.modules.community.syncfusion.loadLibrary = function (editorType, callback) {
		if (loaded) {
			callback(null);
			return;
		}

		pendingCallbacks.push(callback);

		if (loading) {
			return;
		}
		loading = true;

		loadCss(getCssUrl());

		// Ext.Loader has no error handling — a failed CDN load would leave
		// every caller queued forever. Use a plain script tag instead.
		var script = document.createElement('script');
		script.src = getJsUrl();
		script.onload = function () {
			registerLicense();
			loaded = true;
			loading = false;
			fireCallbacks(null);
		};
		script.onerror = function () {
			loading = false;
			fireCallbacks(new Error("Failed to load the Syncfusion library from " + script.src));
		};
		document.head.appendChild(script);
	};

})();
