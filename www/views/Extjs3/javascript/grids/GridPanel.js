/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: GridPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
/**
 * @class GO.grid.GridPanel
 * @extends Ext.grid.GridPanel
 * This class represents the primary interface of a component based grid control.
 * 
 * This extension of the default Ext grid implements some basic Group-Office functionality
 * like deleting items.
 *  
 * <br><br>Usage:
 * <pre><code>var grid = new Ext.grid.GridPanel({
    store: new Ext.data.Store({
        reader: reader,
        data: xg.dummyData
    }),
    columns: [
        {id:'company', header: "Company", width: 200, sortable: true, dataIndex: 'company'},
        {header: "Price", width: 120, sortable: true, renderer: Ext.util.Format.usMoney, dataIndex: 'price'},
        {header: "Change", width: 120, sortable: true, dataIndex: 'change'},
        {header: "% Change", width: 120, sortable: true, dataIndex: 'pctChange'},
        {header: "Last Updated", width: 135, sortable: true, renderer: Ext.util.Format.dateRenderer('m/d/Y'), dataIndex: 'lastChange'}
    ],
    viewConfig: {
        forceFit: true
    },
    sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
    width:600,
    height:300,
    frame:true,
    title:'Framed with Checkbox Selection and Horizontal Scrolling',
    iconCls:'icon-grid'
});</code></pre>
 * <b>Note:</b> Although this class inherits many configuration options from base classes, some of them
 * (such as autoScroll, layout, items, etc) won't function as they do with the base Panel class.<br>
 * <br>
 * To access the data in a Grid, it is necessary to use the data model encapsulated
 * by the {@link #store Store}. See the {@link #cellclick} event.
 * @constructor
 * @param {Object} config The config object
 */


