/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: MainPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Twan Verhofstad
 */

Ext.namespace('GO.bookmarks');

GO.bookmarks.getThumbUrl= function(logo, pub){
	
	if(GO.util.empty(pub)){
		return GO.url('core/thumb', {src:logo, h:16,w:16,pub:0});
	}else
	{
		return GO.settings.modules.bookmarks.url+logo;
	}

}

GO.bookmarks.MainPanel = function(config){

	if(!config)
	{
		config = {};
	}

	this.selectCategory = new GO.form.ComboBoxReset({
		fieldLabel: 'Category',
		hiddenName:'category_id',
		store: GO.bookmarks.comboCategoriesStore,
		displayField:'name',
		valueField:'id',
		triggerAction: 'all',
		editable: true,
		width:200,
		emptyText:t("Show all", "bookmarks"),
		selectOnFocus :false,
		listeners:{
			clear:function(){
				GO.bookmarks.groupingStore.baseParams['category']=0;
				GO.bookmarks.groupingStore.load();
			},
			select: function(combo,record) {
				GO.bookmarks.groupingStore.baseParams['category']=record.data.id;
				GO.bookmarks.groupingStore.load();
			//this.setValue(record.data[this.displayField]);
			}
		}
	});

	this.bookmarkColumnView = new GO.bookmarks.BookmarkColumnView({store:GO.bookmarks.groupingStore});
	
	this.bmColumn = new Ext.Panel({
		autoScroll: true,
		region:'center',
		id:'bookmarks-center-column-panel',
		border:false,
    items:this.bookmarkColumnView,
	});

	this.bmView=new GO.bookmarks.BookmarksView({store:GO.bookmarks.groupingStore});

	this.cardPanel = new Ext.Panel({
		region : 'center',
		layout:'card',
		border:false,
		activeItem: 0,
		layoutConfig: {
			deferredRender: true
		},
		items: [
			this.bmView,
			this.bmColumn
		]
	});

	config.tbar=new Ext.Toolbar({

		items: [{
			iconCls: 'btn-add',
			text: t("Add"),
			handler: function(){
				GO.bookmarks.showBookmarksDialog({edit:0});
			},
			scope:this
		},{
			text: t("Toggle view", "bookmarks"),
			iconCls: 'btn-thumbnails',
			handler: function(){
				this.nextLayout();
			},
			scope:this
		},{
			iconCls: 'no-btn-categories',
			text: t("Administrate categories", "bookmarks"),
			hidden: !GO.settings.modules.bookmarks.write_permission,
			handler: function(){
				if(!this.categoriesDialog)
				{
					this.categoriesDialog = new GO.bookmarks.ManageCategoriesDialog({
						listeners:{
							close:function(){
							},
							scope:this
						}
					});
					this.categoriesDialog.on('change', function(){
						}, this);
				}
				this.categoriesDialog.show();
			},
			scope: this
		},t("Category", "bookmarks"),this.selectCategory,'->',
		{
			xtype:'tbsearch',
			store:GO.bookmarks.groupingStore,
			onSearch: function(v) {
				this.store.baseParams.query = v
				this.store.reload();
			}
		}]
	});
 
	config.layout='fit';
	config.items=this.cardPanel;

	GO.bookmarks.MainPanel.superclass.constructor.call(this, config);
	
	this.init();
}

//-----------------------------------------------------------------------------


Ext.extend(GO.bookmarks.MainPanel, Ext.Panel, {
		
	init : function(){
		this.activeItemIndex = Ext.state.Manager.get('bookmark-active-panel');
		
		if(GO.util.empty(this.activeItemIndex))
			this.activeItemIndex = 0;
		
		this.cardPanel.activeItem = this.activeItemIndex;
	},

	// Walk through the available layouts
	nextLayout : function()
	{
		var itemCount = this.cardPanel.items.length; // Get the total number of items in the cardPanel
		var currentItemIndex = this.cardPanel.items.indexOf(this.cardPanel.getLayout().activeItem); // Get current index
		
		var nextItemIndex = 0;
		// If currentIndex is smaller then the total itemcount minus 1 (array indexes start at 0) then add +1 else go to 0
		if(currentItemIndex < (itemCount-1)){
			nextItemIndex = currentItemIndex+1;
		}

		this.cardPanel.layout.setActiveItem(nextItemIndex);
		this.activeItemIndex = nextItemIndex;
		this.saveState();
	},
	saveState : function(){
		Ext.state.Manager.getProvider().set('bookmark-active-panel', this.activeItemIndex);
	}
});

//----------------------------------------------------------------------------

//
// GLOBAL
//

// Bookmarks toevoegen of editten
GO.bookmarks.showBookmarksDialog = function(config){
	
	if(!this.bookmarksDialog){

		this.bookmarksDialog = new GO.bookmarks.BookmarksDialog({
			edit:config.edit, // leeg of bestaand record?
			listeners:{
				save:function(){
					GO.bookmarks.groupingStore.load();
				},
				scope:this
			}
		});
	}
	this.bookmarksDialog.show(config);
}

// Bookmark hyperlink openen, in GO tab of in browser tab
GO.bookmarks.openBookmark = function(record)
{
	if(record.data.behave_as_module == '1')
	{
		var panel = GO.mainLayout.openModule('bookmarks-id-'+record.id);
		if(panel)
		{
			return true;
		}
	}

	if(record.data.open_extern==0){
		var websiteTab = new GO.panel.IFrameComponent( {
			title : record.data.name,
			url:    record.data.content,
			border:false,
			closable:true
		})

		GO.mainLayout.tabPanel.add(websiteTab) // open nieuwe tab in group-office
		websiteTab.show();
	}
	else{
		window.open(record.data.content) // open in nieuw browser tab
	}
	
}

// bookmark verwijderen
GO.bookmarks.removeBookmark = function(record)
{
	if(confirm(t("Are you sure you want to delete this bookmark?", "bookmarks")))
	{

		GO.request({
			url : 'bookmarks/bookmark/delete', 
			params: {
				id: record.data.id
			},
			scope:this,

			callback: function(options, success, response){
				var responseParams = Ext.decode(response.responseText);
				if(!responseParams.success)
				{
					Ext.MessageBox.alert(t("Error"),responseParams.feedback);
				}
				else
				{
					GO.bookmarks.groupingStore.remove(record);
					GO.bookmarks.groupingStore.load();
				}
			}
		})
	}
}

// bookmark module toevoegen aan modulemanager
GO.moduleManager.addModule('bookmarks', GO.bookmarks.MainPanel, {
	title : t("Bookmarks", "bookmarks"),
	iconCls : 'go-tab-icon-bookmarks'
});
