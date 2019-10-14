go.links.CreateLinkWindow = Ext.extend(go.Window, {
	entity: null,
	entityId: null,
	modal: true,
	singleSelect:false,
	stateId: "go-create-link-windows",
	
	/**
	 * Provide the entities to show in the list here
	 * When not provided, the list will show all entities
	 */
	entities : null,
	
	search: function (v) {
		
		var filter = {};
		
		var entities = [];

		Ext.each(this.entityGrid.getSelectionModel().getSelections(), function (r) {
			entities.push(r.data.entity);
		}, this);

		if(entities.length) { 
			filter.entities = entities;
		}

		filter.q = this.searchField.getValue();
			
		
		this.grid.store.load({
			params: {
				filter: filter
			}
		});
	},

	initComponent: function () {

		this.searchField = new go.SearchField({
			anchor: "100%",
			handler: function(field, v){
				this.search(v);
			},
			scope: this
		});

		var search = new Ext.Panel({
			layout: "form",
			region: "north",
			autoHeight: true,
			items: [{
					xtype: "fieldset",
					items: [this.searchField]
				}]
		});		

		this.grid = new go.links.LinkGrid({
			selModel: new Ext.grid.RowSelectionModel({
				singleSelect:this.singleSelect
			}),
			cls: 'go-search-grid',
			region: "center",
			listeners: {
				dblclick: function () {
					this.link();
				},
				scope: this
			}
		});
		
		this.entityGrid = new go.links.EntityGrid({
			width: dp(200),
			region:"west",
			savedSelection: "link",
			entities:this.entities
		});
		
		this.entityGrid.getSelectionModel().on('selectionchange', function (sm) {
			this.search();
		}, this, {buffer: 1}); //add buffer because it clears selection first

		Ext.apply(this, {
			title: t("Create link", "links"),
			width: dp(700),
			height: dp(600),
			layout: 'border',
			items: [this.entityGrid, search, this.grid],
			listeners: {
				render: function () {
					//this.store.load();
				},
				scope: this
			},
			buttons: [{
					text: t("Ok"),
					handler: function () {
						this.link();
					},
					scope: this
				}
			]
		});



		go.links.CreateLinkWindow.superclass.initComponent.call(this);
	},
	
	focus : function() {
		this.searchField.focus();
	},

	link: function () {
		
		var selections = this.singleSelect ? [this.grid.getSelected()] : this.grid.getSelectionModel().getSelections();
		var me = this;
		var links = [];

		selections.forEach(function (record) {
			var link = {
				fromEntity: me.entity,
				fromId: me.entityId,
				toEntity: record.get('entity'),
				toId: record.get('entityId')
			}

			links.push(link);
		});

		go.Stores.get("Link").set({
			create: links
		}, function () {
			me.close();
		});
	}
});