GO.grid.GridPanel =Ext.extend(Ext.grid.GridPanel, {
	
	lastSelectedIndex : false,
	currentSelectedIndex : false,
	primaryKey : 'id', //Set this value if your record has a PK of multiple columns (eg ['user_id','project_id'])
	editDialogConfig:null,
	
	loadMask:true,
	
	initComponent : function(){
		
		
		if(!this.view && !this.viewConfig){
			this.view = new Ext.grid.GridView({
				autoFill: true,
				forceFit: true,
				emptyText: t("No items to display")
			});
		}
		
		if(this.view instanceof Ext.grid.GroupingView){
			//GroupingViews sometimes have rendering issues
			this.addListener('show', function(){
				this.doLayout();
			}, this);
		}

		if(!this.keys)
		{
			this.keys=[];
		}
	
		if(!this.store)
		{
			this.store=this.ds;
		}
		
		if(this.store.model && GO.customfields && GO.customfields.columns[this.store.model]){
			for(var i=0;i<GO.customfields.columns[this.store.model].length;i++)
			{
				if(GO.customfields.nonGridTypes.indexOf(GO.customfields.columns[this.store.model][i].datatype)==-1){
					if(GO.customfields.columns[this.store.model][i].exclude_from_grid != 'true')
					{
            if(!this.columns){
							this.columns = this.cm.columns;
						}              
						this.columns.push(GO.customfields.columns[this.store.model][i]);
					}
				}
			}	
		}

		if(!this.noDelete){
			this.keys.push({
				key: Ext.EventObject.DELETE,
				fn: function(key, e){
					//sometimes there's a search input in the grid, so dont delete when focus is on an input
					if(e.target.tagName!='INPUT')
						this.deleteSelected(this.deletethis);
				},
				scope:this
			});
		}
    
		if(this.paging)
		{
			if(typeof(this.paging)=='boolean')
				this.paging=parseInt(GO.settings['max_rows_list']);

			if(!this.bbar)
			{
				this.bbar = new Ext.PagingToolbar({
					cls: 'go-paging-tb',
					store: this.store,
					pageSize: this.paging,
					displayInfo: true,
					displayMsg: t("Displaying items {0} - {1} of {2}"),
					emptyMsg: t("No items to display")
				});
			}
    
			if(!this.store.baseParams)
			{
				this.store.baseParams={};
			}
			this.store.baseParams['limit']=this.paging;
		}
		
		this.store.on('load', function(){
//			this.changed=false;
			
			if(this.store.reader && this.store.reader.jsonData){
				if(this.store.reader.jsonData.title)
					this.setTitle(this.store.reader.jsonData.title);
				
//				if(this.store.reader.jsonData.emptyText){
//					this.getView().emptyText=this.store.reader.jsonData.emptyText;
//				}
			} 
			
			
		}, this);

	
		if(!this.sm && !this.disableSelection)
			this.sm=this.selModel=new Ext.grid.RowSelectionModel();
	
		if(this.standardTbar){

			this.tbar = this.tbar ? this.tbar : [];
			if(!this.hideSearchField){
				this.tbar.unshift(					
					'->',{
						xtype: 'tbsearch',
						store: this.store,
						onSearch: function(v) { 
							this.store.baseParams['query'] = v;
							this.store.reload();
						}
					}				
				);
			}
			this.tbar.unshift({
				itemId:'add',
				iconCls: 'btn-add',							
				text: t("Add"),
				cls: 'x-btn-text-icon',
				handler: this.btnAdd,
				disabled:this.standardTbarDisabled,
				scope: this
			},{
				itemId:'delete',
				iconCls: 'btn-delete',
				text: t("Delete"),
				cls: 'x-btn-text-icon',
				disabled:this.standardTbarDisabled,
				handler: function(){
					this.deleteSelected();
				},
				scope: this
			});
			
			this.standardTbarConfig = this.standardTbarConfig ? this.standardTbarConfig : {};
			this.standardTbarConfig.items = this.tbar;
			this.tbar = new Ext.Toolbar(this.standardTbarConfig);
		}
		
		
		
		
		GO.grid.GridPanel.superclass.initComponent.call(this);
		
		//create a delayed rowselect event so that when a user repeatedly presses the
		//up and down button it will only load if it stays on the same record for 400ms
		this.addEvents({
			'delayedrowselect':true
		});



		this.on("rowcontextmenu", function(grid, rowIndex, e) {
			e.stopEvent();

			this.rowClicked=true;

			var sm =this.getSelectionModel();
			if(sm.isSelected(rowIndex) !== true) {
				sm.clearSelections();
				sm.selectRow(rowIndex);
			}
		}, this);

		this.on('rowclick', function(grid, rowIndex, e){
			var record = this.getSelectionModel().getSelected();

			if(!e.ctrlKey && !e.shiftKey)
			{
				if(record){
					this.lastSelectedIndex= this.currentSelectedIndex;
					this.currentSelectedIndex= this.getSelectionModel().last;
					this.fireEvent('delayedrowselect', this, rowIndex, record);
				}
			}
		
			if(record)
				this.rowClicked=true;
		}, this);
		
		//no delay on this
		this.getSelectionModel().on("rowselect",function(sm, rowIndex, r){
			if(!this.rowClicked)
			{
				this.lastSelectedIndex= this.currentSelectedIndex;
				this.currentSelectedIndex= this.getSelectionModel().last;
			}
		}	,this);

		this.getSelectionModel().on("rowselect",function(sm, rowIndex, r){
			if(!this.rowClicked)
			{
				var record = this.getSelectionModel().getSelected();
				if(record==r)
				{					
					this.fireEvent('delayedrowselect', this, rowIndex, r);
				}
			}
			this.rowClicked=false;
		}, this, {
			delay:250
		});
		
		//Load the datastore when render event fires if autoLoadStore is true
		this.on('render',function(grid)
		{
			if(this.autoLoadStore)
				grid.store.load();
		}, this);
	
		this.on('rowdblclick', function(grid, rowIndex){
			var record = grid.getStore().getAt(rowIndex);			
			this.dblClick(grid, record, rowIndex)		
		}, this);

		if(GO.util.isMobileOrTablet()) {
			this.on('rowclick', function(grid, rowIndex){
				var record = grid.getStore().getAt(rowIndex);			
				this.dblClick(grid, record, rowIndex)		
			}, this);
		}
	
	},

	deleteConfig : {},

	/**
	 *@cnf {Boolean} Load the datastore into the grid when it's rendered for the first time
	 */
	autoLoadStore: false,

	/**
	 * @cfg {Boolean} paging True to set the store's limit parameter and render a bottom
	 * paging toolbar.
	 */
	paging : false,
	
	
//	selectNextAfterDelete : function(){		
//		var old = this.lastSelectedIndex;
//		if(this.currentSelectedIndex>this.lastSelectedIndex){			
//			//return value is always false somehow so we check with getSelected
//			this.getSelectionModel().selectRow(this.currentSelectedIndex);
//			
//			if(!this.getSelectionModel().getSelected())				
//				this.getSelectionModel().selectLastRow();
//		}else
//		{
//			//return value is always false somehow so we check with getSelected
//			this.getSelectionModel().selectRow(this.currentSelectedIndex-1);			
//			if(!this.getSelectionModel().getSelected())
//				this.getSelectionModel().selectFirstRow();
//		}
//		
//		this.lastSelectedIndex=old;
//	},
	selectNextAfterDelete : function(selectNext){	

		switch(selectNext){
			case "last":
				this.getSelectionModel().selectLastRow();
				break;
				
			case "first":
				this.getSelectionModel().selectFirstRow();
				break;
				
			default:			
				//console.log("DEFAULT");
				//this.getSelectionModel().selectRecords([selectNext]);	
				var index = this.store.indexOfId(selectNext.id);
//				console.log(index);
				this.getSelectionModel().selectRow(index);
				break;
		}
//		if(!GO.util.empty(selectNext)){
//			console.log("NIET LEEG");
//			this.getSelectionModel().selectRecords([selectNext]);
//		} else {
//			console.log("LEEG");
//			this.getSelectionModel().selectFirstRow();
//			var old = this.lastSelectedIndex;
//			if(this.currentSelectedIndex>this.lastSelectedIndex){			
//				//return value is always false somehow so we check with getSelected
//				this.getSelectionModel().selectRow(this.currentSelectedIndex);
//
//				if(!this.getSelectionModel().getSelected())				
//					this.getSelectionModel().selectLastRow();
//			}else
//			{
//				//return value is always false somehow so we check with getSelected
//				this.getSelectionModel().selectRow(this.currentSelectedIndex-1);			
//				if(!this.getSelectionModel().getSelected())
//					this.getSelectionModel().selectFirstRow();
//			}
//
//			this.lastSelectedIndex=old;
//		}
	},

	/**
	 * Sends a delete request to the remote store. It will send the selected keys in json
	 * format as a parameter. (delete_keys by default.)
	 *
	 * @param {Object} options An object which may contain the following properties:<ul>
     * <li><b>deleteParam</b> : String (Optional)<p style="margin-left:1em">The name of the
     * parameter that will send to the store that holds the selected keys in JSON format.
     * Defaults to "delete_keys"</p>
     * </li>
	 *
	 */
	deleteSelected : function(config){

		config = config || {};

		Ext.apply(config, this.deleteConfig);

		if(!config['deleteParam'])
		{
			config['deleteParam']='delete_keys';
		}
		
		var params={}
		//if Primary key is array
		if(Ext.isArray(this.primaryKey)){
		  var pkeys = [];
		  var records = this.selModel.getSelections();
		  for (var i=0;i<this.selModel.selections.keys.length;i++) {
			var pk = {};
			for (var j=0;j<this.primaryKey.length;j++)
			  pk[this.primaryKey[j]] = records[i].data[this.primaryKey[j]];
			pkeys.push(pk);
		  }
		  params[config.deleteParam] = Ext.encode(pkeys);
		} else {
		  params[config.deleteParam]=Ext.encode(this.selModel.selections.keys);
		}
		  
		var deleteItemsConfig = {
			store:this.store,
			grid: this,
			params: params,
			count: this.selModel.selections.keys.length,
			extraWarning: config.extraWarning || "",
			noConfirmation: config.noConfirmation
		};
		
		var selectedArray = this.selModel.getSelections();
		
		this.move = 'up'; // Default move up
		
		if(this.currentSelectedIndex<this.lastSelectedIndex)
			this.move = 'down'; // check for move down
	
			
		var indexInstore;
		var itemIndexAfterSelected;
		// Calculate wich item should be selected after the deletion
		if(selectedArray.length > 1){ // If you have selected more than one item
			// Select the item after the latest selected item
			var highestIndex = 0;
			for(var i=0;i<selectedArray.length;i++){ // loop through the selected items
				indexInstore = this.store.indexOf(selectedArray[i]);
				if(highestIndex < indexInstore) // Check if the index in the store is higher than the current index
					highestIndex = indexInstore; // If so, change the highest index
			}
			
			// Check if there is an item after the latest selected index
			itemIndexAfterSelected = highestIndex+1;
			
		} else {
			// Select the item after the (single)selected item
			indexInstore = this.store.indexOf(this.selModel.getSelected());
			
			if(this.move =='down')
				itemIndexAfterSelected = indexInstore-1;
			else
				itemIndexAfterSelected = indexInstore+1;
			
		}
		
		var selectThisRecordAfterDelete = this.store.getAt(itemIndexAfterSelected);
		if(!selectThisRecordAfterDelete){
			if(this.move =='down')
				selectThisRecordAfterDelete = "first";
			else
				selectThisRecordAfterDelete = "last";
		}	
		
		deleteItemsConfig['selectRecordAfterDelete']=selectThisRecordAfterDelete;
		
		if(config.callback)
		{
			deleteItemsConfig['callback']=config.callback;
		}
		if(config.success)
		{
			deleteItemsConfig['success']=config.success;
		}
		if(config.failure)
		{
			deleteItemsConfig['failure']=config.failure;
		}
		if(config.scope)
		{
			deleteItemsConfig['scope']=config.scope;
		}

		this.getView().scrollToTopOnLoad=false;
		GO.deleteItems(deleteItemsConfig);
		
//		this.changed=true;
	},
	/**
	 * Fetch alle the row dat aof the grid's store
	 * @param {boolean} dirtyOnly fetch only attributes of dirty rows (but all ids)
	 * @returns {Array}
	 */
	getGridData : function(dirtyOnly){

		var data = [];
		var record;

		for (var i = 0; i < this.store.data.items.length;  i++)
		{
			if(dirtyOnly && !this.store.data.items[i].dirty) {
				data.push({id: this.store.data.items[i].data.id});
				continue;
			}
			var r = this.store.data.items[i].data;
			record={};

			for(var key in r)
			{
				record[key]=r[key];
			}
			data.push(record);
		}

		return data;
	},

	numberRenderer : function(v)
	{
		return GO.util.numberFormat(v);
	},
	
	btnAdd : function(){
		if(this.editDialogClass){
			this.showEditDialog();
		}
	},
	
	dblClick : function(grid, record, rowIndex){
		if(this.editDialogClass){
			this.showEditDialog(record.id, {}, record);
		}
	},
	
	showEditDialog : function(id, config, record){
        config = config || {};
		if(!this.editDialog){
			this.editDialog = new this.editDialogClass(this.editDialogConfig);

			this.editDialog.on('save', function(){   
				this.store.reload();   
//				this.changed=true;
			}, this);	
		}
		
		if(Ext.isArray(this.primaryKey) && record) {
		  for (var j=0;j<this.primaryKey.length;j++)
			this.editDialog.formPanel.baseParams[this.primaryKey[j]] = record.data[this.primaryKey[j]];
		}
		
		if(this.relatedGridParamName)
			this.editDialog.formPanel.baseParams[this.relatedGridParamName]=this.store.baseParams[this.relatedGridParamName];
		
		this.editDialog.show(id, config);	  
	}
	
});


