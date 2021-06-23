/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: CalendarOverrides.js 17032 2014-03-12 09:41:25Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.moduleManager.onModuleReady('calendar',function(){

	Ext.override(GO.calendar.MainPanel, {	
		
		init : GO.calendar.MainPanel.prototype.init.createSequence(function(){
			//GO.favorites.favoritesCalendarStore.load();
		}),
		
		initComponent : GO.calendar.MainPanel.prototype.initComponent.createSequence(function(){
			
			if(!go.Modules.isAvailable("legacy", "favorites")) {
				return;
			}
			
			this.calendarFavoritesList = new GO.favorites.CalendarFavoritesList({	
				stateEvents: ['collapse', 'expand'],
				getState: function () {                              
						return {
								collapsed: !this.calendarList.collapsed
						}
				}.createDelegate(this)
			
			});
			this.calendarListPanel.insert(0,this.calendarFavoritesList);
			
			this.calendarFavoritesList.parent = this;
			
			this.calendarList.stateEvents = ['collapse', 'expand'];
			this.calendarList.getState= function () {                              
						return {
								collapsed: !this.calendarFavoritesList.collapsed
						}
				}.createDelegate(this);
			
			
			this.on("afterrender", function() {
				GO.favorites.favoritesCalendarStore.load();
			}, this);
						
			var changeCalendar = function(grid, calendars, records)
			{
				if(records.length){
					var cal_ids = [];

					for (var i=0,max=records.length;i<max;i++) {
						cal_ids[i] = records[i].data.id;
					}
					var config = {
						calendars: cal_ids,
						group_id:1,
						merge:true,
						owncolor:true,
						project_id:records[0].data.project_id
					};
					this.setDisplay(config);
									
			//		this.calendarListPanel.getLayout().setActiveItem('calendarFavoritesList');				
				}
				
					// Clear the checkbox selection of the calendar List
					this.calendarList.applyFilter([],true);
				
			};
			
//			this.calendarList.on('expand',function(){
//				this.setDisplay();
//			},this);
			
			this.calendarFavoritesList.on('change', changeCalendar, this);
			
			this.calendarList.on('change', function(grid, abooks, records){
				// Clear the checkbox selection of the calendarFavoritesList
				this.calendarFavoritesList.applyFilter([],true);

			}, this);
		}),
	
		clearGrids : function(config){
			// debugger;
			var selectGrid, clearGrids=[];
			if(this.view_id>0){
				selectGrid = this.viewsList;

				selectGrid.expand();

				this.resourcesList.getSelectionModel().clearSelections();

				clearGrids.push(this.calendarList);
				if(this.projectCalendarsList)
					clearGrids.push(this.projectCalendarsList);
			}else
			{
				this.viewsList.getSelectionModel().clearSelections();

				if(this.group_id==1){
					selectGrid = this.calendarListPanel.getLayout().activeItem;
					if(!selectGrid.applyFilter) {
						//when views or resources was selected
						selectGrid = this.calendarList;
					}
					this.resourcesList.getSelectionModel().clearSelections();

					selectGrid.expand();

					if(config.applyFilter)
						selectGrid.applyFilter(this.calendars, true);
				}else
				{
					clearGrids.push(this.calendarList);
					selectGrid = this.resourcesList;

					var records=[];
					for(var i=0,max=this.calendars.length;i<max;i++){
						records.push(selectGrid.store.getById(this.calendars[i]));
					}
					selectGrid.getSelectionModel().selectRecords(records);
					selectGrid.expand();
				}					
			}

			for(var i=0,max=clearGrids.length;i<max;i++){
				//clearGrids[i].allowNoSelection=true;
				clearGrids[i].applyFilter('clear', true);
				//clearGrids[i].allowNoSelection=false;
			}
		},
		
		/**
		 * Helper function to call the setCalendarBackgroundColors function of the parent
		 */
		setOriginalCalendarBackgroundColors: GO.calendar.MainPanel.prototype.setCalendarBackgroundColors,

	 /**
	  * Set the background colors of the selected rows in the Grid.
		* When only one record is selected, then there is no background color needed.
		*   
	  * @returns {undefined}
	  */
		setCalendarBackgroundColors : function(){

			if(!go.Modules.isAvailable("legacy", "favorites")) {
				return this.setOriginalCalendarBackgroundColors();
			}

			var activeItem = this.calendarListPanel.getLayout().activeItem;

			if(activeItem.id && activeItem.id === this.calendarFavoritesList.id){
				this.calendarFavoritesList.setFavoritesCalendarBackgroundColors(this.calendarFavoritesList.getView(),this.getActivePanel().store);
				this.clearBackgroundColors(this.calendarList);
				
			}else{
				this.setOriginalCalendarBackgroundColors();
				this.clearBackgroundColors(this.calendarFavoritesList);
			}
		},	
		
		/**
		 * Clear the background color of each row in the given Grid.
		 * 
		 * @param GO.grid.MultiSelectGrid grid
		 * @returns {undefined}
		 */
		clearBackgroundColors: function(grid){
			var store = grid.store;
			var view = grid.getView();
			var total = store.getTotalCount();

			for(var i=0;i<total;i++){
				var rowEl = Ext.get(view.getRow(i));		
					if(rowEl)
						rowEl.applyStyles("background-color: inherit");
			}
			
		}
	});
});
