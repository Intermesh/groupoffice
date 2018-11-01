/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: TasklistOverrides.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.moduleManager.onModuleReady('tasks',function(){
	
	Ext.override(GO.tasks.MainPanel, {	
		
		initComponent : GO.tasks.MainPanel.prototype.initComponent.createSequence(function(){
			
			if(!go.Modules.isAvailable("legacy", "favorites")) {
				return;
			}
			
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
			
			this.on("afterrender", function() {
				GO.favorites.favoritesTasklistStore.load();
			}, this);

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
