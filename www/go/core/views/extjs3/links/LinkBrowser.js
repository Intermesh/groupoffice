
/* global go, Ext */

go.links.LinkBrowser = Ext.extend(go.Window, {
	entity: null,
	entityId: null,
	
	stateId: "go-link-browser",
	
	layout: "responsive",
	maximizable: !GO.util.isMobileOrTablet(),
	
	initComponent: function () {

		var actions = this.initRowActions();
		
		this.entityGrid = new go.links.EntityGrid({
			
			width: dp(160),
			mobile:{
				width: dp(120),
			},
			region: "west",
			split: true,
			stateId: "go-link-browser-entity-grid",
			savedSelection: "go-link-browser-" + this.entity
		});

		this.entityGrid.getTopToolbar().add('->');
		this.entityGrid.getTopToolbar().add({
			cls: 'go-narrow',
			iconCls: "ic-arrow-forward",
			tooltip: t("Links"),
			handler: function () {
				this.grid.show();
			},
			scope: this
		});


		applyEntityFilter = () => {
			this.store.setFilter('entities', {
				entities: this.entityGrid.getSelectionModel().getSelections().map(function(r){
					return {name: r.data.entity, filter: r.data.filter};
				})
			});

			this.store.load();
		}

		this.entityGrid.getSelectionModel().on('selectionchange', function (sm) {
			applyEntityFilter();
			

		}, this, {buffer: 1});

		this.entityGrid.on("viewready", () => {
			applyEntityFilter();
		})


		this.store = new go.data.GroupingStore({
			autoDestroy: true,
			remoteGroup: true,
			fields: [
				'id', 
				'toId', 
				'toEntity', 
				{name: "to", type: "relation"}, 
				'description', 
				{
					name: 'modifiedAt',
					mapping: "to.modifiedAt",
					type: "date"
				}
				],
			entityStore: "Link",
			sortInfo: {field: 'modifiedAt', direction: 'DESC'},
			autoLoad: false,
			remoteSort: true,
			groupField: 'toEntity'
		});

		this.store.setFilter('entity', {
			entity: this.entity,
			entityId: this.entityId
		});

		this.grid = new go.grid.GridPanel({
			cls: "go-link-grid",
			stateId: "go-link-browser-grid",
			region: "center",
			plugins: [actions],
			tbar: [		
				{
					cls: 'go-narrow', //Shows on mobile only
					iconCls: "ic-menu",
					handler: function () {
						this.entityGrid.show();
					},
					scope: this
				},
				'->',
				{
					iconCls: 'ic-add',
					tooltip: t("Add"),
					cls: "primary",
					handler: function () {
						var linkWindow = new go.links.CreateLinkWindow({
							entityId: this.entityId,
							entity: this.entity
						});
						linkWindow.show();
					},
					scope: this
				},
				{
					xtype: 'tbsearch'
				}			
			],
			store: this.store,
			view: new Ext.grid.GroupingView({
				hideGroupedColumn: true,
				forceFit: true,
				// custom grouping text template to display the number of items per group
				groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
			}),
			columns: [
				{
					id: 'name',
					header: t('Name'),
					sortable: true,
					dataIndex: 'name',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						if(!record.data || !record.data.to) {
							console.warn(record);
							return '';
						}
						if (!record.data.to.description) {
							record.data.to.description = "-";
						}

						var str = record.data.to.name + " <br /><label>" + record.data.to.description + "</label>";

						var linkIconCls = go.Entities.getLinkIcon(record.data.toEntity, record.data.to.filter);
						
						if (rowIndex === 0 || this.lastLinkIconCls != linkIconCls) {
							str = '<i class="entity ' + linkIconCls + '"></i>' + str;
							
							this.lastLinkIconCls = linkIconCls;
						}

						return str;
					}
				}, {
					id: 'toEntity',
					header: t('Type'),
					width: 75,
					sortable: true,
					dataIndex: 'toEntity',
					renderer: function(v) {
						return t(v, go.Entities.get(v).module);
					}
				},
				{
					id: 'modifiedAt',
					header: t('Modified at'),					
					hidden: false,
					sortable: true,
					dataIndex: 'modifiedAt',
					xtype:"datecolumn"
				},
				actions
			],
			listeners: {
				navigate: function(grid, index, record) {				
					this.load(record.data.toEntity, record.data.toId);		
									
				},
				scope: this
			},
			autoExpandColumn: 'name'			
		});

		Ext.apply(this, {
			title: t("Links"),
			width: dp(1200),
			height: dp(600),
			layout: 'responsive',
			items: [this.centerPanel = new Ext.Panel({
				region: "center",
				layout:"responsive",
				layoutConfig: {
					triggerWidth: 1000
				},
				items: [this.grid, this.entityGrid]
			}), 
			this.getPreviewPanel()]
		});

		if(GO.util.isMobileOrTablet()) {
			this.tools = [{
				id: "left",
				handler: function () {
					this.centerPanel.show();
				},
				scope: this
			}];			

			this.centerPanel.on("show", function() {
				var tool = this.getTool("left");
				tool.hide();
			},this);

			this.previewPanel.on("show", function() {			
				var tool = this.getTool("left");
				tool.show();				
			}, this)
		}


		go.links.CreateLinkWindow.superclass.initComponent.call(this);
	},
	
	load : function(entity, id) {
		var pnl = this.previewPanel.getComponent(entity);
		if(pnl) {
			pnl.load(id);

			this.previewPanel.getLayout().setActiveItem(pnl);
			this.previewPanel.show();
		}
	},
	
	getPreviewPanel : function() {
		
		var all = go.Entities.getLinkConfigs().filter(function(l) {
			return !!l.linkDetail;
		});
		
		var items = all.map(function(e) {
			var panel = e.linkDetail();
			panel.itemId = e.entity;

			if(panel.getItemId) {
				return panel;
			}else {
				return new go.GOUIWrapper({itemId: e.entity,comp:panel, load: (id) => {panel.load(id)}});
			}
		});
		
		// console.log(items);
		
		return this.previewPanel = new Ext.Panel({
			split: true,
			region: "east",
			width: dp(500),
			layout:"card",
			items: items
		});
	},

	initRowActions: function () {

		var actions = new Ext.ux.grid.RowActions({
			menuDisabled: true,
			hideable: false,
			draggable: false,
			fixed: true,
			header: '',
			hideMode: 'display',
			keepSelection: true,

			actions: [{
					iconCls: 'btn-delete ux-row-action-on-hover',
					qtip: t("Add")
				}]
		});

		actions.on({
			action: function (grid, record, action, row, col, e, target) {
				go.Db.store("Link").set({
					destroy: [record.id]
				});
			}
		});

		return actions;

	}
});
