/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ActionGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @author WilmarVB <wilmar@intermesh.nl>
 */

GO.sieve.ActionGrid = function(config){
	if(!config)
	{
		config = {};
	}
	config.height=180;
	config.style='margin: 5px;';
	config.border=true;
	config.cls = 'go-grid3-hide-headers';
	var fields ={
		fields:['type','copy','target','days','addresses','reason','vacationStart','vacationEnd','text'],
		header: false,
		columns:[{
				header:false,
				dataIndex:'text'
			}]
	};

	var columnModel =  new Ext.grid.ColumnModel({
		columns:fields.columns
	});

	config.store = new GO.data.JsonStore({
	    root: 'actions',
	    id: 'id',
	    totalProperty:'total',
	    fields: fields.fields,
	    remoteSort: true
	});
	config.enableDragDrop = true;
	config.ddGroup = 'SieveActionDD';
	config.cm=columnModel;
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("Please click 'add' to add an action", "sieve")
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	config.tbar=[{
			iconCls: 'btn-add',
			text: t("Add"),
			cls: 'x-btn-text-icon',
			handler: function(){this.showActionCreatorDialog();},
				scope: this
		},{
			iconCls: 'btn-delete',
			text: t("Delete"),
			cls: 'x-btn-text-icon',
			handler: function(){this.deleteSelected();},
				scope: this
		}];

	GO.sieve.ActionGrid.superclass.constructor.call(this, config);

	this.on('render',function(){
		//enable row sorting
		var DDtarget = new Ext.dd.DropTarget(this.getView().mainBody,
		{
			ddGroup : 'SieveActionDD',
			copy:false,
			notifyDrop : this.onNotifyDrop.createDelegate(this)
		});
	}, this);
};

Ext.extend(GO.sieve.ActionGrid, GO.grid.GridPanel,{
	
	accountAliasesString : '',
	
	deleteSelected : function(){this.store.remove(this.getSelectionModel().getSelections());},

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
		var filters = {};

		for (var i = 0; i < this.store.data.items.length;  i++)
		{
			filters[this.store.data.items[i].get('id')] = i;
		}
	},
	
	_saveActionRecord : function( values ) {
		var record;
	
		if(values.id<0)
		{
			record = new GO.sieve.ActionRecord(values);
			var insertId = this.store.getCount();

//			if(this.store.getCount() > 0 && this.store.getAt(this.store.getCount()-1).data.type == 'stop'){
//				insertId = this.store.getCount()-1;
//			}else
//			{
//				if (!(values.type=='redirect' && values.copy==true) && values.type!='vacation') {
//					var stopRecord = new GO.sieve.ActionRecord({
//								type:"stop",
//								copy: false,
//								target:"",
//								days:"",
//								addresses:"",
//								reason:"",
//								text : t("Stop", "sieve")
//							});
//
//					this.store.insert(insertId, stopRecord);
//				}
//			}
			//}
			
			record.data.id = insertId;

			this.store.insert(insertId, record);
		}
		else
		{
			record = this.store.getAt(values.id);
			Ext.apply(record.data,values);
			record.commit();
		}
	},
	
	showActionCreatorDialog : function(recordId) {	
		if (!this.actionCreatorDialog) {
			this.actionCreatorDialog = new GO.sieve.ActionCreatorDialog();
			this.actionCreatorDialog.on('actionPrepared',function(actionValues){
				this._saveActionRecord(actionValues);
			},this);
		}
		
		if (recordId>=0) {
			var record = this.store.getAt(recordId);
			record.set('id',recordId);
			this.actionCreatorDialog.show(record);
		} else {
			var record = new Ext.data.Record();
			record.set('id',-1);
			record.set('addresses',this.accountAliasesString);
			this.actionCreatorDialog.show(record);
		}
	}
});
