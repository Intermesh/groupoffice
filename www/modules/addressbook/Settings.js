/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: Settings.js 14816 2013-05-21 08:31:20Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
GO.addressbook.SettingsPanel = Ext.extend(Ext.Panel, {
	autoScroll: true,
	title: t("Address book", "addressbook"),
	iconCls: 'ic-perm-contact-calendar',
	
	onLoadStart: function (userId) {

	},
	initComponent: function() {

		this.items = {
			xtype:'fieldset',
			autoHeight:true,
			layout:'form',
			forceLayout:true,
			hideLabel: true,
			title:t("Defaults settings for address book", "addressbook"),
			items:[
				this.selectAddressbook = new GO.addressbook.SelectAddressbook({
					fieldLabel : t("Default address book", "addressbook"),
					hiddenName : 'addressbookSettings.default_addressbook_id'
				})
			]
		};

		GO.addressbook.SettingsPanel.superclass.initComponent.call(this);
	}
});

