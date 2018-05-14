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
		
		this.on("rowcontextmenu", function(grid, rowIndex, e) {
			e.stopEvent();
			var sm =this.getSelectionModel();
			if(sm.isSelected(rowIndex) !== true) {
				sm.clearSelections();
				sm.selectRow(rowIndex);
			}
		}, this);
	},
	
	deleteSelected : function() {
	
		var selectedRecords = this.getSelectionModel().getSelections(), ids = [];
		
		selectedRecords.forEach(function(r) {
			ids.push(r.data.id);
		});
		
		this.store.destroy(ids);
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
