go.defaultpermissions.DefaultPermissionsPanel = Ext.extend(go.grid.GridPanel, {

	
	initComponent: function () {
		
		var data = go.Entities.getAll().map(function(e) {			
			var module = go.Modules.get(e.package, e.module);		
			e.moduleId = module.id;	
			e.moduleTitle = go.Modules.getConfig(module.package, module.name).title;						
			return e;
			
		}).filter(function(e){return e.isAclOwner;});
		
		this.store = new Ext.data.Store({
			reader: new Ext.data.JsonReader({
				fields: ['moduleId', 'name', 'title', 'module', "package", "customFields", "defaultAcl", "defaultsPanel", "moduleTitle"],
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
		
		var win = new go.defaultpermissions.ShareWindow({
			forEntityStore: record.data.name
		});
		win.load(record.data.moduleId).show();		
	}
});
