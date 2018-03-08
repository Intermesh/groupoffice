/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: MainPanel.js 18927 2015-03-23 08:53:45Z wsmits $
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
		emptyText:GO.bookmarks.lang.showAll,
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

	this.searchField = new GO.form.SearchField({
		store: GO.bookmarks.groupingStore ,
		width:220
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
		tbar: [GO.bookmarks.lang.category+':',this.selectCategory,'-',GO.lang.strSearch+':',this.searchField],
		layoutConfig: {
			deferredRender: true
		},
		items: [
			this.bmView,
			this.bmColumn
		]
	});

	config.tbar=new Ext.Toolbar({
		cls:'go-head-tb',
		items: [{
			xtype:'htmlcomponent',
			html:GO.bookmarks.lang.name,
			cls:'go-module-title-tbar'
		},{
			iconCls: 'btn-add',
			text: GO.lang['cmdAdd'],
			cls: 'x-btn-text-icon',
			handler: function(){
				GO.bookmarks.showBookmarksDialog({edit:0});
			},
			scope:this
//		},{
//			itemId:'refresh',
//			iconCls: 'btn-refresh',
//			text: GO.lang['cmdRefresh'],
//			cls: 'x-btn-text-icon',
//			handler: function(){
//				GO.bookmarks.groupingStore.reload();
//				this.bookmarkColumnView.refresh();
//				this.bmView.DV.refresh();
//			},
//			scope: this
		},{ 
			text: GO.bookmarks.lang.toggle,
			iconCls: 'btn-refresh',
			cls: 'x-btn-text-icon',
			handler: function(){
				this.nextLayout();
			},
			scope:this
		},{
			iconCls: 'no-btn-categories',
			text: GO.bookmarks.lang.administrateCategories,
			cls: 'x-btn-text-icon',
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
	if(confirm(GO.bookmarks.lang.confirmDelete))
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
					Ext.MessageBox.alert(GO.lang['strError'],responseParams.feedback);
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
	title : GO.bookmarks.lang.bookmarks,
	iconCls : 'go-tab-icon-bookmarks'
});
