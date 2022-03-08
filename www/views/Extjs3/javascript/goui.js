(function() {

	let styleInjected = false;
	const injectGouiStyle = function() {
		if(styleInjected) {
			return;
		}
		var head = document.getElementsByTagName('head')[0];

		var style = document.createElement('link');
		style.href = "./views/Extjs3/goui/goui.css";

		style.type = 'text/css';
		style.rel = 'stylesheet';
		head.append(style);

		styleInjected = true;

	};


	/**
	 *
	 * Load a goui component module into a HTMLElement
	 *
	 * Compiled goui must be present in views/Extjs3/goui
	 *
	 * @param {string} module Must export a single Component
	 * @param {HTMLElement} el
	 * @return {Promise<void>}
	 */
	window.goui = async function(module, el) {
		injectGouiStyle();

		el.classList.add("goui");
		const mods = await import("../../."+module);
		const modName = Object.keys(mods)[0];
		mods[modName].create().render(el);

	}
})();