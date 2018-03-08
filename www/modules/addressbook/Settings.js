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

GO.addressbook.SettingsPanel = function(config) {
	if(!config) {
		config = {};
	}

	config.autoScroll = true;
	config.border = false;
	config.hideLabel = true;
	config.title = GO.addressbook.lang.addressbook;
	config.hideMode='offsets';
	config.layout = 'form';
	config.labelWidth=125;
	config.bodyStyle='padding:5px;';
	config.items = {
		xtype:'fieldset',
		autoHeight:true,
		layout:'form',
		forceLayout:true,
		title:GO.addressbook.lang.addressbookDefaults,
		items:[
			this.selectAddressbook = new GO.addressbook.SelectAddressbook({
				fieldLabel : GO.addressbook.lang.defaultAddressbook,
				hiddenName : 'default_addressbook_id'
			})
		]
	};
	
	GO.addressbook.SettingsPanel.superclass.constructor.call(this, config);
};

Ext.extend(GO.addressbook.SettingsPanel, Ext.Panel, {
		
});

GO.mainLayout.onReady(function() {
	GO.moduleManager.addSettingsPanel('addressbook', GO.addressbook.SettingsPanel);
});