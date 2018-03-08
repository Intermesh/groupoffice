go.Links = {

	linkToWindows: [],

	/**
	 * Register a menu item for the "Link to" button
	 * 
	 * @param {string} entity eg. "Contact"
	 * @param {function} openWindowFunction If this function returns a window 
	 *		object it will set a link on the window's save event. If you don't 
	 *		return this your window must take care of the linking.
	 *		
	 * @param {string} title If omitted the entity type will be translated with the module
	 * @returns {void}
	 */
	registerLinkToWindow: function (entity, openWindowFunction, title) {

		if (!title) {
			title = t(entity, go.entities[entity].module);
		}

		this.linkToWindows.push({
			entity: entity,
			openWindowFunction: openWindowFunction,
			title: title
		});
	}
};

go.EntityManager.register("links", "Link");
