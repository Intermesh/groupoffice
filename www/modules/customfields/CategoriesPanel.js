GO.customfields.CategoriesPanel = Ext.extend(GO.grid.GridPanel, {
	changed : false,
	initComponent : function(){
		
		this.layout='fit';
		
		
		this.store = GO.customfields.categoriesStore;
		this.enableDragDrop=true;
	  this.columns=[
				{
					header:GO.lang['strName'],
					dataIndex: 'name'
				}];
	 this.view= new Ext.grid.GridView({
				autoFill:true,
				forceFit:true		    
			});
			
	this.ddGroup='cfCategoriesDD';
			
		this.sm=new Ext.grid.RowSelectionModel();
		this.loadMask=true;
		
		this.tbar=[{
				iconCls: 'btn-add',							
				text: GO.lang['cmdAdd'],
				cls: 'x-btn-text-icon',
				handler: function(){				
					this.categoryDialog.setType(this.store.baseParams.link_type);	
		    	this.categoryDialog.show();
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
			}];
		
		this.categoryDialog = new GO.customfields.CategoryDialog();
		this.categoryDialog.on('save', function(){
			this.store.reload();
			this.changed=true;
		}, this);
		
		this.on('rowdblclick', function(grid){
			var selectionModel = grid.getSelectionModel();
			var record = selectionModel.getSelected();
						
			this.categoryDialog.show(record.id);
		}, this);
		
	
		
		GO.customfields.CategoriesPanel.superclass.initComponent.call(this);
	},
	
	afterRender : function(){
		
		GO.customfields.CategoriesPanel.superclass.afterRender.call(this);
		//enable row sorting
		var DDtarget = new Ext.dd.DropTarget(this.getView().mainBody, 
		{
			ddGroup : 'cfCategoriesDD',
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
		
		for(i = 0; i < rows.length; i++) 
		{								
			var rowData=this.store.getById(rows[i].id);
			
			if(!this.copy){
				this.store.remove(this.store.getById(rows[i].id));
			}
			
			this.store.insert(cindex,rowData);
		}
		
		//save sort order							
		var records = [];
  	for (var i = 0; i < this.store.data.items.length;  i++)
  	{			    	
			records.push({id: this.store.data.items[i].get('id'), sort_index : i});
  	}
  	
  	this.changed=true;
		
//		Ext.Ajax.request({
//			url: GO.settings.modules.customfields.url+'action.php',
//			params: {
//				task: 'save_categories_sort_order',
//				categories: Ext.encode(records)
//			}
//		});			

		GO.request({
			url:'customfields/category/saveSort',
			params:{
				categories:Ext.encode(records)
			}
		})
		
	}
	
});