go.grid.TilePanel = Ext.extend(Ext.DataView, {
	//autoHeight:true,
	autoScroll:true,
	multiSelect: true,
	cls: 'x-view-tiles',
	overClass:'x-view-over',
	selectedClass:'x-view-selected',
	itemSelector:'div.tile',
	tpl: null, // new Ext.XTemplate('<tpl for="."><div class="tile"></div></tpl>')
	/**
	 * If the end of the list is within this number of pixels it will request the next page	
	 */
	scrollBoundary : 300,

	initComponent: function () {
		go.grid.TilePanel.superclass.initComponent.call(this);
		
		if(!this.keys)
		{
			this.keys=[];
		}
		this.keys.push({
			key: Ext.EventObject.DELETE,
			fn: function(key, e){
				this.deleteSelected();
			},
			scope:this
		});

		this.on("contextmenu", function(view, rowIndex, node, e) {
			e.stopEvent();
			if(view.isSelected(rowIndex) !== true) {
				view.clearSelections();
				view.select(rowIndex);
			}
		}, this);
	},
	
	afterRender: function() {
		go.grid.TilePanel.superclass.afterRender.call(this);
		
		this.el.on("scroll", this.loadMore, this, {buffer: 100});
	},
	
	deleteSelected : function() {
	
		var selectedRecords = this.getSelectedRecords(), ids = [];
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
						scroller = this.el.dom,
						body = this.ownerCt.el.dom;


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
