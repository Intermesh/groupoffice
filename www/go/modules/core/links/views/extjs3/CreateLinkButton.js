/* global Ext, go */

go.modules.core.links.CreateLinkButton = Ext.extend(Ext.Button, {
	iconCls: 'ic-add',
	tooltip: t("Add"),
	newLinks : [],
	initComponent: function () {

		this.searchField = new go.modules.core.search.SearchCombo({
			anchor: "100%",
			hideLabel: true,
			listeners: {
				scope: this,
				select: function (cmb, record, index) {
					this.linkGrid.store.loadData({"records" :[{
						"toId": record.get('entityId'),
						"toEntity": record.get('entity'),
						to: {
							name: record.data.name,
							description: "" 
						}
					}]}, true);
					this.searchField.reset();
					
					this.newLinks.push({						
						toEntity: record.get('entity'),
						toId: record.get('entityId')
					});
				}
			},
			getListParent: function () {
				//this avoids hiding the menu on click in the list
				return this.el.up('.x-menu');
			}
		});

		this.linkGrid = new go.grid.GridPanel({
			columns: [
				{
					id: 'name',
					header: t('Name'),					
					sortable: true,
					dataIndex: 'to',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						return '<i class="entity ' + record.data.toEntity + '"></i> ' + record.data.to.name;
					}
				},
				{
					width: dp(80),
					menuDisabled: true,
					draggable: false,
					hidable: false,
					align: "right",
					sortable: false,
					dataIndex: "entityId",
					renderer: function (v, meta, record) {						
						return "<button class='icon'>delete</button>";						
					}
				}
			],
			autoExpandColumn: 'name',
			store: new go.data.Store({
				autoDestroy: true,
				fields: ['id', 'toId', 'toEntity', 'to', 'description', {name: 'modifiedAt', type: 'date'}],
				entityStore: "Link",
				sortInfo: {
					field: 'modifiedAt',
					direction: 'DESC'
				},
				baseParams: {
					filter: {}
				}
			}),
			tbar: new Ext.Toolbar({
				layout: "fit",
				items: [{
						xtype: "fieldset",
						items: [this.searchField]
					}]
			}),
			listeners: {
				scope: this,
				rowclick: function (grid, rowIndex, e) {
					if (e.target.tagName !== "BUTTON") {
						return false;
					}

					var record = grid.store.getAt(rowIndex);
					grid.store.remove(record);
					
					
					var i = this.newLinks.findIndex(function(l) {
						return l.toId === record.get('toId') && l.toEntity === record.get('toEntity');
					});
					
					if(i > -1) {
						this.newLinks = this.newLinks.splice(i, 1);
					} else
					{
						go.Stores.get("Link").set({
							destroy: [record.id]
						});
					}
				}
			},
			width: dp(500),
			height: dp(400)
		}
		);

		var me = this;

		this.menu = new Ext.menu.Menu({
			items: [this.linkGrid],
			doFocus: function () {
				me.searchField.focus();
			},
			listeners: {
				scope: this,	
				show: function() {
					if(this.linkGrid.store.baseParams.filter.entityId) {
						this.linkGrid.store.load();
					}
				}
			}
		});

		go.modules.core.links.CreateLinkButton.superclass.initComponent.call(this);
	},
	
	setEntity : function(entity, entityId) {
		this.linkGrid.store.baseParams.filter.entity = entity;
		this.linkGrid.store.baseParams.filter.entityId = entityId;		
		this.menu.on("show", this.load, this, {single: true});
	},
	
	reset : function() {
		this.linkGrid.store.removeAll();
		this.linkGrid.store.baseParams.filter.entity = null;
		this.linkGrid.store.baseParams.filter.entityId = null;	
		this.menu.un("show", this.load);
	},
	
	load: function() {
		this.linkGrid.store.load();		
	},	
	
	getNewLinks : function() {
		var links = {}, i = 0, id;		
		
		this.newLinks.forEach(function(l) {
			id = "new" + (i++);
			l.fromEntity = this.linkGrid.store.baseParams.filter.entity;
			l.fromId = this.linkGrid.store.baseParams.filter.entityId;
			
			links[id] = l;
		}, this);
		
		return links;
	},
	
	save : function() {
		if(this.newLinks.length === 0) {
			return;
		}
		
		go.Stores.get("Link").set({
			create: this.getNewLinks()
		}, function() {
			this.newLinks = [];
		}, this);
	}
});
