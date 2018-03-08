/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: SelectOptionsGrid.js 19873 2016-03-01 10:55:30Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */



GO.customfields.SelectOptionsGrid = function(config){
	if(!config)
	{
		config = {};
	}

	config.title = GO.customfields.lang.SelectOptions;
	config.layout='fit';
	config.anchor='-20';
	config.autoScroll=true;
	config.split=true;
	//config.height=200;
	config.autoHeight=true;
	
	//config.disabled=true;
	var fields ={
		fields:['id','text'],
		columns:[	{
			sortable:false,
			hideable:false,
			menuDisabled:true,
			header: GO.lang.strText,
			dataIndex: 'text',
			editor: new Ext.form.TextField()
		}]
	};


	config.store = new GO.data.JsonStore({
			//url: GO.settings.modules.customfields.url+'json.php',
			url: GO.url("customfields/field/selectOptions"),
			baseParams: {
				//'task': 'field_options',
				'field_id' : 0
				},
			root: 'results',
			totalProperty: 'total',
			id: 'id',
			fields:['id','text'],
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
		emptyText: GO.lang['strNoItems']
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;

	config.clicksToEdit=1;



	var Option = Ext.data.Record.create([
	// the "name" below matches the tag name to read, except "availDate"
	// which is mapped to the tag "availability"
	{
		name: 'id',
		type: 'int'
	},

	{
		name: 'text',
		type: 'string'
	}
	]);

	config.enableDragDrop=true;
	config.listeners={
		scope:this,
		render:function(){
			//enable row sorting
			var DDtarget = new Ext.dd.DropTarget(this.getView().mainBody,
			{
				ddGroup : 'cfSelectOptionsDD',
				copy:false,
				notifyDrop : this.notifyDrop.createDelegate(this)
			});
		}
	}
	config.ddGroup='cfSelectOptionsDD';

	config.tbar=[{
		iconCls: 'btn-add',
		text: GO.lang['cmdAdd'],
		cls: 'x-btn-text-icon',
		handler: function(){
			var e = new Option({
				id: '0',
				text:''
			});
			this.stopEditing();
			var rowIndex = this.store.getCount();
			this.store.insert(rowIndex, e);
			this.startEditing(rowIndex, 0);
		},
		scope: this
	},{
		iconCls: 'btn-delete',
		text: GO.lang['cmdDelete'],
		cls: 'x-btn-text-icon',
		handler: function(){
			var selectedRows = this.selModel.getSelections();
			for(var i=0;i<selectedRows.length;i++)
			{
				selectedRows[i].commit();
				this.store.remove(selectedRows[i]);
			}
		},
		scope: this
	},{
		iconCls: 'btn-upload',
		text:GO.lang.cmdImport,
		handler:this.importSelectOptions,
		scope:this
	}];
	GO.customfields.SelectOptionsGrid.superclass.constructor.call(this, config);

};
Ext.extend(GO.customfields.SelectOptionsGrid, Ext.grid.EditorGridPanel,{
	importSelectOptions : function(){

		if(GO.util.empty(this.store.baseParams.field_id)){
			alert(GO.customfields.lang.clickApplyFirst);
			return false;
		}

		if(!this.importDialog)
		{
			this.importDialog = new GO.customfields.ImportDialog({
				importText:GO.customfields.lang.importText,
				task: 'import_select_options',
				listeners:{
					scope:this,
					importSelectOptions:function(){this.store.reload();}
				}
			});

		}
		this.importDialog.upForm.baseParams.field_id=this.store.baseParams.field_id;
		this.importDialog.show();
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
	},
	setFieldId : function(field_id){
		//this.setDisabled(!field_id);
		if(field_id!=this.store.baseParams.field_id){
			this.store.baseParams.field_id=field_id;

			if(GO.util.empty(field_id)){
				this.store.loaded=false;
				this.store.removeAll();
			}else
			{
				this.store.load();
			}
		}
	},

	getGridData : function(){

		var data = {};

		for (var i = 0; i < this.store.data.items.length;  i++)
		{
			var r = this.store.data.items[i].data;

			data[i]={};

			for(var key in r)
			{
				data[i][key]=r[key];
			}
		}

		return data;
	},
	setIds : function(ids)
	{
		for(var index in ids)
		{
			if(index!="remove")
			{
				this.store.getAt(index).set('id', ids[index]);
			}
		}
	}
});