GO.grid.EditorGridPanel = function(config)
{
	if(!config)
	{
		config={};
	}

	if(!config.keys)
	{
		config.keys=[];
	}

	if(!config.store)
	{
		config.store=config.ds;
	}

	config.keys.push({
		key: Ext.EventObject.DELETE,
		fn: function(key, e){
			//sometimes there's a search input in the grid, so dont delete when focus is on an input
			if(e.target.tagName!='INPUT')
				this.deleteSelected(this.deleteConfig);
		},
		scope:this
	});

	if(config.paging)
	{
		if(!config.bbar)
		{
			config.bbar = new Ext.PagingToolbar({
				cls: 'go-paging-tb',
				store: config.store,
				pageSize: parseInt(GO.settings['max_rows_list']),
				displayInfo: true,
				displayMsg: t("Displaying items {0} - {1} of {2}"),
				emptyMsg: t("No items to display")
			});
		}

		if(!config.store.baseParams)
		{
			config.store.baseParams={};
		}
		config.store.baseParams['limit']=parseInt(GO.settings['max_rows_list']);
	}

	GO.grid.EditorGridPanel.superclass.constructor.call(this, config);

	this.addEvents({
		delayedrowselect:true
	});

	this.on('rowclick', function(grid, rowIndex, e){
		if(!e.ctrlKey && !e.shiftKey)
		{
			var record = this.getSelectionModel().getSelected();
			this.lastSelectedRecord = this.currentSelectedRecord;
			this.currentSelectedRecord=record;
			
			this.fireEvent('delayedrowselect', this, rowIndex, record);
		}
		this.rowClicked=true;
	}, this);
	
	//no delay on this
	this.getSelectionModel().on("rowselect",function(sm, rowIndex, r){
		if(!this.rowClicked)
		{
			this.lastSelectedIndex= this.currentSelectedIndex;
			this.currentSelectedIndex= this.getSelectionModel().last;
		}
	}	,this);

	this.getSelectionModel().on("rowselect",function(sm, rowIndex, r){
		if(!this.rowClicked)
		{
			var record = this.getSelectionModel().getSelected();
			if(record==r)
			{
				this.fireEvent('delayedrowselect', this, rowIndex, r);
			}
		}
		this.rowClicked=false;
	}, this, {
		delay:250
	});
}

