go.defaultpermissions.DefaultPermissionsPanel = Ext.extend(go.grid.GridPanel, {
	
	initComponent: function () {
		
		var data = go.Entities.getAll().map(function(e) {
			
			var module = go.Modules.get(e.package, e.module),
			
				serverInfo = module.entities.find(function(serverInfo) {
					return serverInfo.name == e.name;
				});
				
			if(serverInfo) {
				e.isAclOwner = serverInfo.isAclOwner;
				e.defaultAclId = serverInfo.defaultAclId;
			}
			
			e.moduleTitle = go.Modules.getConfig(module.package, module.name).title;
						
			return e;
			
		}).filter(function(e){return e.isAclOwner;});
		
		this.store = new Ext.data.Store({
			reader: new Ext.data.JsonReader({
				fields: ['name', 'title', 'module', "package", "customFields", "defaultAclId", "defaultsPanel", "moduleTitle"],
				root: 'data'
			}),			
			data: {data: data },
			sortInfo: {
				field: 'name',
				direction: 'ASC'
			},
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
					sortable: true,
					dataIndex: 'title',
					hideable: false,				
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
		
		go.defaultpermissions.DefaultPermissionsPanel.superclass.initComponent.call(this);
		
		this.on('cellclick', this.onCellClick, this);
	},
	
	onCellClick : function(grid, rowIndex, columnIndex, e) {
		
		if(e.target.tagName != "BUTTON") {
			return;
		}
		var record = this.store.getAt(rowIndex);						
		
		var win = new go.defaultpermissions.ShareWindow();
		win.entity = record.data.name;
		win.load(record.data.defaultAclId).show();
		
	}
});
