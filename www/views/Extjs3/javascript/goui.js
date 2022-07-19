
(function() {

	let styleInjected = false;
	const injectGouiStyle = async function() {
		if(styleInjected) {
			return;
		}

		//inject css stylesheet
		var head = document.getElementsByTagName('head')[0];

		var style = document.createElement('link');
		style.href = "./views/Extjs3/goui/style/goui.css";

		style.type = 'text/css';
		style.rel = 'stylesheet';
		head.append(style);

		styleInjected = true;


		//create root div for holding goui windows, menu's, alerts etc.
		const div = document.createElement("div");
		div.id="goui";
		div.classList.add("goui");
		document.body.appendChild(div);
		//
		// const mods = await import("../goui/component/Root.js");
		// mods.root.setEl(div);
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
		//add class to apply goui style to chidren
		// el.id = "goui";
		el.classList.add("goui");
		//const rootMods = await import("../build/goui.js");

		const clientMods = await import("../goui/build/goui.js");

		clientMods.client.uri = BaseHref + "api/";

		clientMods.client.session = Ext.apply(go.User.session, {accessToken: go.User.accessToken});

		//load component module
		const mods = await import("../../."+module);
		const modName = Object.keys(mods)[0];

		mods[modName].render(el);

		//render first export to given el
		//rootMods.root.getItems().add(mods[modName]);

	}
})();