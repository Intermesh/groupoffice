go.links.CreateLinkWindow = Ext.extend(go.Window, {
	entity: null,
	entityId: null,
	modal: true,
	singleSelect:false,
	stateId: "go-create-link-windows",

	width: dp(800),
	height: dp(600),
	title: t("Create link", "links"),
	layout:"border",
	supportsFiles: false,

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

		go.searchLinkFilter = v;
		
		this.grid.store.load();
	},

	initComponent: function () {

		this.descriptionField = new Ext.form.TextField({
			'xtype': 'textfield',
			'fieldLabel': t('Description'),
			'name': 'description',
			'maxLength': 190,
			'emptyText': t('Optional description'),
			'anchor': '100%'
		});

		this.searchField = new go.SearchField({
			anchor: "100%",
			handler: function(field, v){
				this.search(v);
			},
			scope: this
		});

		this.descriptionPanel = new Ext.Panel({
			layout: "form",
			region: "south",
			autoHeight: true,
			items: [{
				xtype: "fieldset",
				items: [this.descriptionField]
			}]
		});

		var search = new Ext.Panel({
			layout: "form",
			region: "north",
			autoHeight: true,
				items: [{
					xtype: "fieldset",
					items: [
						this.searchField
					]
				}]
			}
		);

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
			width: dp(160),
			mobile:{
				width: dp(120),
			},
			region:"west",
			savedSelection: "link",
			supportsFiles: this.supportsFiles,
			split: true
		});

		this.entityGrid.getSelectionModel().on('selectionchange', function (sm) {
			this.search();
		}, this, {buffer: 1}); //add buffer because it clears selection first

		Ext.apply(this, {
			items: [this.entityGrid, search, this.grid, this.descriptionPanel],
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

		//wait for entity selection
		this.entityGrid.on("viewready", function() {
			if(go.searchLinkFilter) {
				this.searchField.setValue(go.searchLinkFilter);
				this.search(go.searchLinkFilter);
			}
		}, this);
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
				description: me.descriptionField.getValue(),
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


