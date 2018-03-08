/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: Stores.js 15954 2013-10-17 12:04:36Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.addressbook.addressbooksStoreFields = new Array('id','name','user_name', 'acl_id','user_id','contactCustomfields','companyCustomfields','default_salutation', 'checked');


GO.addressbook.readableAddressbooksStore = new GO.data.JsonStore({
			url: GO.url('addressbook/addressbook/store'),
			baseParams: {
				'permissionLevel' : GO.permissionLevels.read,
				limit:parseInt(GO.settings['max_rows_list'])

				},
			root: 'results', 
			totalProperty: 'total', 
			id: 'id',
			fields: GO.addressbook.addressbooksStoreFields,
			remoteSort: true
		});

GO.addressbook.writableAddressbooksStore = new GO.data.JsonStore({
			url: GO.url('addressbook/addressbook/store'),
			baseParams: {
				'permissionLevel' : GO.permissionLevels.write,
				limit:parseInt(GO.settings['max_rows_list'])
				},
			root: 'results', 
			totalProperty: 'total', 
			id: 'id',
			fields: GO.addressbook.addressbooksStoreFields,
			remoteSort: true
		});

GO.addressbook.writableAddresslistsStore = new GO.data.JsonStore({
    url: GO.url("addressbook/addresslist/store"),
    baseParams: {
        permissionLevel: GO.permissionLevels.write,
				limit:0
    },
    fields: ['id', 'name', 'user_name','acl_id'],
    remoteSort: true
});
		
GO.addressbook.writableAddresslistsStore.on('load', function(){
	GO.addressbook.writableAddresslistsStore.on('load', function(){
    GO.addressbook.readableAddresslistsStore.load();
	}, this);
}, this, {single:true});
		
GO.addressbook.readableAddresslistsStore = new GO.data.JsonStore({
    url: GO.url("addressbook/addresslist/store"),
    baseParams: {
        permissionLevel: GO.permissionLevels.read
    },
    fields: ['id', 'name', 'user_name','acl_id', 'checked'],
    remoteSort: true
});

