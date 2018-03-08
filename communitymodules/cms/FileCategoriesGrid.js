/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: 
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 */

/**
 * This code will have to make place for FileCategoriesTree.js .
 */


GO.cms.FileCategoriesGrid = function(config){
	
	config = config || {};

	config.title = GO.cms.lang.categories;
	
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
			url : GO.settings.modules.cms.url + 'json.php',
			baseParams : {
				task : 'file_categories',
				file_id : 0
			},
			fields : ['id','used','name'],
			root : 'results',
			id : 'id',
			totalProperty : 'total'
		});
	
	this.checkColumn = new GO.grid.CheckColumn({
		header: '&nbsp;',
		dataIndex: 'used',
		width: 30
	});
	
	config.plugins = [this.checkColumn];
	
	config.paging=true;
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
		this.checkColumn,
		{
			header: GO.lang.strName,
			dataIndex: 'name',
			align:"left",
			width:280,
			editor: new Ext.form.TextField({
			  fieldLabel: GO.lang.strName,
			  name:'name'
			})
		}
	]
	});
	
	config.cm=columnModel;
	//config.autoExpandColumn='description';
	config.view=new Ext.grid.GridView({
		emptyText: GO.lang['strNoItems']
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	config.clicksToEdit=1;

	var Category = Ext.data.Record.create([
	// the "name" below matches the tag name to read, except "availDate"
	// which is mapped to the tag "availability"
	{
		name: 'id',
		type: 'int'
	},
	{
		name: 'checked',
		type: 'bool'
	},
	{
		name: 'name',
		type: 'string'
	}
	]);

	config.tbar=[{
		iconCls: 'btn-add',
		text: GO.lang['cmdAdd'],
		cls: 'x-btn-text-icon',
		handler: function(){
			var e = new Category({
				id: '0',
				checked: false,
				name:''
			});
			this.stopEditing();
			var rowIndex = this.store.getCount();
			this.store.insert(rowIndex, e);
			this.startEditing(rowIndex, 1);
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

	GO.cms.FileCategoriesGrid.superclass.constructor.call(this, config);

	this.checkColumn.on('change', function(record){
		this.save_category(record.data);
	},this);
	this.on('afteredit', function(e){
		this.save_category(e.record.data);
	}, this);
};

Ext.extend(GO.cms.FileCategoriesGrid, GO.grid.EditorGridPanel,{

	load : function(file_id){
		this.store.baseParams.file_id=this.file_id=file_id;
		this.store.load();
	},
	
	save_category : function (data) {
		this.body.mask(GO.lang.waitMsgSave);
		Ext.Ajax.request({
			url : GO.settings.modules.cms.url + 'action.php',
			params : {
				task : 'save_category',
				id : data.id,
				file_id : this.file_id,
				used : data.used,
				name : data.name
			},
			scope : this,
			callback : function (options, success,response) {
				var responseParams = Ext.decode(response.responseText);
				if (!success) {
					GO.errorDialog.show(responseParams.feedback)
				}
				else
				{
					this.body.unmask();
				}
			}
		})
	}
	
});