go.grid.GridPanel = Ext.extend(Ext.grid.GridPanel, {
	
	/**
	 * If the end of the list is within this number of pixels it will request the next page	
	 */
	scrollBoundary : 300,

	initComponent: function () {
		go.grid.GridPanel.superclass.initComponent.call(this);
		
		if(!this.keys)
		{
			this.keys=[];
		}
		this.keys.push({
			key: Ext.EventObject.DELETE,
			fn: function(key, e){
				console.log("DELETE");
				this.deleteSelected();
			},
			scope:this
		});

		this.on("bodyscroll", this.loadMore, this, {buffer: 100});
	},
	
	deleteSelected : function() {
	
		var selectedRecords = this.getSelectionModel().getSelections(), ids = [];
		
		selectedRecords.forEach(function(r) {
			ids.push(r.data.id);
		});
		
		switch(ids.length)
		{
			case 0:				
				return;
			case 1:
				var strConfirm = t("Are you sure you want to delete the selected item?");
			break;

			default:
				var strConfirm = t("Are you sure you want to delete the {count} items?").replace('{count}', ids.length);
			break;					
		}
		
		Ext.MessageBox.confirm(t("Confirm delete"), t(strConfirm), function(btn) {
			
			if(btn != "yes") {
				return;
			}
			
			go.Stores.get('Note').set({
				destroy: ids
			});
		});
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
