go.grid.GridPanel = Ext.extend(Ext.grid.GridPanel, {
	
	/**
	 * If the end of the list is within this number of pixels it will request the next page	
	 */
	scrollBoundary : 300,

	initComponent: function () {
		go.grid.GridPanel.superclass.initComponent.call(this);

		this.on("bodyscroll", this.loadMore, this, {buffer: 100});
	},

	/**
	 * Loads more data if the end off the scroll area is reached
	 * @returns {undefined}
	 */
	loadMore: function () {
		var store = this.getStore();

		if (store.getCount() == store.getTotalCount()) {
			return;
		}

		store.lastOptions.params = store.lastOptions.params || {};

		var limit = store.lastOptions.params.limit || store.getCount(),
						pos = store.lastOptions.params.position || 0,
						scroller = this.getView().scroller.dom,
						body = this.getView().mainBody.dom;


		if ((scroller.offsetHeight + scroller.scrollTop + this.scrollBoundary) >= body.offsetHeight) {

			var p = Ext.apply(store.lastOptions, {
				add: true,
				params: {
					limit: limit,
					position: pos + limit
				}
			});
			store.load(p);
		}
	}

});

Ext.reg("gogrid", go.grid.GridPanel);
