go.customfields.SystemSettingsPanel = Ext.extend(go.grid.GridPanel, {
	iconCls: 'ic-storage',
	title: t("Custom fields"),
	initComponent: function () {
		
		var data = go.Entities.getAll().map(function(e) {
			
			var module = go.Modules.get(e.package, e.module),			
				serverInfo = module.entities.find(function(serverInfo) {
					return serverInfo.name == e.name;
				});
				
			if(serverInfo) {
				if(!e.customFields) {
					e.customFields = serverInfo.supportsCustomFields;
				}				
			}
			
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
	},
	
	onCellClick : function(grid, rowIndex, columnIndex, e) {
		
		if(e.target.tagName != "BUTTON") {
			return;
		}
		var record = this.store.getAt(rowIndex);						
		
		var win = new go.customfields.EntityDialog({
			entity: record.data.name
		});
		win.show();
		
	}
});

