/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: AddressbookOverrides.js 16833 2014-02-13 14:22:53Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
GO.moduleManager.onModuleReady('addressbook',function(){
	
	Ext.override(GO.addressbook.MainPanel, {	
		
		initComponent : GO.addressbook.MainPanel.prototype.initComponent.createSequence(function(){

			if(!go.Modules.isAvailable("legacy", "favorites")) {
				return;
			}

			this.addressbookFavoritesList = new GO.favorites.AddressbookFavoritesList({
				stateEvents: ['collapse', 'expand'],
				getState: function () { 
					return { collapsed: !this.addressbooksGrid.collapsed }
				}.createDelegate(this)
			});
						
			this.addressbookFavoritesList.on('change', function(grid, abooks, records){
				var books = Ext.encode(abooks);
				var panel = this.tabPanel.getActiveTab();

				this.companiesGrid.store.baseParams.books = books;
				this.contactsGrid.store.baseParams.books = books;

				if(panel.id=='ab-contacts-grid')
				{
					this.contactsGrid.store.load();
					delete this.contactsGrid.store.baseParams.books;
				}else
				{

					this.companiesGrid.store.load();
					delete this.companiesGrid.store.baseParams.books;
				}

				if(records.length)
				{
					GO.addressbook.defaultAddressbook = records[0];
				}
				
				// Clear the checkbox selection of the addressbooksGrid
				this.addressbooksGrid.applyFilter([],true);
			}, this);

			this.addressbooksGrid.on('change', function(grid, abooks, records){
				// Clear the checkbox selection of the addressbookFavoritesList
				this.addressbookFavoritesList.applyFilter([],true);
			}, this);

			this.westPanelContainer.insert(1,this.addressbookFavoritesList);
			
			this.addressbooksGrid.stateEvents = ['collapse', 'expand'];
			this.addressbooksGrid.getState= function () {                              
				return {
					collapsed: !this.addressbookFavoritesList.collapsed
				}
			}.createDelegate(this);

			this.on("afterrender", function() {
				GO.favorites.favoritesAddressbookStore.load();
			}, this);
		})
	});
});
