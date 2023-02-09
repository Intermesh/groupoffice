/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: CriteriumGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @author WilmarVB <wilmar@intermesh.nl>
 */

GO.sieve.CriteriumGrid = function(config){
	if(!config)
	{
		config = {};
	}
	config.autoScroll=true;
	config.height=180;
	config.style='margin: 5px;';
	config.border=true;
	config.cls = 'go-grid3-hide-headers';
	var fields ={
		fields:['test','not','type','arg','arg1','arg2','text','part'],
		header: false,
		columns:[
//		{
//			header: t("Test", "sieve"),
//			dataIndex: 'test'
//		},{
//			header: t("Not", "sieve"),
//			dataIndex: 'not'
//		},{
//			header: t("Type", "sieve"),
//			dataIndex: 'type'
//		},{
//			header: t("Argument", "sieve"),
//			dataIndex: 'arg'
//		},{
//			header: t("Argument1", "sieve"),
//			dataIndex: 'arg1'
//		},{
//			header: t("Argument2", "sieve"),
//			dataIndex: 'arg2'
//		},
		{
			header:false,
			dataIndex:'text',
			renderer:function(value, metaData, record, rowIndex, colIndex, store){
				
				var txtToDisplay = '';

				switch(record.data.test)
				{
					case 'currentdate':
				
//						id: 1, test: "currentdate", not: false, type: "is", arg: Date 2015-08-19T22:00:00.000Z, arg1: "", arg2: ""
						switch(record.data.type){
							case 'value-le':
								txtToDisplay = t("Current Date", "sieve")+' '+t("before", "sieve")+' '+record.data.arg;
								break;
							case 'is':
								txtToDisplay = t("Current Date", "sieve")+' '+t("is", "sieve")+' '+record.data.arg;
								break;
							case 'value-ge':
								txtToDisplay = t("Current Date", "sieve")+' '+t("after", "sieve")+' '+record.data.arg;
								break;
						}

					break;
						
					case 'body':
						if(record.data.type == 'contains')
						{
							if(record.data.not)
							{
								txtToDisplay = t("Body doesn't contain", "sieve")+' '+record.data.arg;
							} else {
								txtToDisplay = t("Body contains", "sieve")+' '+record.data.arg;
							}
						} else {
							if(record.data.not)
							{
								txtToDisplay = t("Body doesn't match", "sieve")+' '+record.data.arg;
							} else {
								txtToDisplay = t("Body matches", "sieve")+' '+record.data.arg;
							}
						}
						break;
					case 'header':
						if(record.data.type == 'contains')
						{
							if(record.data.not)
							{
								if(record.data.arg1 == 'Subject')
									txtToDisplay = t("Subject doesn't contain", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'From')
									txtToDisplay = t("Sender doesn't contain", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'To')
									txtToDisplay = t("Recipient doesn't contain", "sieve")+' '+record.data.arg2;
								else
									txtToDisplay = t("Mailheader:", "sieve")+" "+record.data.arg1+" "+t("doesn't contain", "sieve")+" "+record.data.arg2;
							}
							else
							{
								if(record.data.arg1 == 'Subject')
									txtToDisplay = t("Subject contains", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'From')
									txtToDisplay = t("Sender contains", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'To')
									txtToDisplay = t("Recipient contains", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'X-Spam-Flag')
									txtToDisplay = t("Marked as spam", "sieve");
								else
									txtToDisplay = t("Mailheader:", "sieve")+" "+record.data.arg1+" "+t("contains", "sieve")+" "+record.data.arg2;
							}
						}
						else if(record.data.type == 'is')
						{
							if(record.data.not)
							{
								if(record.data.arg1 == 'Subject')
									txtToDisplay = t("Subject is not equal to", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'From')
									txtToDisplay = t("From is not equal to", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'To')
									txtToDisplay = t("To is not equal to", "sieve")+' '+record.data.arg2;
								else
									txtToDisplay = t("Mailheader:", "sieve")+" "+record.data.arg1+" "+t("doesn't equal", "sieve") +" "+record.data.arg2;
							}
							else
							{
								if(record.data.arg1 == 'Subject')
									txtToDisplay = t("Subject equals", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'From')
									txtToDisplay = t("From equals", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'To')
									txtToDisplay = t("To equals", "sieve")+' '+record.data.arg2;
								else
									txtToDisplay = t("Mailheader:", "sieve")+" "+record.data.arg1+" "+t("is", "sieve") +" "+record.data.arg2;
							}
						}
						else if(record.data.type == 'matches')
						{
							if(record.data.not)
							{
								if(record.data.arg1 == 'Subject')
									txtToDisplay = t("Subject doesn't match", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'From')
									txtToDisplay = t("From doesn't match", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'To')
									txtToDisplay = t("To doesn't match", "sieve")+' '+record.data.arg2;
								else
									txtToDisplay = t("Mailheader:", "sieve")+" "+record.data.arg1+" "+t("doesn't match", "sieve") +" "+record.data.arg2;
							}
							else
							{
								if(record.data.arg1 == 'Subject')
									txtToDisplay = t("Subject matches", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'From')
									txtToDisplay = t("From matches", "sieve")+' '+record.data.arg2;
								else if(record.data.arg1 == 'To')
									txtToDisplay = t("To matches", "sieve")+' '+record.data.arg2;
								else
									txtToDisplay = t("Mailheader:", "sieve")+" "+record.data.arg1+" "+t("matches", "sieve") +" "+record.data.arg2;
							}
						}
						break;

					case 'exists':
						if(record.data.not)
						{
							if(record.data.arg == 'Subject')
								txtToDisplay = t("Subject doesn't exist", "sieve");
							else if(record.data.arg == 'From')
								txtToDisplay = t("Sender doesn't exist", "sieve");
							else if(record.data.arg == 'To')
								txtToDisplay = t("Recipient doesn't exist", "sieve");
							else
								txtToDisplay = t("Mailheader:", "sieve")+" "+record.data.arg+" "+t("doesn't exist", "sieve");
						}
						else
						{
							if(record.data.arg == 'Subject')
								txtToDisplay = t("Subject exists", "sieve");
							else if(record.data.arg == 'From')
								txtToDisplay = t("Sender exists", "sieve");
							else if(record.data.arg == 'To')
								txtToDisplay = t("Recipient exists", "sieve");
							else
								txtToDisplay = t("Mailheader:", "sieve")+" "+record.data.arg+" "+t("doesn't exist", "sieve");
						}
						break;

					case 'true':	
						txtToDisplay = 'Alle';
						break;

					case 'size':
						if(record.data.type == 'under')
							txtToDisplay = t("Size is smaller than", "sieve")+' '+record.data.arg;
						else
							txtToDisplay = t("Size is bigger than", "sieve")+' '+record.data.arg;
						break;
						
					default:
						txtToDisplay = t("Error while displaying test line", "sieve");
						break;
				}
				return txtToDisplay;
			}
		}
	]};
	
	var columnModel =  new Ext.grid.ColumnModel({
		columns:fields.columns
	});

	config.store = new GO.data.JsonStore({
	    root: 'criteria',
	    id: 'id',
	    totalProperty:'total',
	    fields: fields.fields,
	    remoteSort: true
	});
	enableDragDrop:true,
	config.enableDragDrop = true;
	config.ddGroup = 'SieveTestDD';
	config.cm=columnModel;
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("Please click 'add' to add a criterium", "sieve")
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	config.tbar=[{
			iconCls: 'btn-add',
			text: t("Add"),
			cls: 'x-btn-text-icon',
			handler: function(){this.showCriteriumCreatorDialog();},
				scope: this
		},{
			iconCls: 'btn-delete',
			text: t("Delete"),
			cls: 'x-btn-text-icon',
			handler: function(){this.deleteSelected();},
				scope: this
		}];

	GO.sieve.CriteriumGrid.superclass.constructor.call(this, config);

	this.on('render',function(){
	
		//enable row sorting
		var DDtarget = new Ext.dd.DropTarget(this.getView().mainBody,
		{
			ddGroup : 'SieveTestDD',
			copy:false,
			notifyDrop : this.onNotifyDrop.createDelegate(this)
		});
	}, this);
	
	this.on('rowdblclick', function(grid, index, e){
//		var record = this.store.getAt(index);
		this.showCriteriumCreatorDialog(index);
	},this);
};

Ext.extend(GO.sieve.CriteriumGrid, GO.grid.GridPanel,{
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
	
	_saveCriteriumRecord : function(values) {
		if(values.id<0){
			var record = new GO.sieve.CriteriumRecord(values)
			record.set('id',this.store.getCount());
			this.store.insert( this.store.getCount(), record);
		}
		else
		{
			var record = this.store.getAt(values.id);
			Ext.apply(record.data,values);
			record.commit();
		}
	},
	
	showCriteriumCreatorDialog : function(recordId) {
		if (!this.criteriumCreatorDialog) {
			this.criteriumCreatorDialog = new GO.sieve.CriteriumCreatorDialog();
			this.criteriumCreatorDialog.on('criteriumPrepared',function(critValues){
				this._saveCriteriumRecord(critValues);
			},this);
		}
		
		if (recordId>=0) {
			var record = this.store.getAt(recordId);
			record.set('id',recordId);
			this.criteriumCreatorDialog.show(record);
		} else {
			var record = new Ext.data.Record();
			record.set('id',-1);
			this.criteriumCreatorDialog.show(record);
		}
	}
});
