/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ManageCategoriesGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Twan Verhofstad
 */

GO.bookmarks.ManageCategoriesGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.store = GO.bookmarks.writableCategoriesStore; // de categorieen

	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.border=false;	
	config.paging=true;

	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
		{
			header: t("Name"), 
			dataIndex: 'name'
		},{
			header: t("Owner"), 
			dataIndex: 'user_name',
			sortable: false
		}		
		]
	});
	
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")		
	});
	
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	
	this.categoryDialog = new GO.bookmarks.CategoryDialog();
	this.categoryDialog.on('save', function(){   
		this.store.load();
		this.changed=true;
	}, this);
	
	
	config.tbar=[{
		iconCls: 'btn-add',
		text: t("Add"),
		cls: 'x-btn-text-icon',
		handler: function(){				
			this.categoryDialog.show();
		},
		scope: this
	},{
		iconCls: 'btn-delete',
		text: t("Delete"),
		cls: 'x-btn-text-icon',
		handler: function(){
			this.deleteSelected();
			this.changed=true;
		},
		scope: this
	}];
	
	// Constructor
	GO.bookmarks.ManageCategoriesGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);
		this.categoryDialog.show(record.data.id);
	}, this);

	GO.bookmarks.writableCategoriesStore.load();
	
};


Ext.extend(GO.bookmarks.ManageCategoriesGrid, GO.grid.GridPanel,{
	changed : false
});
