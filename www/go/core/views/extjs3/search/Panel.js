go.search.Panel = Ext.extend(Ext.Panel, {
	lastQ: "",
	height: dp(500),
	initComponent: function () {
		this.grid = new go.links.LinkGrid({
			hideHeaders: true,
			region: "center",
			sortInfo: {
				field: 'id', //faster than modifiedAt
				direction: 'DESC'
			},
			listeners: {
				click: function () {
					this.gotoSelected();
				},
				keydown: function(e) {
					console.warn("keypress", e.getKey());

					if(e.getKey() == Ext.EventObject.SPACE || e.getKey() == Ext.EventObject.ENTER) {
						this.gotoSelected();
					}
				},
				scope: this
			}
		});

		
		this.entityGrid = new go.links.EntityGrid({
			width: dp(160),
			mobile:{
				width: dp(120)
			},
			region: "east",
			split: true,
			savedSelection: "search"

		});

		
		this.entityGrid.getSelectionModel().on('selectionchange', function (sm) {
			this.search(this.lastQ);
		}, this, {buffer: 1}); //add buffer because it clears selection first	

		Ext.apply(this, {
			cls: "go-search-panel",
			layout: 'border',
			floating: true,
			frame: true,
			collapsed: true,
			items: [this.entityGrid, this.grid]			
		});

		go.search.Panel.superclass.initComponent.call(this);
		
		this.on("expand", function() {
			Ext.getDoc().on('mousedown', this.collapseIf, this);
		}, this);
		this.on("collapse", function() {
			Ext.getDoc().un('mousedown', this.collapseIf, this);
		}, this);
	},
	
	gotoSelected: function() {		
		var record = this.grid.getSelectionModel().getSelected();
		if(!record) {
			return;
		}
		
		this.searchField.fireEvent("select", this.searchField, record);
		this.collapse();
		
	},
	
	search: function (q) {

		this.expand();
		
//
//		
//		if(!this.entityGrid.viewReady) {
//			this.entityGrid.on("viewready", function() {
//				this.search(q);
//			}, this, {single: true});
//			return;
//		}
//		
		this.lastQ = q;
		var filter = {}, entities = [];

		// this.getEl().mask(t("Loading..."));
		
		Ext.each(this.entityGrid.getSelectionModel().getSelections(), function (r) {
			entities.push({
				name: r.data.entity,
				filter: r.data.filter
			});
			
		}, this);
		if(entities.length) {
			filter.entities = entities;
		}

		filter.text = q;
		this.grid.store.baseParams.limit = 20;
		this.grid.store.removeAll();

		var me = this;
		
		this.grid.store.load({
			params: {
				filter: filter
			}
		}).finally(function() {
			// me.getEl().unmask();
			// me.expand();
		}).catch(function(response) {			
			me.fireEvent("searchexception", this, response);
			//me.collapse();
		}).then(function() {
			
		});
		
		//this.setHeight(dp(600));
		
	},
	// private
	collapseIf : function(e){
		if(!e.within(this.getEl()) && !e.within(this.searchField.getEl())){
				this.collapse();
		}
	}

});
