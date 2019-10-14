go.modules.community.bookmarks.MainPanel = Ext.extend(go.modules.ModulePanel, {

	// Will make a single item fit in this panel. We'll change this later.
	layout : "fit",
	init : function(){
		this.activeItemIndex = Ext.state.Manager.get('bookmark-active-panel');
		
		if(GO.util.empty(this.activeItemIndex))
			this.activeItemIndex = 0;
		
		this.cardPanel.activeItem = this.activeItemIndex;
	},

	// Walk through the available layouts
	nextLayout : function() {
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
	saveState: function(){
		Ext.state.Manager.getProvider().set('bookmark-active-panel', this.activeItemIndex);
	},
	initComponent: function() {

		this.store = new go.data.GroupingStore({
			fields: ['id','categoryId',{
				name: "category", 
				type: "relation",
				sortType: function(category) {
					return category.name;
				}
			},'createdBy','name','content','description','logo','openExtern','permissionLevel','thumb','behaveAsModule'],
			entityStore: "Bookmark",
			baseParams: {
				limit:0
			},
			groupField:'category',
			sortInfo: {
				field: 'name',
				direction: 'ASC'
			},
			remoteGroup:false,
			remoteSort:false
		});

		this.selectCategory = new go.form.ComboBoxReset({
			fieldLabel: 'Category',
			hiddenName:'categoryId',
			store:new go.data.Store({
				baseParams: {
					limit:0
				},
				fields: ['id','name'],
				entityStore: "BookmarksCategory",				
			}),
			displayField:'name',
			valueField:'id',
			triggerAction: 'all',
			editable: true,
			width:200,
			emptyText:t("Show all"),
			selectOnFocus :false,
			listeners:{
				scope: this,
				clear:function(){
					this.store.setFilter("category", null);
					this.store.load();
				},
				select: function(combo,record) {
					this.store.setFilter("category", {categoryId: record.data.id});
					this.store.load();
					//this.setValue(record.data[this.displayField]);
				}
			}
		});

		this.bookmarkColumnView = new go.modules.community.bookmarks.BookmarkColumnView({
			store: this.store
		});
	
		this.bmColumn = new Ext.Panel({
			autoScroll: true,
			region:'center',
			id:'bookmarks-center-column-panel',
			border:false,
			items:this.bookmarkColumnView,
		});
	
		this.bmView = new go.modules.community.bookmarks.BookmarksView({
			store: this.store
		});
	
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

			//add it to the main panel's items.
			this.tbar = new Ext.Toolbar([this.addButton = new Ext.Button({
				iconCls: 'btn-add',
				text: t("Add"),
				handler: function(){
					//this.addButton.setDisabled(true);
					var dlg = new go.modules.community.bookmarks.BookmarksDialog();
					dlg.show();
				},
				scope:this
			}),{
				text: t("Toggle view"),
				iconCls: 'btn-thumbnails',
				handler: function(){
					this.nextLayout();
				},
				scope:this
			},{
				iconCls: 'no-btn-categories',
				text: t("Administrate categories"),
				hidden: go.Modules.get("community", "bookmarks").permissionLevel < GO.permissionLevels.write,
				handler: function(){
					if(!this.categoriesDialog)
					{
						this.categoriesDialog = new go.modules.community.bookmarks.ManageCategoryDialog({
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
			},t("Category"),this.selectCategory,'->', 
			{
				xtype:'tbsearch',
				store:this.store
			}]);

			this.items = [
				this.cardPanel
			];

			go.modules.community.bookmarks.MainPanel.superclass.initComponent.call(this);
			this.init();
	}
});

// Bookmark hyperlink openen, in GO tab of in browser tab
go.modules.community.bookmarks.openBookmark = function(record) {

	if(record.data.openExtern==0){
		var websiteTab = new GO.panel.IFrameComponent( {
			title: record.data.name,
			url: record.data.content,
			border: false,
			closable: true
		})

		GO.mainLayout.tabPanel.add(websiteTab) // open nieuwe tab in group-office
		websiteTab.show();
	}
	else{
		window.open(record.data.content) // open in nieuw browser tab
	}
}

// bookmark module toevoegen aan modulemanager
GO.moduleManager.addModule('bookmark', go.modules.community.bookmarks.MainPanel, {
	title : t("Bookmark"),
	iconCls : 'go-tab-icon-bookmarks'
});