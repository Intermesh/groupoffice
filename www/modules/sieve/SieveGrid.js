/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: SieveGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.sieve.SieveGrid = function(config){
	
	this.selectScript = new Ext.form.ComboBox({
		hiddenName:'selectScript',
		valueField:'value',
		displayField:'name',
		store: new GO.data.JsonStore({
			url: GO.url('sieve/sieve/scripts'),
			baseParams: {
				account_id: 0
			},
			fields: ['name', 'value','active'],
			root: 'results'
		}),
		mode:'local',
		triggerAction:'all',
		editable:false,
		selectOnFocus:true,
		forceSelection:true,
		allowBlank:false,
		width:140
	});

	this.selectScript.on('select', function(combo, record){
		this.setSelectedScript(record.json.value);
		this.store.reload();
	},this);

	var fields ={
		fields:['id','name', 'index', 'script_name','active'],
		columns:[{
			header: t("Sieve", "sieve"),
			dataIndex: 'name'
		},{
			header: t("Active", "sieve"),
			dataIndex: 'active',
			renderer: function(value, metaData, record, rowIndex, colIndex, store) {
				if(value)
					value = t("Yes");
				else
					value = t("No");
				return value;
			}
		}
	]};

	if(!config)
	{
		config = {};
	}
	config.title=t("Filters", "email");
	config.layout='fit';
	config.region='center';
	config.autoScroll=true;
	config.border=false;
	config.disabled=true;
	config.store = new GO.data.JsonStore({
		url: GO.url('sieve/sieve/rules'),
		baseParams: {
			script_name: ''
			},
		root: 'results',
		id: 'index',
		totalProperty:'total',
		fields: fields.fields,
		remoteSort: true
	});
	config.paging=false;
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true,
			autoFill: true,
			forceFit: true
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

	this.sieveDialog = new GO.sieve.SieveDialog();
	this.sieveDialog.on('hide', function(panel){
		this.store.load();
	}, this);

	config.enableDragDrop=true;
	config.ddGroup='SieveFilterDD';

	config.tbar=[
		t("Filterset:", "sieve"),
		this.selectScript,{
			iconCls: 'btn-extra',
			text: t("Activate", "sieve"),
			handler: function(){

				this.selectScript.store.load({
					params:{
						set_active_script_name: this.selectScript.getValue()
					},
					callback:function(){
						this.selectScript.setValue(this.selectScript.getValue());
					},
					scope:this
				});
			},
			scope: this
		}, '->',{
			iconCls: 'ic-add',
			tooltip: t("Add"),
			handler: function(){
	    		this.sieveDialog.show(-1,this.selectScript.getValue(),this.store.baseParams.account_id);
			},
			scope: this
		},{
			iconCls: 'ic-delete',
			tooltip: t("Delete"),
			handler:function(){this.deleteSelected();},
			scope: this
		}];
	GO.sieve.SieveGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);
		this.sieveDialog.show(record.data.index, record.data.script_name, this.store.baseParams.account_id);
		}, this);

	this.on('show', function(){		
		this.selectScript.store.load({
			callback:function(){
				if(this.selectScript.store.reader.jsonData.success){
					this.selectScript.setValue(this.selectScript.store.reader.jsonData.active);
					this.store.load();
				}else
				{
					//alert("Sieve not supported");
					this.setDisabled(true);
				}
			},
			scope:this
		});
	}, this);

	this.on('render', function(){
		//enable row sorting
		var DDtarget = new Ext.dd.DropTarget(this.getView().mainBody,
		{
			ddGroup : 'SieveFilterDD',
			copy:false,
			notifyDrop : this.onNotifyDrop.createDelegate(this)
		});
	}, this);
};

Ext.extend(GO.sieve.SieveGrid, GO.grid.GridPanel,{
	setAccountId : function(account_id){
		this.setDisabled(!account_id);
		this.accountId=account_id;
		this.store.baseParams.account_id = account_id;
		this.selectScript.store.baseParams.account_id = account_id;
	},
	setSelectedScript : function(name){
		if(name)
			this.store.baseParams.script_name = name;
		else
			this.store.baseParams.script_name = this.selectScript.getValue();
	},
	onNotifyDrop : function(dd, e, data)
	{
		var rows=this.selModel.getSelections();
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
		var filters = [];

		for (var i = 0; i < this.store.data.items.length;  i++)
		{
			filters.push(this.store.data.items[i].get('index'));
		}

		Ext.Ajax.request({
			url: GO.url('sieve/sieve/saveScriptsSortOrder'),
			params: {
				sort_order: Ext.encode(filters),
				account_id: this.store.baseParams.account_id
			},
			success: function(response, opts) {
				this.store.load();
			},
			scope: this				
		});
	}
});
