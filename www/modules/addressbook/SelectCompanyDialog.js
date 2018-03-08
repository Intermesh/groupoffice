/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: SelectCompanyDialog.js 15314 2013-07-26 09:23:02Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * 
 * Params:
 * 
 * linksStore: store to reload after items are linked
 * gridRecords: records from grid to link. They must have a link_id and link_type
 * fromLinks: array with link_id and link_type to link
 */
 
 /**
 * @class GO.dialog.SelectCompany
 * @extends Ext.Window
 * A window to select a number of User-Office user Users.
 * 
 * @cfg {Function} handler A function called when the Add or Ok button is clicked. The grid will be passed as argument.
 * @cfg {Object} scope The scope of the handler
 * 
 * @constructor
 * @param {Object} config The config object
 */
 
GO.addressbook.SelectCompanyDialog = function(config){
	
	Ext.apply(this, config);
	
	  
  this.searchField = new GO.form.SearchField({
		width:320
  });

	this.addressbooksGrid = new GO.addressbook.AddresbooksGrid({
		region:'north',
		height:250
	});

	this.addressbooksGrid.getSelectionModel().on('rowselect', function(sm, rowIndex, r){
		var record = this.addressbooksGrid.getStore().getAt(rowIndex);
		this.grid.store.baseParams.books='["'+record.get("id")+'"]';
		this.grid.store.load();
	}, this);
	
	
	this.mailingsFilterPanel= new GO.addressbook.AddresslistsMultiSelectGrid({
		id: 'ab-scom-mailingsfilter-panel'
	});

	this.mailingsFilterPanel.on('change', function(grid, addresslist_filter){	
		this.grid.store.baseParams.addresslist_filter = Ext.encode(addresslist_filter);
		this.grid.store.load();		
	}, this);


	var westPanel = new Ext.Panel({
		layout:'border',
		border:false,
		region:'west',
		width:230,
		split:true,
		items:[this.addressbooksGrid,this.mailingsFilterPanel]
	});

		
	this.grid = this.companiesGrid = new GO.addressbook.CompaniesGrid({
		region:'center',
		tbar: [
    GO.lang['strSearch']+': ', ' ', this.searchField,{
				handler: function()
				{
					if(!this.advancedSearchWindow)
					{
						this.advancedSearchWindow = GO.addressbook.advancedSearchWindow = new GO.addressbook.AdvancedSearchWindow();
						this.advancedSearchWindow.on('ok', function(win){

//						this.grid.store.baseParams.advancedQuery=this.searchField.getValue();
						this.searchField.setValue("[ "+GO.addressbook.lang.advancedSearch+" ]");
						this.searchField.setDisabled(true);
						this.grid.store.load();

						}, this)
					}
					this.advancedSearchWindow.show({dataType:'companies',masterPanel : this });
				},
				text: GO.addressbook.lang.advancedSearch,
				scope: this,
				style:'margin-left:5px;'
			},{
				handler: function()
				{
					this.searchField.setValue("");
					delete this.grid.store.baseParams.advancedQueryData;
					this.searchField.setDisabled(false);
					this.grid.store.load();
				},
				text: GO.lang.cmdReset,
				scope: this
			}
    ]});
    
  //dont filter on address lists when selecting
  delete this.grid.store.baseParams.enable_mailings_filter;
		
	this.searchField.store=this.grid.store;
	
	var focusSearchField = function(){
		this.searchField.focus(true);
	};
	
	
	
	
	GO.addressbook.SelectCompanyDialog.superclass.constructor.call(this, {
    layout: 'border',
    focus: focusSearchField.createDelegate(this),
		modal:false,
		height:600,
		width:800,
		closeAction:'hide',
		title: GO.addressbook.lang['strSelectCompany'],
		items: [westPanel,this.grid],
		buttons: [
			{
				text: GO.lang['cmdOk'],
				handler: function (){
					this.callHandler(true);
				},
				scope:this
			},
			{
				text: GO.lang['cmdAdd'],
				handler: function (){
					this.callHandler(false);
				},
				scope:this
			},
			{
				text: GO.addressbook.lang.addAllSearchResults,
				handler: function (){
					if(confirm(GO.addressbook.lang.confirmAddAllSearchResults)){
						this.callHandler(true, true);
					}
				},
				scope:this
			},
			{
				text: GO.lang['cmdClose'],
				handler: function(){this.hide();},
				scope: this
			}
		]
    });
};

Ext.extend(GO.addressbook.SelectCompanyDialog, Ext.Window, {

	show : function(){
		
		this.mailingsFilterPanel.store.load();
		
		if(!this.grid.store.loaded)
		{
			this.grid.store.load();
		}
		GO.addressbook.SelectCompanyDialog.superclass.show.call(this);
	},
	
	//private
	callHandler : function(hide, allResults){
		if(this.handler)
		{
			if(!this.scope)
			{
				this.scope=this;
			}
			
			var handler = this.handler.createDelegate(this.scope, [this.grid, allResults]);
			handler.call();
		}
		if(hide)
		{
			this.hide();
		}
	}	
	
});