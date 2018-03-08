/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */



GO.customfields.ManageBlocksGrid = function(config){
	if(!config)
	{
		config = {};
	}

	config.layout='fit';
	config.anchor='-20';
	config.autoScroll=true;
	config.split=true;
	//config.height=200;
	
	//config.disabled=true;
	var fields ={
		fields:['id','name','col_id','customfield_name','customfield_datatype','extendsModel'],
		columns:[{
			header: 'ID',
			dataIndex: 'id',
			width: 50,
			sortable: true
		},{
			header: t("Name"),
			dataIndex: 'name',
			width: 100,
			sortable: true
		},{
			header: t("Custom field ID", "customfields"),
			dataIndex: 'col_id',
			width: 80,
			sortable: true
		},{
			header: t("Custom field's data type", "customfields"),
			dataIndex: 'customfield_datatype',
			width: 150,
			sortable: true,
			renderer: function(v) {
				return t("v", "customfields");
			}
		},{
			header: t("Custom field used in", "customfields"),
			dataIndex: 'extendsModel',
			width: 150,
			sortable: true,
			renderer: function(v) {
				return t("v", "customfields");
			}
		}]
	};

	config.store = new GO.data.JsonStore({
			//url: GO.settings.modules.customfields.url+'json.php',
			url: GO.url("customfields/block/manageStore"),
			root: 'results',
			totalProperty: 'total',
			id: 'id',
			fields: fields.fields,
			remoteSort:true
		});

	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:fields.columns
	});

	config.cm=columnModel;
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	config.paging=true;

	config.clicksToEdit=1;

	config.tbar=[{
		iconCls: 'btn-add',
		text: t("Add"),
		cls: 'x-btn-text-icon',
		handler: function(){
			this.showManageBlockDialog(0);
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
	}];
	GO.customfields.ManageBlocksGrid.superclass.constructor.call(this, config);

	this.on('rowdblclick',function(grid,rowIndex,event){
		var record = grid.store.getAt(rowIndex);
		this.showManageBlockDialog(record.data.id);
	}, this);

};
Ext.extend(GO.customfields.ManageBlocksGrid, GO.grid.GridPanel,{
	showManageBlockDialog : function(blockId)
	{
		if (!GO.customfields.manageBlockDialog) {
			
			GO.customfields.manageBlockDialog = new GO.customfields.ManageBlockDialog();
			GO.customfields.manageBlockDialog.on('save',function(){
				this.store.load();
			},this);
			
		}
		GO.customfields.manageBlockDialog.show(blockId);
	}
});




