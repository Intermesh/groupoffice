go.grid.GridPanel = Ext.extend(Ext.grid.GridPanel, {

	/**
	 * If the end of the list is within this number of pixels it will request the next page	
	 */
	scrollBoundary: 300,
	
	pageSize: 10,
	pagePosition : 0,

	initComponent: function () {
		go.grid.GridPanel.superclass.initComponent.call(this);

		if (!this.keys)
		{
			this.keys = [];
		}
	
		this.initDeleteKey();		

		this.on("bodyscroll", this.loadMore, this, {buffer: 100});

		this.store.baseParams.limit = this.pageSize;
	},
	
	initDeleteKey : function() {
		this.keys.push({
			key: Ext.EventObject.DELETE,
			fn: function (key, e) {
				this.deleteSelected();
			},
			scope: this
		});
	},

	deleteSelected: function () {

		var selectedRecords = this.getSelectionModel().getSelections(), ids = [], strConfirm;

		selectedRecords.forEach(function (r) {
			ids.push(r.data.id);
		});

		switch (ids.length)
		{
			case 0:
				return;
			case 1:
				strConfirm = t("Are you sure you want to delete the selected item?");
				break;

			default:
				strConfirm = t("Are you sure you want to delete the {count} items?").replace('{count}', ids.length);
				break;
		}

		Ext.MessageBox.confirm(t("Confirm delete"), t(strConfirm), function (btn) {

			if (btn != "yes") {
				return;
			}

			this.getStore().entityStore.set({
				destroy: ids
			});
		}, this);
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


		var	scroller = this.getView().scroller.dom,
						body = this.getView().mainBody.dom;


		if ((scroller.offsetHeight + scroller.scrollTop + this.scrollBoundary) >= body.offsetHeight) {

			var o = GO.util.clone(store.lastOptions);
			o.add = true;
			o.params = o.params || {};
			
			o.params.position = o.params.position || 0;
			o.params.position += this.pageSize;
			
			store.load(o);			
		}
	}

});

Ext.reg("gogrid", go.grid.GridPanel);
