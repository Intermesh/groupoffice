/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: SelectContactDialog.js 22252 2018-01-25 14:07:41Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 *
 * Params:
 * 
 * linksStore: store to reload after items are linked
 * gridRecords: records from grid to link. They must have a link_id and link_type
 * fromLinks: array with link_id and link_type to link
 */
 
 /**
 * @class GO.dialog.SelectContact
 * @extends Ext.Window
 * A window to select a number of User-Office user Users.
 * 
 * @cfg {Function} handler A function called when the Add or Ok button is clicked. The grid will be passed as argument.
 * @cfg {Object} scope The scope of the handler
 * 
 * @constructor
 * @param {Object} config The config object
 */
 
GO.addressbook.SelectContactDialog = function(config){
	
	Ext.apply(this, config);
	
	this.searchField = new GO.form.SearchField({
		width:320
  });

	this.addressbooksGrid = new GO.addressbook.AddresbooksGrid({
		region:'north',
		height:250,
		store:new GO.data.JsonStore({
			url: GO.url('addressbook/addressbook/store'),
			baseParams: {
				'auth_type' : 'read'
				},
			root: 'results',
			totalProperty: 'total',
			id: 'id',
			fields: ['id','name','owner','checked'],
			remoteSort: true
		})
	});

	this.addressbooksGrid.on('change', function(grid, abooks, records)
	{
		var books = Ext.encode(abooks);
		this.grid.store.baseParams.books=books;
		this.grid.store.load();
	}, this);
	
	
	this.mailingsFilterPanel= new GO.addressbook.AddresslistsMultiSelectGrid({
		id: 'ab-sc-mailingsfilter-panel'
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


	this.grid = this.contactsGrid = new GO.addressbook.ContactsGrid({
		region:'center',
		tbar: [
    t("Search")+': ', ' ', this.searchField,{
				handler: function()
				{
					if(!this.advancedSearchWindow)
					{
						this.advancedSearchWindow = GO.addressbook.advancedSearchWindow = new GO.addressbook.AdvancedSearchWindow();
						this.advancedSearchWindow.on('ok', function(win){

//						this.grid.store.baseParams.advancedQuery=this.searchField.getValue();
						this.searchField.setValue("[ "+t("Advanced search", "addressbook")+" ]");
						this.searchField.setDisabled(true);
						this.grid.store.load();

						}, this)
					}
					this.advancedSearchWindow.show({dataType:'contacts',masterPanel : this});
				},
				text: t("Advanced search", "addressbook"),
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
				text: t("Reset"),
				scope: this
			}
    ]});
    
  //dont filter on address lists when selecting
  delete this.grid.store.baseParams.enable_mailings_filter;

  //don't save filter but send it each time
  this.grid.store.baseParams.disable_filter_save="1";
		
	this.searchField.store=this.grid.store;
	
	var focusSearchField = function(){
		this.searchField.focus(true);
	};
	
	GO.addressbook.SelectContactDialog.superclass.constructor.call(this, {
    layout: 'border',
		modal:false,
		focus: focusSearchField.createDelegate(this),
		height:600,
		width:800,
		closeAction:'hide',
		title: t("Select contact", "addressbook"),
		items: [westPanel, this.grid],
		buttons: [
			{
				text: t("Ok"),
				handler: function (){
					this.callHandler(true);
				},
				scope:this
			},
			{
				text: t("Add"),
				handler: function (){
					this.callHandler(false);
				},
				scope:this
			},
			{
				text: t("Add all search results", "addressbook"),
				handler: function (){
					if(confirm(t("Are you sure you want to add all the search results to the address list?", "addressbook"))){
						this.callHandler(true, true);
					}
				},
				scope:this
			},
			{
				text: t("Close"),
				handler: function(){this.hide();},
				scope: this
			}
		],
                tbar: [{
                        style: " margin-bottom: 10px; ",
                        iconCls: 'btn-add',
			text: t("Add new", "addressbook"),
			cls: 'x-btn-text-icon', 
                        handler: function(){
                            if(!this.contactDialog) {
                                this.contactDialog = new GO.addressbook.ContactDialog({});
                                
                            }
                            this.contactDialog.show(0 ,{values: {company_id: this.company_id}});
                        },
                        scope: this
                        
                }] 
    });
};

Ext.extend(GO.addressbook.SelectContactDialog, Ext.Window, {

	show : function(config){		
		
		config = config || {};
		
		if(config.values) {
			this.addressbookId = config.values.addressbookId || 0;
			this.company_id = config.values.company_id || 0;
		} else {
			this.company_id = 0;
			this.addressbookId = 0;
		}
		
		
		this.addressbooksGrid.setDisabled(this.addressbookId>0);
		
		GO.addressbook.SelectContactDialog.superclass.show.call(this);
		
		//if(!this.grid.store.loaded)
		//{
		
		this.mailingsFilterPanel.store.load();
		
		
		if(!this.addressbooksGrid.store.loaded) {
			this.addressbooksGrid.store.load({
				callback:function(){
					if(this.addressbookId>0) {
						this.addressbooksGrid.applyFilter([this.addressbookId]);
					}
					var books = this.addressbooksGrid.getSelected();
					this.grid.store.baseParams.books=Ext.encode(books);
					this.grid.store.load();
				},
				scope:this
			});
		} else {
			this.grid.store.load();
			if(this.addressbookId>0) {
				this.addressbooksGrid.applyFilter([this.addressbookId]);
			}
		}
		
		//}
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
