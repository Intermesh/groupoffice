GO.customfields.TypePanel = Ext.extend(GO.grid.GridPanel, {
	
	initComponent : function(){
		
		this.layout='fit';
		
		this.ddGroup='cfFieldsDD';
		
		this.store = new Ext.data.GroupingStore({
			reader: new Ext.data.JsonReader({
        totalProperty: "count",
		    root: "results",
		    id: "id",
		    fields:[
		    'id',
		    'name', 
		    'datatype', 
				'type',
				'required',
		    'category_name',
		    'fieldSetId',
				'column_name',
				'unique_values'
		    ]}),
		    
	  		baseParams: {
					//task:'all_fields', 
					extendsModel:""
				},
			proxy: new Ext.data.HttpProxy({
		      //url: GO.settings.modules.customfields.url+'json.php'
					url:GO.url('customfields/field/store')
		  }),        
	    groupField:'category_name',
	    remoteSort:true,
	    remoteGroup:true
	  });
	  
	  this.enableDragDrop=true;
	  this.title=t("Custom fields", "customfields")+': '+t("Changes you make here will take affect after you reload Group-Office in your browser.", "customfields").replace('Group-Office', GO.settings.config.product_name);
	  this.columns=[
				{
					header:t("Category", "customfields"),
					dataIndex: 'category_name',
					width: 120
				},
				{
					header:t("Name"),
					dataIndex: 'name'
				},
				{
					header:t("Type"),
					dataIndex: 'type'
				},
				{
					header:t("Required field", "customfields"),
					dataIndex: 'required',
					hidden:true
				},
				{
					header:'Database name',
					dataIndex: 'column_name'
				},
				{
					header:t("Unique values", "customfields"),
					dataIndex: 'unique_values'
				}];
	 this.view= new Ext.grid.GroupingView({
				autoFill:true,
				forceFit:true,
		    hideGroupedColumn:true,
		    groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
		   	emptyText: t("No custom fields to display", "customfields"),
		   	showGroupName:false
			});
			
		this.disabled=true;
			
		this.sm=new Ext.grid.RowSelectionModel();
		this.loadMask=true;
		
		this.tbar=new Ext.Toolbar([{
				iconCls: 'btn-add',							
				text: t("Add"),
				cls: 'x-btn-text-icon',
				handler: function(){

					if(!GO.customfields.categoriesStore.data.items[0])
					{
						alert(t("You must create a category first", "customfields"));
					}else
					{
						this.fieldDialog.show();
					}
				},
				scope: this
			},{	
				iconCls: 'btn-delete',
				text: t("Delete"),
				cls: 'x-btn-text-icon',
				handler: function(){
					this.deleteSelected();
				},
				scope: this
			},{
				iconCls: 'btn-folder',
				text: t("Manage categories", "customfields"),
				cls: 'x-btn-text-icon',
				handler: function(){
					if(!this.categoriesDialog)
					{
						this.categoriesDialog = new GO.customfields.CategoriesDialog();
						this.categoriesDialog.on('change', function(){this.store.reload();}, this);						
					}
					this.categoriesDialog.show(this.store.baseParams.extendsModel);
				},
				scope: this
				
			}
			
//			,new Ext.Button({
//				iconCls: 'btn-settings',
//				text: t("Manage blocks", "customfields"),
//				cls: 'x-btn-text-icon',
//				handler: function(){
//					if (!GO.customfields.manageBlocksWindow) {
//						GO.customfields.manageBlocksWindow = new GO.Window({
//							title : t("Manage blocks", "customfields"),
//							items: [this.manageBlocksGrid = new GO.customfields.ManageBlocksGrid({layout:'fit',height:490})],
//							width: 800,
//							height: 600,
//							layout: 'fit'
//						});
//						GO.customfields.manageBlocksWindow.on('show',function(){
//							this.manageBlocksGrid.store.load();
//						},this);
//					}
//					GO.customfields.manageBlocksWindow.show();
//				},
//				scope: this
//			})
		]);
		
		this.fieldDialog = new GO.customfields.FieldDialog();
		this.fieldDialog.on('save', function(){
			this.store.reload();
		}, this);
		
		this.on('rowdblclick', function(grid){
			var selectionModel = grid.getSelectionModel();
			var record = selectionModel.getSelected();
						
			this.fieldDialog.show(record.id);
		}, this);
		
		//this.tbar.setDisabled(true);
		
		GO.customfields.TypePanel.superclass.initComponent.call(this);
	},
	
	afterRender : function(){
		
		GO.customfields.TypePanel.superclass.afterRender.call(this);
		//enable row sorting
		var DDtarget = new Ext.dd.DropTarget(this.getView().mainBody, 
		{
			ddGroup : 'cfFieldsDD',
			copy:false,
			notifyDrop : this.notifyDrop.createDelegate(this)
		});
	},
	
	notifyDrop : function(dd, e, data)
	{
		var sm=this.getSelectionModel();
		var rows=sm.getSelections();
		var dragData = dd.getDragData(e);
		var cindex=dragData.rowIndex;
		if(cindex=='undefined')
		{
			cindex=this.store.data.length-1;
		}
		var dropRowData = this.store.getAt(cindex);
		
		
		for(i = 0; i < rows.length; i++) 
		{								
			var rowData=this.store.getById(rows[i].id);
			
			//set new group field
			rowData.set(this.store.groupField, dropRowData.get(this.store.groupField));
			rowData.set('fieldSetId', dropRowData.get('fieldSetId'));
			
		
			if(!this.copy){
				this.store.remove(this.store.getById(rows[i].id));
			}
			
			this.store.insert(cindex,rowData);
		}
		
		//save sort order							
		var records = [];

  	for (var i = 0; i < this.store.data.items.length;  i++)
  	{			    	
			records.push({id: this.store.data.items[i].get('id'), sortOrder : i, fieldSetId: this.store.data.items[i].get('fieldSetId')});
  	}
		
		GO.request({
			url:'customfields/field/saveSort',
			params:{
				fields:Ext.encode(records)
			}
		})
		
//		Ext.Ajax.request({
//			url: GO.settings.modules.customfields.url+'action.php',
//			params: {
//				task: 'save_fields_sort_order',
//				fields: Ext.encode(records)
//			}
//		});
//					
		
	},
	
	setLinkType : function(extendsModel)
	{
		this.setDisabled(false);
		this.fieldDialog.setExtendModel(extendsModel);
		this.store.baseParams.extendsModel=extendsModel;
		GO.customfields.categoriesStore.baseParams.extendsModel=extendsModel;
		GO.customfields.categoriesStore.load();
	}
	
});
