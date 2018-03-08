/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: TasklistFavoritesList.js 16391 2013-12-03 10:01:29Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.favorites.TasklistFavoritesList = Ext.extend(GO.grid.MultiSelectGrid,{

	initComponent : function(){
		
		Ext.apply(this,{
			id : 'fav-tasklist-grid',
			title:GO.favorites.lang.favorites,
			allowNoSelection:true,
			tools: [{
				text:GO.favorites.lang.manageFavorites,
				id:'gear',
				handler:function(){
					if(!GO.favorites.tasklistFavoritesDialog){
						GO.favorites.tasklistFavoritesDialog = new GO.base.model.multiselect.dialog({
							url:'favorites/tasklistFavorites',
							columns:[{ header: GO.lang['strName'], dataIndex: 'name', sortable: true }],
							fields:['id','name'],
							title:GO.favorites.lang.favorites,
							model_id:GO.settings.user_id
						});
					}
					GO.favorites.tasklistFavoritesDialog.show();
					GO.favorites.tasklistFavoritesDialog.on("hide", function(){
						this.store.load();
					},this);
				},
				scope: this
			},{
				text:GO.lang.selectAll,
				id:'plus',
				qtip:GO.lang.selectAll,
				handler:function(){this.selectAll();},
				scope: this
			}],
			store: GO.favorites.favoritesTasklistStore,
			bbar: new GO.SmallPagingToolbar({
				items:[this.searchField = new GO.form.SearchField({
					store: GO.favorites.favoritesTasklistStore,
					width:120,
					emptyText: GO.lang.strSearch
				})],
				store:GO.favorites.favoritesTasklistStore,
				pageSize:GO.settings.config.nav_page_size
			})
		});
		
		GO.favorites.TasklistFavoritesList.superclass.initComponent.call(this);		
	},
	getRequestParam : function(){
		return 'ta-taskslists';
	}
});