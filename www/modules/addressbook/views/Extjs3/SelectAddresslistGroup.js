/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: SelectAddresslistGroup.js
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.addressbook.SelectAddresslistGroup = Ext.extend(GO.form.ComboBoxReset,{ //GO.form.ComboBox, {
	initComponent : function(){

		if(!this.hiddenName)
			this.hiddenName='addresslist_group_id';

		Ext.apply(this, {
			store: new GO.data.JsonStore({
				url: GO.url('addressbook/addresslistgroup/store'),
				root: 'results',
				id: 'id',
				totalProperty:'total',
				fields: ['id','name'],
				remoteSort: true,
				baseParams: {
					limit:GO.settings.addresslists_store_forced_limit?parseInt(GO.settings.addresslists_store_forced_limit):parseInt(GO.settings['max_rows_list'])
				}
			}),
			valueField:'id',
			displayField:'name',
			triggerAction: 'all',
			selectOnFocus:true
		});

		GO.addressbook.SelectAddresslistGroup.superclass.initComponent.call(this);
	}
});
