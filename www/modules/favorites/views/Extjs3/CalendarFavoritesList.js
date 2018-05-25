/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CalendarFavoritesList.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.favorites.CalendarFavoritesList = Ext.extend(GO.grid.MultiSelectGrid,{

	// Calendar mainPanel, used to set the correct calendar colors
	parent:false,

	initComponent : function(){
		
		Ext.apply(this,{
			id : 'fav-calendar-grid',
			title:t("Favorites", "favorites"),
			allowNoSelection:true,
			
			tools: [{
				text:t("Manage favorites", "favorites"),
				id:'gear',
				handler:function(){
					if(!GO.favorites.calendarFavoritesDialog){
						GO.favorites.calendarFavoritesDialog = new GO.base.model.multiselect.dialog({
							url:'favorites/calendarFavorites',
							columns:[{ header: t("Name"), dataIndex: 'name', sortable: true }],
							fields:['id','name'],
							title:t("Favorites", "favorites"),
							model_id:GO.settings.user_id
						});
						
						GO.favorites.calendarFavoritesDialog.on("hide", function(){
							this.store.load({
								callback:function(){
									this.getSelectionModel().clearSelections();
									this.setFavoritesCalendarBackgroundColors(this.getView(),this.parent.getActivePanel().store);
								},
								scope:this
							});
						},this);
					}
					GO.favorites.calendarFavoritesDialog.show();
				},
				scope: this
			},{
				text:t("Select all"),
				id:'plus',
				qtip:t("Select all"),
				handler:function(){this.selectAll();},
				scope: this
			}],
			store: GO.favorites.favoritesCalendarStore,
			bbar: new GO.SmallPagingToolbar({
				items:[this.searchField = new GO.form.SearchField({
					store: GO.favorites.favoritesCalendarStore,
					width:120,
					emptyText: t("Search")
				})],
				store:GO.favorites.favoritesCalendarStore,
				pageSize:GO.settings.config.nav_page_size
			})
		});
		
		GO.favorites.CalendarFavoritesList.superclass.initComponent.call(this);		
	},
	setFavoritesCalendarBackgroundColors : function(view,activePanelStore){
		view.refresh();
				
		if(activePanelStore.reader.jsonData.backgrounds){
			var rowIndex;
			
			for(var cal_id in activePanelStore.reader.jsonData.backgrounds){
				rowIndex = this.store.indexOfId(parseInt(cal_id));				
				if(rowIndex>-1){
					var rowEl = Ext.get(view.getRow(rowIndex));
					if(rowEl)
						rowEl.applyStyles("background-color: #"+activePanelStore.reader.jsonData.backgrounds[cal_id]);				
				}
			}
		}
	}
});
