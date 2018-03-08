/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: TasklistOverrides.js 16391 2013-12-03 10:01:29Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.moduleManager.onModuleReady('tasks',function(){
	
	Ext.override(GO.tasks.MainPanel, {	
		
		initComponent : GO.tasks.MainPanel.prototype.initComponent.createSequence(function(){
			this.tasklistFavoritesList = new GO.favorites.TasklistFavoritesList({
				id:'tasklistFavoritesList',
				relatedStore: this.gridPanel.store,
				autoLoadRelatedStore:false
			});
			
			this.taskListsPanel.on('change', function(grid, tasklists, records){
				// Clear the checkbox selection of the addressbookFavoritesList
				this.tasklistFavoritesList.applyFilter([],true);
			}, this);
			
			this.accordionPanel.insert(0,this.tasklistFavoritesList);
			
			GO.favorites.favoritesTasklistStore.load();
						
		//	this.calendarListPanel.getLayout().setActiveItem('calendarFavoritesList');				

			
//			this.contextMenu = new Ext.menu.Menu({
//				items: [{	
//					id: 'addToFavorites',
//					text: GO.favorites.lang.addToFavorites,
//					iconCls:'btn-add-to-favorites'
//				}],
//				listeners: {
//					itemclick: function(item,e) {
//						
//						var selected = this.calendarList.getSelected();
//						console.log(selected[0]);
//						
//						switch (item.id) {
//							case 'addToFavorites':
//								console.log(item);
//								console.log(e);
//							break;
//						}
//					},
//					scope:this
//				}
//			});
//			
//			this.calendarList.on('rowcontextmenu',function(grid, index, event){
//				event.stopEvent();
//				this.contextMenu.showAt(event.xy);
//			},this);
//			
//			this.taskListsPanel.on('expand',function(){
//
//			},this);
//			
			this.tasklistFavoritesList.on('change', function(grid, tasklists, records){
				this.tasklist_ids = tasklists;

				if(records.length)
				{
					this.addTaskPanel.populateComboBox(records);

					this.tasklist_id = records[0].data.id;
					this.tasklist_name = records[0].data.name;
				}
				
				this.taskListsPanel.applyFilter([],true);
				
			}, this);
		})
	})
});