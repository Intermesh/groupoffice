import {client} from "./goui/lib/api/Client.js";

client.uri = document.location.origin + "/api/";



//testing

import (BaseHref + "views/Extjs3/javascript/goui/lib/api/Client.js").then((mods) => {
	this.client = mods.client;

	this.client.session = go.User.session;
	this.client.session.accessToken = go.User.accessToken;
	this.client.uri = document.location.origin + "/api/";

	import (BaseHref + "views/Extjs3/javascript/goui/lib/component/Component.js").then((mods) => {
		// mods.Image.replaceImages(readMore.getComponent("content").getEl().dom);


		const cmp = mods.Component.create({
			html: "Hello"
		})

		cmp.render(this.getEl().dom);


	});
});