/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AddressbookFavoritesList.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.favorites.AddressbookFavoritesList = Ext.extend(GO.grid.MultiSelectGrid,{

	initComponent : function(){
		
		Ext.apply(this,{
			id : 'fav-addressbook-grid',
			title:t("Favorites", "favorites"),
			allowNoSelection:true,
			layout:'fit',
			autoHeight: true,
			tbtools: [{
				tooltip:t("Manage favorites", "favorites"),
				iconCls:'ic-settings',
				handler:function(){
					if(!GO.favorites.addressbookFavoritesDialog){
						GO.favorites.addressbookFavoritesDialog = new GO.base.model.multiselect.dialog({
							url:'favorites/addressbookFavorites',
							columns:[{ header: t("Name"), dataIndex: 'name', sortable: true }],
							fields:['id','name'],
							title:t("Favorites", "favorites"),
							model_id:GO.settings.user_id
						});
					}
					GO.favorites.addressbookFavoritesDialog.show();
					GO.favorites.addressbookFavoritesDialog.on("hide", function(){
						this.store.load();
					},this);
				},
				scope: this
			}],
			store: GO.favorites.favoritesAddressbookStore
		});
		
		GO.favorites.AddressbookFavoritesList.superclass.initComponent.call(this);		
	},
	getRequestParam : function(){
		return 'abooks';
	}
});
