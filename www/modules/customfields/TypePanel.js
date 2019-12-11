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
				'required_condition',
		    'category_name',
		    'category_id',
				'column_name',
				'unique_values'
		    ]}),
		    
	  		baseParams: {
					//task:'all_fields', 
					extends_model:""
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
	  this.title=GO.customfields.lang.customfields;
	  this.columns=[
				{
					header:GO.customfields.lang.category,
					dataIndex: 'category_name',
					width: 120
				},
				{
					header:GO.lang['strName'],
					dataIndex: 'name'
				},
				{
					header:GO.lang['strType'],
					dataIndex: 'type'
				},
				{
					header:GO.customfields.lang['required'],
					dataIndex: 'required',
					hidden:true
				},
				{
					header:'Database name',
					dataIndex: 'column_name'
				},
				{
					header:GO.customfields.lang['uniqueValues'],
					dataIndex: 'unique_values'
				}];
	 this.view= new Ext.grid.GroupingView({
				autoFill:true,
				forceFit:true,
		    hideGroupedColumn:true,
		    groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
		   	emptyText: GO.customfields.lang.noFields,
		   	showGroupName:false
			});
			
		this.disabled=true;
			
		this.sm=new Ext.grid.RowSelectionModel();
		this.loadMask=true;
		
		this.tbar=new Ext.Toolbar([{
				iconCls: 'btn-add',							
				text: GO.lang['cmdAdd'],
				cls: 'x-btn-text-icon',
				handler: function(){

					if(!GO.customfields.categoriesStore.data.items[0])
					{
						alert(GO.customfields.lang.createCategoryFirst);
					}else
					{
						this.fieldDialog.show();
					}
				},
				scope: this
			},{	
				iconCls: 'btn-delete',
				text: GO.lang['cmdDelete'],
				cls: 'x-btn-text-icon',
				handler: function(){
					this.deleteSelected();
				},
				scope: this
			},{
				iconCls: 'btn-folder',
				text: GO.customfields.lang.manageCategories,
				cls: 'x-btn-text-icon',
				handler: function(){
					if(!this.categoriesDialog)
					{
						this.categoriesDialog = new GO.customfields.CategoriesDialog();
						this.categoriesDialog.on('change', function(){this.store.reload();}, this);						
					}
					this.categoriesDialog.show(this.store.baseParams.extends_model);
				},
				scope: this
				
			}]);
		
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
			rowData.set('category_id', dropRowData.get('category_id'));
			
		
			if(!this.copy){
				this.store.remove(this.store.getById(rows[i].id));
			}
			
			this.store.insert(cindex,rowData);
		}
		
		//save sort order							
		var records = [];

  	for (var i = 0; i < this.store.data.items.length;  i++)
  	{			    	
			records.push({id: this.store.data.items[i].get('id'), sort_index : i, category_id: this.store.data.items[i].get('category_id')});
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
	
	setLinkType : function(extends_model)
	{
		this.setDisabled(false);
		this.fieldDialog.setExtendModel(extends_model);
		this.store.baseParams.extends_model=extends_model;
		GO.customfields.categoriesStore.baseParams.extends_model=extends_model;
		GO.customfields.categoriesStore.load();
	}
	
});