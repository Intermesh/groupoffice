/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */
 

GO.comments.ManageCategoriesGrid = Ext.extend(GO.grid.GridPanel,{
	changed : false,
	
	initComponent : function(){
		
		Ext.apply(this,{
			standardTbar:true,
			standardTbarDisabled:!GO.settings.modules.comments.write_permission,
			store: GO.comments.categoriesStore,
			border: false,
			paging:true,
			view:new Ext.grid.GridView({
				autoFill: true,
				forceFit: true,
				emptyText: GO.lang['strNoItems']		
			}),
			cm:new Ext.grid.ColumnModel({
				defaults:{
					sortable:true
				},
				columns:[
				{
					header: GO.lang.strName, 
					dataIndex: 'name'
				}
				]
			})
		});
		
		GO.comments.ManageCategoriesGrid.superclass.initComponent.call(this);

	},
	
	dblClick : function(grid, record, rowIndex){
		this.showCategoryDialog(record.id);
	},
	
	btnAdd : function(){				
		this.showCategoryDialog();	  	
	},
	showCategoryDialog : function(id){
		if(!this.categoryDialog){
			this.categoryDialog = new GO.comments.CategoryDialog();

			this.categoryDialog.on('save', function(){   
				this.store.load();
				this.changed=true;	    			    			
			}, this);	
		}
		this.categoryDialog.show(id);	  
	}
//	,
//	deleteSelected : function(){
//		GO.comments.ManageCategoriesGrid.superclass.deleteSelected.call(this);
//	}
});