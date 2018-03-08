/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ManageCategoriesGrid.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 

GO.notes.ManageCategoriesGrid = Ext.extend(GO.grid.GridPanel,{
	changed : false,
	
	initComponent : function(){
		
		Ext.apply(this,{
			standardTbar:true,
			standardTbarDisabled:!GO.settings.modules.notes.write_permission,
			store: GO.notes.writableAdminCategoriesStore,
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
				},{
					header: GO.lang.strOwner, 
					dataIndex: 'user_name',
					sortable: false
				}		
				]
			})
		});
		
		GO.notes.ManageCategoriesGrid.superclass.initComponent.call(this);
		
		GO.notes.writableAdminCategoriesStore.load();	
	},
	
	dblClick : function(grid, record, rowIndex){
		this.showCategoryDialog(record.id);
	},
	
	btnAdd : function(){				
		this.showCategoryDialog();	  	
	},
	showCategoryDialog : function(id){
		if(!this.categoryDialog){
			this.categoryDialog = new GO.notes.CategoryDialog();

			this.categoryDialog.on('save', function(){   
				this.store.load();
				this.changed=true;	    			    			
			}, this);	
		}
		this.categoryDialog.show(id);	  
	},
	deleteSelected : function(){
		GO.notes.ManageCategoriesGrid.superclass.deleteSelected.call(this);
		this.changed=true;
	}
});