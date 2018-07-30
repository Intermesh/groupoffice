go.modules.community.search.Panel = Ext.extend(Ext.Panel, {
	lastQ: "",
	initComponent: function () {



		this.grid = new go.links.LinkGrid({
			cls: 'go-grid3-hide-headers',
			region: "center",
			listeners: {
				click: function () {
					this.gotoSelected();
				},
				keypress: function() {
					this.gotoSelected();
				},
				scope: this
			}
		});

		this.entityGrid = new go.links.EntityGrid({
			width: dp(200),
			region: "east",
			split: true
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

		go.modules.community.search.Panel.superclass.initComponent.call(this);
		
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
		
		var e = go.Entities.get(record.data.entity);
		e.goto(record.data.entityId);
		this.collapse();
	},
	
	search: function (q) {
		
		this.lastQ = q;
		var filter = {}, entities = [];
		
		Ext.each(this.entityGrid.getSelectionModel().getSelections(), function (r) {
			entities.push(r.data.entity);
		}, this);
		if(entities.length) {
			filter.entities = entities;
		}

		filter.q = q;
		this.grid.store.baseParams.limit = 20;
		this.grid.store.removeAll();
		
		this.grid.store.load({
			params: {
				filter: filter
			},
			callback: function() {
						
			},
			scope: this
		});
		
		this.setHeight(dp(600));
		this.expand();
	},
	// private
	collapseIf : function(e){
		if(!e.within(this.getEl())){
				this.collapse();
		}
	}

});
