/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 19225 2015-06-22 15:07:34Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

 
GO.notes.MainPanel = function(config){
	
	if(!config)
	{
		config = {};
	}

	this.centerPanel = new GO.notes.NotesGrid({
		region:'center',
		id:'no-center-panel',
		border:true
	});
	
	this.westPanel= new GO.grid.MultiSelectGrid({
		region:'west',
		id:'no-multiselect',
		title:GO.notes.lang.categories,
		loadMask:true,
		store: GO.notes.readableCategoriesStore,
		width: 230,
		split:true,
		allowNoSelection:true,
		collapsible:true,
		collapseMode:'mini',
		bbar: new GO.SmallPagingToolbar({
			items:[this.searchField = new GO.form.SearchField({
				store: GO.notes.readableCategoriesStore,
				width:120,
				emptyText: GO.lang.strSearch
			})],
			store:GO.notes.readableCategoriesStore,
			pageSize:GO.settings.config.nav_page_size
		}),
		relatedStore: this.centerPanel.store
	});

//	this.westPanel.on('change', function(grid, categories, records)
//	{
//		if(records.length){
//			this.centerPanel.store.baseParams.notes_categories_filter = Ext.encode(categories);
//			this.centerPanel.store.reload();
//			//delete this.centerPanel.store.baseParams.notes_categories_filter;
//		}
//	}, this);
//	
//	this.westPanel.store.on('load', function()
//	{
//		this.centerPanel.store.baseParams.notes_categories_filter = Ext.encode(this.westPanel.getSelected());
//		this.centerPanel.store.load();		
//	}, this);

	
	
	this.centerPanel.on("delayedrowselect",function(grid, rowIndex, r){
		this.eastPanel.load(r.data.id);		
	}, this);

	this.centerPanel.on('rowdblclick', function(grid, rowIndex){
		this.eastPanel.editHandler();
	}, this);
	
	this.eastPanel = new GO.notes.NotePanel({
		region:'east',
		id:'no-east-panel',
		width:440,
		collapsible:true,
		collapseMode:'mini',
		border:true
	});
	
	config.tbar=new Ext.Toolbar({
		cls:'go-head-tb',
		items: [
			{
	      xtype:'htmlcomponent',
				html:GO.notes.lang.name,
				cls:'go-module-title-tbar'
			},{
				grid: this.centerPanel,
				xtype:'addbutton',
				handler: function(b){
					this.eastPanel.reset();

					GO.notes.showNoteDialog(0, {
							loadParams:{
								category_id: b.buttonParams.id						
							}
					});
				},
				scope: this
			},{
				xtype:'deletebutton',
				grid:this.centerPanel,
				handler: function(){
					this.centerPanel.deleteSelected({
						callback : this.eastPanel.gridDeleteCallback,
						scope: this.eastPanel
					});
				},
				scope: this
			},{
				iconCls: 'no-btn-categories',
				text: GO.notes.lang.manageCategories,
				cls: 'x-btn-text-icon',
				handler: function(){
					if(!this.categoriesDialog)
					{
						this.categoriesDialog = new GO.notes.ManageCategoriesDialog();
						this.categoriesDialog.on('change', function(){
							this.westPanel.store.reload();
							GO.notes.writableCategoriesStore.reload();
						}, this);
					}
					this.categoriesDialog.show();
				},
				scope: this

			},
			this.exportMenu = new GO.base.ExportMenu({className:'GO\\Notes\\Export\\CurrentGrid'})
		]
	});

	this.exportMenu.setColumnModel(this.centerPanel.getColumnModel());

	config.items=[
	this.westPanel,
	this.centerPanel,
	this.eastPanel
	];	
	
	config.layout='border';
	GO.notes.MainPanel.superclass.constructor.call(this, config);	
};


Ext.extend(GO.notes.MainPanel, Ext.Panel, {
	afterRender : function()
	{
		GO.dialogListeners.add('note',{
			scope:this,
			save:function(){
				this.centerPanel.store.reload();
			}
		});

		GO.notes.readableCategoriesStore.load();
		
		GO.notes.MainPanel.superclass.afterRender.call(this);
	}
});

GO.notes.showNoteDialog = function(note_id, config){

	if(!GO.notes.noteDialog)
		GO.notes.noteDialog = new GO.notes.NoteDialog();
	
	GO.notes.noteDialog.show(note_id, config);
}


/*
 * This will add the module to the main tabpanel filled with all the modules
 */
 
GO.moduleManager.addModule('notes', GO.notes.MainPanel, {
	title : GO.notes.lang.notes,
	iconCls : 'go-tab-icon-notes'
});
/*
 * If your module has a linkable item, you should add a link handler like this. 
 * The index (no. 1 in this case) should be a unique identifier of your item.
 * See classes/base/links.class.inc for an overview.
 * 
 * Basically this function opens a project window when a user clicks on it from a 
 * panel with links. 
 */

GO.linkHandlers["GO\\Notes\\Model\\Note"]=function(id){
	if(!GO.notes.linkWindow){
		var notePanel = new GO.notes.NotePanel();
		GO.notes.linkWindow= new GO.LinkViewWindow({
			title: GO.notes.lang.note,
			items: notePanel,
			notePanel: notePanel,
			closeAction:"hide"
		});
	}
	GO.notes.linkWindow.notePanel.load(id);
	GO.notes.linkWindow.show();
	return GO.notes.linkWindow;
}

GO.linkPreviewPanels["GO\\Notes\\Model\\Note"]=function(config){
	config = config || {};
	return new GO.notes.NotePanel(config);
}


/* {LINKHANDLERS} */


GO.newMenuItems.push({
	text: GO.notes.lang.note,
	iconCls: 'go-model-icon-GO_Notes_Model_Note',
	handler:function(item, e){		
		GO.notes.showNoteDialog(0, {
			link_config: item.parentMenu.link_config			
		});
	}
});
/* {NEWMENUITEMS} */


