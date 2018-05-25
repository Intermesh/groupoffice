/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LiveGridPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author michael de Hart <mdhart@intermesh.nl>
 */
 
/**
 * @class GO.grid.LiveGridPanel
 * @extends Ext.ux.grid.livegrid.GridPanel
 * 
 * Extension to the Ext.ux.LiveGrid plugin
 * Has all functions of the GridPanel
 * 
 * This extension of the default Ext grid implements some basic Group-Office functionality
 * like deleting items.
 * 
 * @constructor
 * @param {Object} config The config object
 */
GO.grid.LiveGridPanel = Ext.extend(Ext.ux.grid.livegrid.GridPanel, {
	
	lastSelectedIndex : false,
	currentSelectedIndex : false,
	primaryKey : 'id', //Set this value if your record has a PK of multiple columns (eg ['user_id','project_id'])
	
	initComponent : function(){
		
		
		if(!this.view && !this.viewConfig){
			this.view = new Ext.ux.grid.livegrid.GridView({
				autoFill: true,
				forceFit: true,
				emptyText: t("No items to display"),
				nearLimit: 2
			});
		}

		if(!this.keys)
		{
			this.keys=[];
		}
	/*
		if(!this.store)
		{
			this.store=this.ds;
		}
	*/	
		//Add customfields to the store and columns (copied from GridPanel)
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
    
		//TODO: use max row list for scroll size
		//this.paging=parseInt(GO.settings['max_rows_list']);
		if(!this.bbar)
		{
			this.bbar = new Ext.ux.grid.livegrid.Toolbar({
				cls: 'go-paging-tb',
				view        : this.view,
				store: this.store,
				displayInfo : true,
				displayMsg: t("Displaying items {0} - {1} of {2}"),
				emptyMsg: t("No items to display")
			})
		}

		
		this.store.on('load', function(){
//			this.changed=false;
			
			if(this.store.reader.jsonData){
				if(this.store.reader.jsonData.title)
					this.setTitle(this.store.reader.jsonData.title);
				
//				if(this.store.reader.jsonData.emptyText){
//					this.getView().emptyText=this.store.reader.jsonData.emptyText;
//				}
			} 
			
			
		}, this);
	
		if(typeof(this.loadMask)=='undefined')
			this.loadMask=true;
	
		if(!this.sm && !this.disableSelection)
			this.sm=this.selModel=new Ext.ux.grid.livegrid.RowSelectionModel();
	
		if(this.standardTbar){

			this.tbar = this.tbar ? this.tbar : [];
			if(!this.hideSearchField){
				this.tbar.unshift(					
					'-',
					new GO.form.SearchField({
						store: this.store,
						width:150
					})					
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

		GO.grid.LiveGridPanel.superclass.initComponent.call(this);
		
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
			console.log('hi');
			var record = grid.getStore().getAt(rowIndex);			
			this.dblClick(grid, record, rowIndex)		
		}, this);
	
	},

	deleteConfig : {},

	/**
	 * TODO: uses autoLoad on livestore
	 */
	autoLoadStore: false,

	/**
	 * TODO: paging is always false in livegrid
	 */
	paging : false,
	
	selectNextAfterDelete : GO.grid.GridPanel.selectNextAfterDelete,

	deleteSelected : GO.grid.GridPanel.deleteSelected,

	getGridData : GO.grid.GridPanel.getGridData,

	numberRenderer : GO.grid.GridPanel.numberRenderer,
	
	btnAdd : GO.grid.GridPanel.btnAdd,
	
	dblClick : function(grid, record, rowIndex){
		if(this.editDialogClass){
			this.showEditDialog(record.id, {}, record);
		}
	},
	
	showEditDialog : GO.grid.GridPanel.showEditDialog
	
});
