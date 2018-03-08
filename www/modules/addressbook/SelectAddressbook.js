/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: SelectAddressbook.js 22112 2018-01-12 07:59:41Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.addressbook.SelectAddressbook = function(config){

	config = config || {};

	if(!config.hiddenName)
		config.hiddenName='addressbook_id';

	if(!config.fieldLabel)
	{
		config.fieldLabel=t("Address book", "addressbook");
	}


	config.store = GO.addressbook.writableAddressbooksStore;

	Ext.apply(config, {
		displayField: 'name',
		valueField: 'id',
		triggerAction:'all',
		mode:'remote',
		editable: true,
		selectOnFocus:true,
		forceSelection: true,
		typeAhead: true,
		emptyText:t("Please select..."),
		pageSize: 20
	});

	GO.addressbook.SelectAddressbook.superclass.constructor.call(this,config);

}
Ext.extend(GO.addressbook.SelectAddressbook, GO.form.ComboBox, {
	
});


