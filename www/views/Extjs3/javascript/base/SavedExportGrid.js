GO.base.SavedExportGrid = Ext.extend(GO.grid.GridPanel,{
	className : null,
	
	initComponent : function(){
		
		Ext.apply(this,{
			editDialogConfig : {},
			editDialogClass : GO.base.SavedExportDialog,
			standardTbar:true,
			store: new GO.data.JsonStore({
				url: GO.url('core/export/SavedExportsStore'),
				baseParams : {
					moduleName : this.moduleName
				},
				root: 'results',
				id: 'id',
				totalProperty:'total',
				fields: ['id','name'],
				remoteSort: true,
				model:"GO\\Base\\Model\\SavedExport"
			}),
			border: false,
			paging:true,
			view:new Ext.grid.GridView({
				autoFill: true,
				forceFit: true,
				emptyText: t("No items to display")		
			}),
			cm:new Ext.grid.ColumnModel({
				defaults:{
					sortable:true
				},
				columns:[
				{
					header: t("Name"), 
					dataIndex: 'name'
				}	
				]
			})
		});
		
		GO.base.SavedExportGrid.superclass.initComponent.call(this);
	},
	setClass : function(className){
		this.className = className;
		
		// TODO: THIS NEEDS TO BE DONE IN THE BASE GRID CLASS
		if(!this.editDialog){
			this.editDialog = new this.editDialogClass(this.editDialogConfig);

			this.editDialog.on('save', function(){   
				this.store.reload();
			}, this);	
		}
		
		this.editDialog.setClass(this.className);
		this.store.baseParams.className = this.className;
		this.store.load();
	},
	showEditDialog : function(id, config, record){
    config = config || {};
	
		config = Ext.apply(config,{loadParams:{className:this.className}});
				
		if(!this.editDialog){
			this.editDialog = new this.editDialogClass(this.editDialogConfig);

			this.editDialog.on('save', function(){   
				this.store.reload();   
//				this.changed=true;
			}, this);	
		}
		
		if(Ext.isArray(this.primaryKey) && record) {
		  for (var j=0;j<this.primaryKey.length;j++)
			this.editDialog.formPanel.baseParams[this.primaryKey[j]] = record.data[this.primaryKey[j]];
		}
		
		if(this.relatedGridParamName)
			this.editDialog.formPanel.baseParams[this.relatedGridParamName]=this.store.baseParams[this.relatedGridParamName];
		
		this.editDialog.show(id, config);	  
	}
});