Ext.extend(GO.grid.EditorGridPanel, Ext.grid.EditorGridPanel, {

	lastSelectedIndex : false,
	currentSelectedIndex : false,
	
	deleteConfig : {},

	/**
	 * @cfg {Boolean} paging True to set the store's limit parameter and render a bottom
	 * paging toolbar.
	 */

	paging : false,

	/**
	 * Sends a delete request to the remote store. It will send the selected keys in json
	 * format as a parameter. (delete_keys by default.)
	 *
	 * @param {Object} options An object which may contain the following properties:<ul>
     * <li><b>deleteParam</b> : String (Optional)<p style="margin-left:1em">The name of the
     * parameter that will send to the store that holds the selected keys in JSON format.
     * Defaults to "delete_keys"</p>
     * </li>
	 *
	 */
	deleteSelected : GO.grid.GridPanel.prototype.deleteSelected,

	getGridData : GO.grid.GridPanel.prototype.getGridData,

	numberRenderer : GO.grid.GridPanel.prototype.numberRenderer,
	
//	selectNextAfterDelete : function(selectNext){	
//		if(!GO.util.empty(selectNext)){
//			console.log("NIET LEEG");
//			this.getSelectionModel().selectRecords([selectNext]);
//		} else {
//			console.log("LEEG");
//			var old = this.lastSelectedIndex;
//			if(this.currentSelectedIndex>this.lastSelectedIndex){			
//				//return value is always false somehow so we check with getSelected
//				this.getSelectionModel().selectRow(this.currentSelectedIndex);
//
//				if(!this.getSelectionModel().getSelected())				
//					this.getSelectionModel().selectLastRow();
//			}else
//			{
//				//return value is always false somehow so we check with getSelected
//				this.getSelectionModel().selectRow(this.currentSelectedIndex-1);			
//				if(!this.getSelectionModel().getSelected())
//					this.getSelectionModel().selectFirstRow();
//			}
//
//			this.lastSelectedIndex=old;
//		}
//	},
selectNextAfterDelete : function(selectNext){	
		
		switch(selectNext){
			case "last":
				this.getSelectionModel().selectLastRow();
				break;
				
			case "first":
				this.getSelectionModel().selectFirstRow();
				break;
				
			default:			
				//console.log("DEFAULT");
				//this.getSelectionModel().selectRecords([selectNext]);	
				var index = this.store.indexOfId(selectNext.id);
//				console.log(index);
				this.getSelectionModel().selectRow(index);
				break;
		}
//		if(!GO.util.empty(selectNext)){
//			console.log("NIET LEEG");
//			this.getSelectionModel().selectRecords([selectNext]);
//		} else {
//			console.log("LEEG");
//			this.getSelectionModel().selectFirstRow();
//			var old = this.lastSelectedIndex;
//			if(this.currentSelectedIndex>this.lastSelectedIndex){			
//				//return value is always false somehow so we check with getSelected
//				this.getSelectionModel().selectRow(this.currentSelectedIndex);
//
//				if(!this.getSelectionModel().getSelected())				
//					this.getSelectionModel().selectLastRow();
//			}else
//			{
//				//return value is always false somehow so we check with getSelected
//				this.getSelectionModel().selectRow(this.currentSelectedIndex-1);			
//				if(!this.getSelectionModel().getSelected())
//					this.getSelectionModel().selectFirstRow();
//			}
//
//			this.lastSelectedIndex=old;
//		}
	},


	/**
	 * Checks if a grid cell is valid
	 * @param {Integer} col Cell column index
	 * @param {Integer} row Cell row index
	 * @return {Boolean} true = valid, false = invalid
	 */
	isCellValid:function(col, row) {
		if(!this.colModel.isCellEditable(col, row)) {
			return true;
		}
		var ed = this.colModel.getCellEditor(col, row);
		if(!ed) {
			return true;
		}
		var record = this.store.getAt(row);
		if(!record) {
			return true;
		}
		var field = this.colModel.getDataIndex(col);
		ed.field.setValue(record.data[field]);
		return ed.field.isValid(true);
	} // end of function isCellValid

	/**
	 * Checks if grid has valid data
	 * @param {Boolean} editInvalid true to automatically start editing of the first invalid cell
	 * @return {Boolean} true = valid, false = invalid
	 */
	,
	isValid:function(editInvalid) {
		var cols = this.colModel.getColumnCount();
		var rows = this.store.getCount();
		var r, c;
		var valid = true;
		for(r = 0; r < rows; r++) {
			for(c = 0; c < cols; c++) {
				valid = this.isCellValid(c, r);
				if(!valid) {
					break;
				}
			}
			if(!valid) {
				break;
			}
		}
		if(editInvalid && !valid) {
			this.startEditing(r, c);
		}
		return valid;
	} // end of function isValid
});
