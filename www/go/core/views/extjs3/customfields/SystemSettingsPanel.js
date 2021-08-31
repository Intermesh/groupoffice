go.customfields.SystemSettingsPanel = Ext.extend(go.grid.GridPanel, {
	hasPermission: function() {
		const module = go.Modules.get(this.package, this.module);
		return module.userRights.mayChangeCustomFields;
	},
	iconCls: 'ic-storage',
	title: t("Custom fields"),
	itemId: "customfields", //makes it routable
	initComponent: function () {
		
		var data = go.Entities.getAll().map(function(e) {			
			var module = go.Modules.get(e.package, e.module);		
			e.moduleTitle = go.Modules.getConfig(module.package, module.name).title;			
			return e;
			
		}).filter(function(e){return e.customFields != false;});
		
		this.store = new Ext.data.Store({
			reader: new Ext.data.JsonReader({
				fields: ['name', 'title', 'module', "package", "customFields", "moduleTitle"],
				root: 'data'
			}),			
			data: {data: data },
			sortInfo: {
				field: 'name',
				direction: 'ASC'
			},
			remoteSort: false
//			groupField: 'moduleTitle'
		});
			
			
//		this.view = new Ext.grid.GroupingView({
//			hideGroupedColumn: true
//		});
		
		this.autoExpandColumn = "name";
			
		this.columns = [
				{
					id: 'name',
					header: t('Name'),
					dataIndex: 'title',
					hideable: false				
				},{
					id: 'moduleTitle',
					dataIndex: 'moduleTitle',
					header: t("Module")
				},{
					dataIndex: 'name',
					align: "right",
					width: dp(180),
					hideable: false,
					draggable: false,
					menuDisabled: false,
					sortable: false,
					renderer: function(v, meta, record) {	
						
						return '<button title="' + Ext.util.Format.htmlEncode(t('Manage default permissions')) + '" class="icon">edit</button>';
						
					}
				}
			
			];
		
		go.customfields.SystemSettingsPanel.superclass.initComponent.call(this);
		
		this.on('cellclick', this.onCellClick, this);
		this.on('rowdblclick', this.onRowDblClick, this);
	},
	
	onRowDblClick :function(grid, rowIndex, e) {
		this.edit(this.store.getAt(rowIndex));
	},
	
	onCellClick : function(grid, rowIndex, columnIndex, e) {
		
		if(e.target.tagName != "BUTTON") {
			return;
		}
		this.edit(this.store.getAt(rowIndex));	
	},
	
	edit : function(record) {
		var win = new go.customfields.EntityDialog({
			entity: record.data.name
		});
		win.show();
	}
});

