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
		
		filter.entities = this.entityGrid.getSelectionModel().getSelections().map(function(r){return {name: r.data.entity, filter: r.data.filter};});
		
		if(this.searchField.getValue() !== "") {
			filter.text = this.searchField.getValue();			
		}

		this.grid.store.setFilter('search', filter);
		
		this.grid.store.load();
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

		this.grid.store.setFilter('permissions', {permissionLevel: go.permissionLevels.write});
		
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
		
		var selections = this.singleSelect ? [this.grid.getSelected()] : this.grid.getSelectionModel().getSelections(),
			me = this, links = {}, i = 0;
		
		selections.forEach(function (record) {
			var link = {
				fromEntity: me.entity,
				fromId: me.entityId,
				toEntity: record.get('entity'),
				toId: record.get('entityId')
			};
			
			i++;
			links['clientId-' + i ] = link;
		});

		me.getEl().mask(t("Saving..."));

		go.Db.store("Link").set({
			create: links
		}).then(function (response) {
			me.getEl().unmask();

			if(!go.util.empty(response.notCreated)) {
				Ext.MessageBox.alert(t("Error"), "Could not link the items.");
			} else{
				me.close();
			}
			
		});
	}
});


